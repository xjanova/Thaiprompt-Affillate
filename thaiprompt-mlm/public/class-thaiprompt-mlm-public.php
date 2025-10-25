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
}
