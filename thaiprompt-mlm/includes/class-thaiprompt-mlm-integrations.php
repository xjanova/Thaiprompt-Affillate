<?php
/**
 * MLM Integrations class (WooCommerce, Dokan)
 */
class Thaiprompt_MLM_Integrations {

    /**
     * Initialize integrations
     */
    public static function init() {
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            self::init_woocommerce();
        }

        // Dokan integration
        if (class_exists('WeDevs_Dokan')) {
            self::init_dokan();
        }
    }

    /**
     * Initialize WooCommerce integration
     */
    private static function init_woocommerce() {
        // Hook into order completion
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'process_completed_order'), 10, 1);
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'process_completed_order'), 10, 1);

        // Add MLM settings to product
        add_action('woocommerce_product_options_general_product_data', array(__CLASS__, 'add_product_mlm_fields'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_product_mlm_fields'));

        // Add referral field to registration
        add_action('woocommerce_register_form', array(__CLASS__, 'add_referral_field'));
        add_action('woocommerce_created_customer', array(__CLASS__, 'process_new_customer'));

        // Add MLM Portal to My Account
        add_filter('woocommerce_account_menu_items', array(__CLASS__, 'add_mlm_menu_items'));
        add_action('init', array(__CLASS__, 'add_mlm_endpoints'));
        add_action('woocommerce_account_mlm-portal_endpoint', array(__CLASS__, 'mlm_portal_content'));
    }

    /**
     * Initialize Dokan integration
     */
    private static function init_dokan() {
        // Add MLM info to vendor dashboard
        add_filter('dokan_get_dashboard_nav', array(__CLASS__, 'add_dokan_mlm_menu'));
        add_action('dokan_load_custom_template', array(__CLASS__, 'load_dokan_mlm_template'));

        // Process vendor earnings
        add_action('dokan_after_withdraw_request', array(__CLASS__, 'sync_dokan_withdrawal'));
    }

    /**
     * Process completed WooCommerce order
     */
    public static function process_completed_order($order_id) {
        // Avoid duplicate processing
        if (get_post_meta($order_id, '_mlm_processed', true)) {
            return;
        }

        $result = Thaiprompt_MLM_Commission::process_order_commissions($order_id);

        if ($result) {
            update_post_meta($order_id, '_mlm_processed', true);
            update_post_meta($order_id, '_mlm_processed_date', current_time('mysql'));
        }
    }

    /**
     * Add MLM fields to product
     */
    public static function add_product_mlm_fields() {
        global $post;

        echo '<div class="options_group">';

        echo '<h3>' . __('MLM Settings', 'thaiprompt-mlm') . '</h3>';

        woocommerce_wp_checkbox(array(
            'id' => '_mlm_enabled',
            'label' => __('Enable MLM', 'thaiprompt-mlm'),
            'description' => __('Enable MLM commissions for this product', 'thaiprompt-mlm')
        ));

        woocommerce_wp_select(array(
            'id' => '_mlm_commission_type',
            'label' => __('Commission Type', 'thaiprompt-mlm'),
            'options' => array(
                'percentage' => __('Percentage', 'thaiprompt-mlm'),
                'fixed' => __('Fixed Amount', 'thaiprompt-mlm')
            )
        ));

        woocommerce_wp_text_input(array(
            'id' => '_mlm_commission_value',
            'label' => __('Commission Value', 'thaiprompt-mlm'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            )
        ));

        woocommerce_wp_checkbox(array(
            'id' => '_mlm_fast_start_enabled',
            'label' => __('Fast Start Bonus', 'thaiprompt-mlm'),
            'description' => __('Enable fast start bonus for this product', 'thaiprompt-mlm')
        ));

        woocommerce_wp_text_input(array(
            'id' => '_mlm_fast_start_value',
            'label' => __('Fast Start Value', 'thaiprompt-mlm'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            )
        ));

        woocommerce_wp_text_input(array(
            'id' => '_mlm_max_level',
            'label' => __('Max Commission Level', 'thaiprompt-mlm'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min' => '1',
                'max' => '20'
            )
        ));

        echo '</div>';
    }

    /**
     * Save product MLM fields
     */
    public static function save_product_mlm_fields($post_id) {
        $settings = array(
            'mlm_enabled' => isset($_POST['_mlm_enabled']),
            'commission_type' => sanitize_text_field($_POST['_mlm_commission_type'] ?? 'percentage'),
            'commission_value' => floatval($_POST['_mlm_commission_value'] ?? 0),
            'fast_start_enabled' => isset($_POST['_mlm_fast_start_enabled']),
            'fast_start_value' => floatval($_POST['_mlm_fast_start_value'] ?? 0),
            'max_level' => intval($_POST['_mlm_max_level'] ?? 10)
        );

        // Save as post meta
        foreach ($settings as $key => $value) {
            update_post_meta($post_id, '_' . $key, $value);
        }

        // Save to MLM database
        Thaiprompt_MLM_Database::save_product_settings($post_id, $settings);
    }

    /**
     * Add referral field to registration form
     */
    public static function add_referral_field() {
        $sponsor_id = Thaiprompt_MLM_Network::get_sponsor_from_referral();
        $sponsor_name = '';

        if ($sponsor_id) {
            $sponsor = get_userdata($sponsor_id);
            $sponsor_name = $sponsor ? $sponsor->display_name : '';
        }

        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="mlm_referral"><?php _e('Referral Code (Optional)', 'thaiprompt-mlm'); ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="mlm_referral" id="mlm_referral"
                   value="<?php echo esc_attr($sponsor_name); ?>" />
            <?php if ($sponsor_name): ?>
                <small><?php echo sprintf(__('Referred by: %s', 'thaiprompt-mlm'), $sponsor_name); ?></small>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Process new customer registration
     */
    public static function process_new_customer($customer_id) {
        $sponsor_id = null;

        // Check for referral code
        if (isset($_POST['mlm_referral']) && !empty($_POST['mlm_referral'])) {
            $referral = sanitize_text_field($_POST['mlm_referral']);
            $sponsor = get_user_by('login', $referral);

            if (!$sponsor) {
                $sponsor = get_user_by('email', $referral);
            }

            if ($sponsor) {
                $sponsor_id = $sponsor->ID;
            }
        }

        // Check session
        if (!$sponsor_id) {
            $sponsor_id = Thaiprompt_MLM_Network::get_sponsor_from_referral();
        }

        // Register in MLM network
        if ($sponsor_id) {
            Thaiprompt_MLM_Network::register_user($customer_id, $sponsor_id);
        }
    }

    /**
     * Add MLM Portal to My Account menu
     */
    public static function add_mlm_menu_items($items) {
        $mlm_items = array(
            'mlm-portal' => __('MLM Portal', 'thaiprompt-mlm')
        );

        // Insert before logout
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        $items = array_merge($items, $mlm_items);
        $items['customer-logout'] = $logout;

        return $items;
    }

    /**
     * Add MLM Portal endpoint
     */
    public static function add_mlm_endpoints() {
        add_rewrite_endpoint('mlm-portal', EP_ROOT | EP_PAGES);
    }

    /**
     * MLM Portal content - redirect to portal page
     */
    public static function mlm_portal_content() {
        $portal_page_id = get_option('thaiprompt_mlm_page_portal');
        if ($portal_page_id) {
            wp_redirect(get_permalink($portal_page_id));
            exit;
        } else {
            echo '<p>' . __('Portal page not found. Please contact administrator.', 'thaiprompt-mlm') . '</p>';
        }
    }

    /**
     * Add MLM menu to Dokan dashboard
     */
    public static function add_dokan_mlm_menu($urls) {
        $urls['mlm'] = array(
            'title' => __('MLM Network', 'thaiprompt-mlm'),
            'icon'  => '<i class="fas fa-sitemap"></i>',
            'url'   => dokan_get_navigation_url('mlm'),
            'pos'   => 55
        );

        return $urls;
    }

    /**
     * Load Dokan MLM template
     */
    public static function load_dokan_mlm_template($query_vars) {
        if (isset($query_vars['mlm'])) {
            include THAIPROMPT_MLM_PLUGIN_DIR . 'public/partials/dokan-mlm.php';
        }
    }

    /**
     * Sync Dokan withdrawal with MLM wallet
     */
    public static function sync_dokan_withdrawal($user_id) {
        // This can be used to sync Dokan vendor withdrawals with MLM wallet if needed
        // Implementation depends on specific requirements
    }

    /**
     * Get order MLM info (for admin)
     */
    public static function get_order_mlm_info($order_id) {
        global $wpdb;
        $commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $commissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $commissions_table WHERE order_id = %d ORDER BY level ASC",
            $order_id
        ));

        $total_commission = 0;
        $commission_details = array();

        foreach ($commissions as $commission) {
            $user = get_userdata($commission->user_id);
            $commission_details[] = array(
                'user_id' => $commission->user_id,
                'user_name' => $user ? $user->display_name : 'Unknown',
                'type' => $commission->commission_type,
                'amount' => floatval($commission->amount),
                'status' => $commission->status
            );
            $total_commission += floatval($commission->amount);
        }

        return array(
            'processed' => get_post_meta($order_id, '_mlm_processed', true),
            'processed_date' => get_post_meta($order_id, '_mlm_processed_date', true),
            'total_commission' => $total_commission,
            'commissions' => $commission_details
        );
    }

    /**
     * Add MLM info to order admin page
     */
    public static function display_order_mlm_info($order) {
        $mlm_info = self::get_order_mlm_info($order->get_id());

        if ($mlm_info['processed']): ?>
            <div class="order_data_column">
                <h3><?php _e('MLM Information', 'thaiprompt-mlm'); ?></h3>
                <p>
                    <strong><?php _e('Total Commissions:', 'thaiprompt-mlm'); ?></strong>
                    <?php echo wc_price($mlm_info['total_commission']); ?>
                </p>
                <p>
                    <strong><?php _e('Processed:', 'thaiprompt-mlm'); ?></strong>
                    <?php echo $mlm_info['processed_date']; ?>
                </p>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mlm_info['commissions'] as $comm): ?>
                            <tr>
                                <td><?php echo esc_html($comm['user_name']); ?></td>
                                <td><?php echo esc_html($comm['type']); ?></td>
                                <td><?php echo wc_price($comm['amount']); ?></td>
                                <td><?php echo esc_html($comm['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif;
    }
}

// Initialize integrations
add_action('plugins_loaded', array('Thaiprompt_MLM_Integrations', 'init'));
