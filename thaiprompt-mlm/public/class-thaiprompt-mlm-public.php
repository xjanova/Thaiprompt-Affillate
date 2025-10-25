<?php
/**
 * Public-facing functionality
 */
class Thaiprompt_MLM_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register public styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            THAIPROMPT_MLM_PLUGIN_URL . 'public/css/thaiprompt-mlm-public.css',
            array(),
            $this->version,
            'all'
        );

        // Enqueue portal styles when on portal page
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
     * Register public scripts
     */
    public function enqueue_scripts() {
        // GSAP for genealogy tree animation
        wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true);

        // Main public script
        wp_enqueue_script(
            $this->plugin_name,
            THAIPROMPT_MLM_PLUGIN_URL . 'public/js/thaiprompt-mlm-public.js',
            array('jquery', 'gsap'),
            $this->version,
            true
        );

        // Genealogy tree script with GSAP
        wp_enqueue_script(
            $this->plugin_name . '-genealogy',
            THAIPROMPT_MLM_PLUGIN_URL . 'public/js/thaiprompt-mlm-genealogy.js',
            array('jquery', 'gsap'),
            $this->version,
            true
        );

        // Portal script when on portal page
        if (is_page_template('mlm-portal-template.php') || is_page('mlm-portal')) {
            wp_enqueue_script(
                $this->plugin_name . '-portal',
                THAIPROMPT_MLM_PLUGIN_URL . 'public/js/thaiprompt-mlm-portal.js',
                array('jquery', 'gsap'),
                $this->version,
                true
            );
        }

        // Localize script
        wp_localize_script($this->plugin_name, 'thaipromptMLM', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thaiprompt_mlm_public_nonce'),
            'user_id' => get_current_user_id()
        ));
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('mlm_dashboard', array($this, 'shortcode_dashboard'));
        add_shortcode('mlm_genealogy', array($this, 'shortcode_genealogy'));
        add_shortcode('mlm_wallet', array($this, 'shortcode_wallet'));
        add_shortcode('mlm_referral_link', array($this, 'shortcode_referral_link'));
        add_shortcode('mlm_team_stats', array($this, 'shortcode_team_stats'));
        add_shortcode('mlm_rank_progress', array($this, 'shortcode_rank_progress'));
        add_shortcode('mlm_commissions', array($this, 'shortcode_commissions'));
        add_shortcode('mlm_leaderboard', array($this, 'shortcode_leaderboard'));

        // AJAX handlers for public
        add_action('wp_ajax_thaiprompt_mlm_get_user_genealogy', array($this, 'ajax_get_user_genealogy'));
        add_action('wp_ajax_thaiprompt_mlm_withdraw_request', array($this, 'ajax_withdraw_request'));
        add_action('wp_ajax_thaiprompt_mlm_copy_referral', array($this, 'ajax_copy_referral'));
    }

    /**
     * Shortcode: MLM Dashboard
     */
    public function shortcode_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please login to view your MLM dashboard.', 'thaiprompt-mlm') . '</p>';
        }

        $user_id = get_current_user_id();
        $position = Thaiprompt_MLM_Network::get_user_position($user_id);
        $team_stats = Thaiprompt_MLM_Network::get_team_stats($user_id);
        $wallet_stats = Thaiprompt_MLM_Wallet::get_wallet_stats($user_id);
        $rank = Thaiprompt_MLM_Database::get_user_rank($user_id);
        $rank_progress = Thaiprompt_MLM_Rank::get_rank_progress($user_id);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: Genealogy Tree
     */
    public function shortcode_genealogy($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please login to view your genealogy tree.', 'thaiprompt-mlm') . '</p>';
        }

        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'max_depth' => 5
        ), $atts);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/genealogy.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: Wallet
     */
    public function shortcode_wallet($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please login to view your wallet.', 'thaiprompt-mlm') . '</p>';
        }

        $user_id = get_current_user_id();
        $wallet = Thaiprompt_MLM_Wallet::get_balance($user_id);
        $transactions = Thaiprompt_MLM_Wallet::get_transactions($user_id, array('limit' => 20));
        $withdrawals = Thaiprompt_MLM_Wallet::get_withdrawals($user_id);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/wallet.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: Referral Link
     */
    public function shortcode_referral_link($atts) {
        if (!is_user_logged_in()) {
            return '';
        }

        $user_id = get_current_user_id();
        $referral_link = Thaiprompt_MLM_Network::get_referral_link($user_id);

        return '<div class="mlm-referral-link">
            <input type="text" value="' . esc_attr($referral_link) . '" readonly class="mlm-referral-input" />
            <button class="mlm-copy-button" data-clipboard-text="' . esc_attr($referral_link) . '">' .
                __('Copy', 'thaiprompt-mlm') .
            '</button>
        </div>';
    }

    /**
     * Shortcode: Team Stats
     */
    public function shortcode_team_stats($atts) {
        if (!is_user_logged_in()) {
            return '';
        }

        $user_id = get_current_user_id();
        $team_stats = Thaiprompt_MLM_Network::get_team_stats($user_id);

        ob_start();
        ?>
        <div class="mlm-team-stats">
            <div class="stat-item">
                <span class="stat-label"><?php _e('Total Team', 'thaiprompt-mlm'); ?></span>
                <span class="stat-value"><?php echo number_format($team_stats['total_team']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Active Members', 'thaiprompt-mlm'); ?></span>
                <span class="stat-value"><?php echo number_format($team_stats['active_members']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Total Sales', 'thaiprompt-mlm'); ?></span>
                <span class="stat-value"><?php echo wc_price($team_stats['total_sales']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Left Leg', 'thaiprompt-mlm'); ?></span>
                <span class="stat-value"><?php echo wc_price($team_stats['left_leg_sales']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Right Leg', 'thaiprompt-mlm'); ?></span>
                <span class="stat-value"><?php echo wc_price($team_stats['right_leg_sales']); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: Rank Progress
     */
    public function shortcode_rank_progress($atts) {
        if (!is_user_logged_in()) {
            return '';
        }

        $user_id = get_current_user_id();
        $rank_progress = Thaiprompt_MLM_Rank::get_rank_progress($user_id);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/rank-progress.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: Commissions
     */
    public function shortcode_commissions($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please login to view your commissions.', 'thaiprompt-mlm') . '</p>';
        }

        $user_id = get_current_user_id();
        $commissions = Thaiprompt_MLM_Database::get_user_commissions($user_id, array('limit' => 50));
        $commission_stats = Thaiprompt_MLM_Commission::get_commission_summary($user_id);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/commissions.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: Leaderboard
     */
    public function shortcode_leaderboard($atts) {
        $atts = shortcode_atts(array(
            'limit' => 50
        ), $atts);

        $leaderboard = Thaiprompt_MLM_Rank::get_leaderboard($atts['limit']);

        ob_start();
        include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/leaderboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Get user genealogy
     */
    public function ajax_get_user_genealogy() {
        check_ajax_referer('thaiprompt_mlm_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please login', 'thaiprompt-mlm')));
        }

        $user_id = intval($_POST['user_id'] ?? get_current_user_id());
        $max_depth = intval($_POST['max_depth'] ?? 5);

        $tree = Thaiprompt_MLM_Database::get_genealogy_tree($user_id, $max_depth);

        if ($tree) {
            wp_send_json_success($tree);
        } else {
            wp_send_json_error(array('message' => __('User not found', 'thaiprompt-mlm')));
        }
    }

    /**
     * AJAX: Withdraw request
     */
    public function ajax_withdraw_request() {
        check_ajax_referer('thaiprompt_mlm_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please login', 'thaiprompt-mlm')));
        }

        $user_id = get_current_user_id();
        $amount = floatval($_POST['amount']);
        $method = sanitize_text_field($_POST['method']);
        $details = array();

        if (isset($_POST['bank_name'])) {
            $details['bank_name'] = sanitize_text_field($_POST['bank_name']);
        }
        if (isset($_POST['account_number'])) {
            $details['account_number'] = sanitize_text_field($_POST['account_number']);
        }
        if (isset($_POST['account_name'])) {
            $details['account_name'] = sanitize_text_field($_POST['account_name']);
        }

        $result = Thaiprompt_MLM_Wallet::process_withdrawal($user_id, $amount, $method, $details);

        if (!is_wp_error($result)) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
    }

    /**
     * AJAX: Copy referral link
     */
    public function ajax_copy_referral() {
        check_ajax_referer('thaiprompt_mlm_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please login', 'thaiprompt-mlm')));
        }

        $user_id = get_current_user_id();
        $referral_link = Thaiprompt_MLM_Network::get_referral_link($user_id);

        wp_send_json_success(array('link' => $referral_link));
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
