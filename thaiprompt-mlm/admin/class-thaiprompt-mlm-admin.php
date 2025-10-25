<?php
/**
 * Admin area functionality
 */
class Thaiprompt_MLM_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            THAIPROMPT_MLM_PLUGIN_URL . 'admin/css/thaiprompt-mlm-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Chart.js for statistics
        wp_enqueue_style('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css');
    }

    /**
     * Register admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            THAIPROMPT_MLM_PLUGIN_URL . 'admin/js/thaiprompt-mlm-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Chart.js for statistics
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);

        // Localize script
        wp_localize_script($this->plugin_name, 'thaipromptMLM', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thaiprompt_mlm_nonce')
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Thaiprompt MLM', 'thaiprompt-mlm'),
            __('Thaiprompt MLM', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm',
            array($this, 'display_dashboard'),
            'dashicons-networking',
            56
        );

        // Dashboard
        add_submenu_page(
            'thaiprompt-mlm',
            __('Dashboard', 'thaiprompt-mlm'),
            __('Dashboard', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm',
            array($this, 'display_dashboard')
        );

        // Network
        add_submenu_page(
            'thaiprompt-mlm',
            __('Network', 'thaiprompt-mlm'),
            __('Network', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-network',
            array($this, 'display_network')
        );

        // Commissions
        add_submenu_page(
            'thaiprompt-mlm',
            __('Commissions', 'thaiprompt-mlm'),
            __('Commissions', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-commissions',
            array($this, 'display_commissions')
        );

        // Wallet & Withdrawals
        add_submenu_page(
            'thaiprompt-mlm',
            __('Wallet & Withdrawals', 'thaiprompt-mlm'),
            __('Wallet & Withdrawals', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-wallet',
            array($this, 'display_wallet')
        );

        // Ranks
        add_submenu_page(
            'thaiprompt-mlm',
            __('Ranks', 'thaiprompt-mlm'),
            __('Ranks', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-ranks',
            array($this, 'display_ranks')
        );

        // Landing Pages
        add_submenu_page(
            'thaiprompt-mlm',
            __('Landing Pages', 'thaiprompt-mlm'),
            __('Landing Pages', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-landing-pages',
            array($this, 'display_landing_pages')
        );

        // Reports
        add_submenu_page(
            'thaiprompt-mlm',
            __('Reports', 'thaiprompt-mlm'),
            __('Reports', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-reports',
            array($this, 'display_reports')
        );

        // Settings
        add_submenu_page(
            'thaiprompt-mlm',
            __('Settings', 'thaiprompt-mlm'),
            __('Settings', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-settings',
            array($this, 'display_settings')
        );

        // LINE Settings
        add_submenu_page(
            'thaiprompt-mlm',
            __('LINE Settings', 'thaiprompt-mlm'),
            __('LINE Settings', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-line-settings',
            array($this, 'display_line_settings')
        );

        // AI Configuration
        add_submenu_page(
            'thaiprompt-mlm',
            __('AI Configuration', 'thaiprompt-mlm'),
            __('AI Configuration', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-ai-configuration',
            array($this, 'display_ai_configuration')
        );

        // Rich Menu
        add_submenu_page(
            'thaiprompt-mlm',
            __('Rich Menu', 'thaiprompt-mlm'),
            __('Rich Menu', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-rich-menu',
            array($this, 'display_rich_menu')
        );

        // Rich Menu Builder (hidden from menu)
        add_submenu_page(
            null, // hidden from menu
            __('Rich Menu Builder', 'thaiprompt-mlm'),
            __('Rich Menu Builder', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-rich-menu-builder',
            array($this, 'display_rich_menu_builder')
        );

        // Flex Message Builder
        add_submenu_page(
            'thaiprompt-mlm',
            __('Flex Message Builder', 'thaiprompt-mlm'),
            __('Flex Message Builder', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-flex-message-builder',
            array($this, 'display_flex_message_builder')
        );

        // Debug Logs
        add_submenu_page(
            'thaiprompt-mlm',
            __('Debug Logs', 'thaiprompt-mlm'),
            __('Debug Logs', 'thaiprompt-mlm'),
            'manage_options',
            'thaiprompt-mlm-debug-logs',
            array($this, 'display_debug_logs')
        );
    }

    /**
     * Display dashboard page
     */
    public function display_dashboard() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }

    /**
     * Display network page
     */
    public function display_network() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/network.php';
    }

    /**
     * Display commissions page
     */
    public function display_commissions() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/commissions.php';
    }

    /**
     * Display wallet page
     */
    public function display_wallet() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/wallet.php';
    }

    /**
     * Display ranks page
     */
    public function display_ranks() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/ranks.php';
    }

    /**
     * Display landing pages page
     */
    public function display_landing_pages() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/landing-pages.php';
    }

    /**
     * Display reports page
     */
    public function display_reports() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/reports.php';
    }

    /**
     * Display settings page
     */
    public function display_settings() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/settings.php';
    }

    /**
     * Display LINE settings page
     */
    public function display_line_settings() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/line-settings.php';
    }

    /**
     * Display AI Configuration page
     */
    public function display_ai_configuration() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/ai-configuration.php';
    }

    /**
     * Display Rich Menu page
     */
    public function display_rich_menu() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/rich-menu.php';
    }

    /**
     * Display Rich Menu Builder page
     */
    public function display_rich_menu_builder() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/rich-menu-builder.php';
    }

    /**
     * Display Flex Message Builder page
     */
    public function display_flex_message_builder() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/flex-message-builder.php';
    }

    /**
     * Display debug logs page
     */
    public function display_debug_logs() {
        include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/debug-logs.php';
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('thaiprompt_mlm_settings', 'thaiprompt_mlm_settings');

        // AJAX handlers
        add_action('wp_ajax_thaiprompt_mlm_approve_commission', array($this, 'ajax_approve_commission'));
        add_action('wp_ajax_thaiprompt_mlm_approve_withdrawal', array($this, 'ajax_approve_withdrawal'));
        add_action('wp_ajax_thaiprompt_mlm_reject_withdrawal', array($this, 'ajax_reject_withdrawal'));
        add_action('wp_ajax_thaiprompt_mlm_get_genealogy', array($this, 'ajax_get_genealogy'));
        add_action('wp_ajax_thaiprompt_mlm_add_user_to_network', array($this, 'ajax_add_user_to_network'));
        add_action('wp_ajax_thaiprompt_mlm_update_rank', array($this, 'ajax_update_rank'));
        add_action('wp_ajax_thaiprompt_mlm_approve_landing_page', array($this, 'ajax_approve_landing_page'));
        add_action('wp_ajax_thaiprompt_mlm_reject_landing_page', array($this, 'ajax_reject_landing_page'));
        add_action('wp_ajax_thaiprompt_mlm_download_log', array($this, 'ajax_download_log'));
        add_action('wp_ajax_thaiprompt_mlm_clear_log', array($this, 'ajax_clear_log'));
        add_action('wp_ajax_thaiprompt_mlm_clear_all_logs', array($this, 'ajax_clear_all_logs'));
    }

    /**
     * AJAX: Approve commission
     */
    public function ajax_approve_commission() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $commission_id = intval($_POST['commission_id']);
        $result = Thaiprompt_MLM_Commission::approve_commission($commission_id);

        if ($result) {
            wp_send_json_success(array('message' => __('Commission approved', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => __('Failed to approve commission', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Approve withdrawal
     */
    public function ajax_approve_withdrawal() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $withdrawal_id = intval($_POST['withdrawal_id']);
        $result = Thaiprompt_MLM_Wallet::approve_withdrawal($withdrawal_id);

        if (!is_wp_error($result)) {
            wp_send_json_success(array('message' => __('Withdrawal approved', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
    }

    /**
     * AJAX: Reject withdrawal
     */
    public function ajax_reject_withdrawal() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $withdrawal_id = intval($_POST['withdrawal_id']);
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');

        $result = Thaiprompt_MLM_Wallet::reject_withdrawal($withdrawal_id, $reason);

        if (!is_wp_error($result)) {
            wp_send_json_success(array('message' => __('Withdrawal rejected', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
    }

    /**
     * AJAX: Get genealogy tree
     */
    public function ajax_get_genealogy() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        $user_id = intval($_POST['user_id']);
        $max_depth = intval($_POST['max_depth'] ?? 5);

        $tree = Thaiprompt_MLM_Database::get_genealogy_tree($user_id, $max_depth);

        if ($tree) {
            wp_send_json_success($tree);
        } else {
            wp_send_json_error(array('message' => __('User not found', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Add user to network
     */
    public function ajax_add_user_to_network() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $user_id = intval($_POST['user_id']);
        $sponsor_id = intval($_POST['sponsor_id']);

        $result = Thaiprompt_MLM_Network::register_user($user_id, $sponsor_id);

        if (!is_wp_error($result)) {
            wp_send_json_success(array('message' => __('User added to network', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
    }

    /**
     * AJAX: Update user rank
     */
    public function ajax_update_rank() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $user_id = intval($_POST['user_id']);
        $rank = Thaiprompt_MLM_Rank::check_user_rank($user_id);

        if ($rank) {
            wp_send_json_success(array(
                'message' => __('Rank updated', 'thaiprompt-mlm'),
                'rank' => $rank->rank_name
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update rank', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Approve landing page
     */
    public function ajax_approve_landing_page() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $landing_id = intval($_POST['landing_id']);
        $admin_notes = sanitize_textarea_field($_POST['admin_notes'] ?? '');

        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

        $result = $wpdb->update(
            $table,
            array(
                'status' => 'approved',
                'is_active' => 1,
                'admin_notes' => $admin_notes,
                'approved_at' => current_time('mysql'),
                'approved_by' => get_current_user_id()
            ),
            array('id' => $landing_id)
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Landing page approved and activated!', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => __('Failed to approve landing page', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Reject landing page
     */
    public function ajax_reject_landing_page() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $landing_id = intval($_POST['landing_id']);
        $admin_notes = sanitize_textarea_field($_POST['admin_notes'] ?? '');

        if (empty($admin_notes)) {
            wp_send_json_error(array('message' => __('Please provide a reason for rejection', 'thaiprompt-mlm')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

        $result = $wpdb->update(
            $table,
            array(
                'status' => 'rejected',
                'is_active' => 0,
                'admin_notes' => $admin_notes,
                'approved_by' => get_current_user_id()
            ),
            array('id' => $landing_id)
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Landing page rejected', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => __('Failed to reject landing page', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Download log file
     */
    public function ajax_download_log() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'thaiprompt-mlm'));
        }

        $log_file = isset($_GET['log_file']) ? sanitize_text_field($_GET['log_file']) : '';

        if (empty($log_file) || !file_exists($log_file)) {
            wp_die(__('Log file not found', 'thaiprompt-mlm'));
        }

        // Security: Ensure file is in the correct directory
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/thaiprompt-mlm-logs';

        if (strpos(realpath($log_file), realpath($log_dir)) !== 0) {
            wp_die(__('Invalid log file', 'thaiprompt-mlm'));
        }

        // Send file for download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($log_file) . '"');
        header('Content-Length: ' . filesize($log_file));
        header('Cache-Control: no-cache');

        readfile($log_file);
        exit;
    }

    /**
     * AJAX: Clear single log file
     */
    public function ajax_clear_log() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        $log_file = isset($_POST['log_file']) ? sanitize_text_field($_POST['log_file']) : '';

        if (empty($log_file)) {
            wp_send_json_error(array('message' => __('No log file specified', 'thaiprompt-mlm')));
        }

        // Security: Ensure file is in the correct directory
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/thaiprompt-mlm-logs';

        if (strpos(realpath($log_file), realpath($log_dir)) !== 0) {
            wp_send_json_error(array('message' => __('Invalid log file', 'thaiprompt-mlm')));
        }

        if (Thaiprompt_MLM_Logger::clear_log($log_file)) {
            wp_send_json_success(array('message' => __('Log file cleared successfully', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => __('Failed to clear log file', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Clear all log files
     */
    public function ajax_clear_all_logs() {
        check_ajax_referer('thaiprompt_mlm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'thaiprompt-mlm')));
        }

        if (Thaiprompt_MLM_Logger::clear_all_logs()) {
            wp_send_json_success(array('message' => __('All log files cleared successfully', 'thaiprompt-mlm')));
        } else {
            wp_send_json_error(array('message' => __('Failed to clear log files', 'thaiprompt-mlm')));
        }
    }
}
