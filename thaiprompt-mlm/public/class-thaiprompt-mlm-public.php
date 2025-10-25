<?php
/**
 * Public-facing functionality - Portal Only
 */
class Thaiprompt_MLM_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('wp_ajax_thaiprompt_mlm_get_genealogy_public', array($this, 'ajax_get_genealogy'));
        add_action('wp_ajax_mlm_save_landing_page', array($this, 'ajax_save_landing_page'));

        // Landing page URL routing
        add_action('init', array($this, 'register_landing_page_rewrite'));
        add_filter('query_vars', array($this, 'register_landing_page_query_vars'));
        add_action('template_redirect', array($this, 'handle_landing_page_request'));
    }

    /**
     * Register portal styles only
     */
    public function enqueue_styles() {
        // Only enqueue portal styles
        if (is_page_template('mlm-portal-template.php') || is_page('mlm-portal')) {
            wp_enqueue_style(
                $this->plugin_name . '-portal',
                THAIPROMPT_MLM_PLUGIN_URL . 'public/css/thaiprompt-mlm-portal.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register portal scripts only
     */
    public function enqueue_scripts() {
        // Only enqueue portal scripts
        if (is_page_template('mlm-portal-template.php') || is_page('mlm-portal')) {
            // GSAP for genealogy tree animation
            wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true);

            wp_enqueue_script(
                $this->plugin_name . '-portal',
                THAIPROMPT_MLM_PLUGIN_URL . 'public/js/thaiprompt-mlm-portal.js',
                array('jquery', 'gsap'),
                $this->version,
                true
            );

            // Localize script for AJAX
            wp_localize_script($this->plugin_name . '-portal', 'thaipromptMLM', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('thaiprompt_mlm_public_nonce'),
                'user_id' => get_current_user_id()
            ));
        }
    }

    /**
     * Register portal page template
     */
    public function register_portal_template($templates) {
        $templates['mlm-portal-template.php'] = __('MLM Portal', 'thaiprompt-mlm');
        return $templates;
    }

    /**
     * Load portal template
     */
    public function load_portal_template($template) {
        if (is_page_template('mlm-portal-template.php')) {
            $portal_template = THAIPROMPT_MLM_PLUGIN_DIR . 'templates/mlm-portal-template.php';
            if (file_exists($portal_template)) {
                return $portal_template;
            }
        }
        return $template;
    }

    /**
     * AJAX: Get genealogy tree for public users
     */
    public function ajax_get_genealogy() {
        check_ajax_referer('thaiprompt_mlm_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please log in', 'thaiprompt-mlm')));
        }

        $current_user_id = get_current_user_id();
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $current_user_id;
        $max_depth = isset($_POST['max_depth']) ? intval($_POST['max_depth']) : 5;

        // Security: Users can only view their own tree or their upline
        if ($user_id != $current_user_id) {
            $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);
            if (!$network_data) {
                wp_send_json_error(array('message' => __('User not found in network', 'thaiprompt-mlm')));
            }

            // Check if requested user is in current user's upline
            $upline = Thaiprompt_MLM_Network::get_upline($current_user_id);
            $allowed = false;
            foreach ($upline as $upline_member) {
                if ($upline_member['user_id'] == $user_id) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
            }
        }

        $tree = Thaiprompt_MLM_Database::get_genealogy_tree($user_id, $max_depth);

        if ($tree) {
            wp_send_json_success($tree);
        } else {
            wp_send_json_error(array('message' => __('No data found', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Save landing page
     */
    public function ajax_save_landing_page() {
        // ตรวจสอบ nonce จาก form (ไม่ใช่จาก localize)
        if (!isset($_POST['mlm_landing_nonce']) || !wp_verify_nonce($_POST['mlm_landing_nonce'], 'mlm_save_landing_page')) {
            wp_send_json_error(array('message' => __('Security check failed', 'thaiprompt-mlm')));
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please log in', 'thaiprompt-mlm')));
        }

        $user_id = get_current_user_id();
        $landing_id = isset($_POST['landing_id']) ? intval($_POST['landing_id']) : 0;

        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

        // Verify ownership if editing
        if ($landing_id > 0) {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND user_id = %d", $landing_id, $user_id));
            if (!$existing) {
                wp_send_json_error(array('message' => __('Landing page not found', 'thaiprompt-mlm')));
            }
        }

        // Prepare data
        $data = array(
            'user_id' => $user_id,
            'title' => sanitize_text_field($_POST['title']),
            'headline' => sanitize_textarea_field($_POST['headline']),
            'description' => sanitize_textarea_field($_POST['description']),
            'cta_text' => sanitize_text_field($_POST['cta_text']),
            'status' => 'pending', // Always pending when saving
            'is_active' => 0
        );

        // Handle image uploads (max 3)
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        for ($i = 1; $i <= 3; $i++) {
            $file_key = 'image' . $i;

            // Check if removing existing image
            if (isset($_POST['remove_image' . $i]) && $_POST['remove_image' . $i]) {
                $data['image' . $i . '_url'] = null;
                continue;
            }

            // Check if new image uploaded
            if (!empty($_FILES[$file_key]['name'])) {
                $file = $_FILES[$file_key];

                // Validate image
                $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
                if (!in_array($file['type'], $allowed_types)) {
                    wp_send_json_error(array('message' => sprintf(__('Image %d must be a valid image format', 'thaiprompt-mlm'), $i)));
                }

                // Check file size (max 5MB)
                if ($file['size'] > 5 * 1024 * 1024) {
                    wp_send_json_error(array('message' => sprintf(__('Image %d must be less than 5MB', 'thaiprompt-mlm'), $i)));
                }

                // Upload file
                $upload = wp_handle_upload($file, array('test_form' => false));

                if (isset($upload['error'])) {
                    wp_send_json_error(array('message' => $upload['error']));
                }

                $data['image' . $i . '_url'] = $upload['url'];
            }
        }

        // Save or update
        if ($landing_id > 0) {
            $result = $wpdb->update($table, $data, array('id' => $landing_id, 'user_id' => $user_id));
        } else {
            $result = $wpdb->insert($table, $data);
            $landing_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            Thaiprompt_MLM_Logger::info('Landing page saved', array(
                'user_id' => $user_id,
                'landing_id' => $landing_id,
                'title' => $data['title']
            ));

            wp_send_json_success(array(
                'message' => __('Landing page saved and submitted for approval!', 'thaiprompt-mlm'),
                'landing_id' => $landing_id,
                'redirect' => true
            ));
        } else {
            Thaiprompt_MLM_Logger::error('Failed to save landing page', array(
                'user_id' => $user_id,
                'error' => $wpdb->last_error
            ));

            wp_send_json_error(array('message' => __('Failed to save landing page', 'thaiprompt-mlm')));
        }
    }

    /**
     * Register landing page rewrite rules
     */
    public function register_landing_page_rewrite() {
        // URL format: /landing/{id}
        add_rewrite_rule(
            '^landing/([0-9]+)/?$',
            'index.php?landing_id=$matches[1]',
            'top'
        );

        // URL format: /landing/{username}
        add_rewrite_rule(
            '^landing/([^/]+)/?$',
            'index.php?landing_username=$matches[1]',
            'top'
        );
    }

    /**
     * Register landing page query vars
     */
    public function register_landing_page_query_vars($vars) {
        $vars[] = 'landing_id';
        $vars[] = 'landing_username';
        return $vars;
    }

    /**
     * Handle landing page template requests
     */
    public function handle_landing_page_request() {
        $landing_id = get_query_var('landing_id', 0);
        $landing_username = get_query_var('landing_username', '');

        // If username is provided, get the active landing page for that user
        if ($landing_username && !$landing_id) {
            $user = get_user_by('login', $landing_username);
            if (!$user) {
                $user = get_user_by('slug', $landing_username);
            }

            if ($user) {
                global $wpdb;
                $table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

                $landing_page = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM $table WHERE user_id = %d AND status = 'approved' AND is_active = 1 ORDER BY created_at DESC LIMIT 1",
                    $user->ID
                ));

                if ($landing_page) {
                    $landing_id = $landing_page->id;
                    set_query_var('landing_id', $landing_id);
                }
            }
        }

        // If we have a landing_id, load the template
        if ($landing_id) {
            $template = THAIPROMPT_MLM_PLUGIN_DIR . 'templates/landing-page-template.php';
            if (file_exists($template)) {
                include $template;
                exit;
            }
        }
    }
}
