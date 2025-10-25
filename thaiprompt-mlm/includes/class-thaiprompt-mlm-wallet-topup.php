<?php
/**
 * Wallet Top-up via WooCommerce
 *
 * Handles wallet top-up through hidden WooCommerce products
 * These products are excluded from commissions
 *
 * @since 1.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_Wallet_Topup {

    /**
     * Initialize hooks
     */
    public static function init() {
        // Hide wallet products from shop
        add_filter('woocommerce_product_is_visible', array(__CLASS__, 'hide_wallet_products'), 10, 2);

        // Exclude from search
        add_action('pre_get_posts', array(__CLASS__, 'exclude_from_search'));

        // Handle order completion
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'process_wallet_topup'), 10, 1);
        add_action('woocommerce_payment_complete', array(__CLASS__, 'process_wallet_topup'), 10, 1);

        // Exclude from commission calculation
        add_filter('thaiprompt_mlm_order_eligible_for_commission', array(__CLASS__, 'exclude_from_commission'), 10, 2);

        // Add custom product type (optional - for better organization)
        add_filter('product_type_selector', array(__CLASS__, 'add_wallet_product_type'));
    }

    /**
     * Create wallet top-up product
     *
     * @param float $amount Amount to top-up
     * @return int|WP_Error Product ID or error
     */
    public static function create_topup_product($amount) {
        if (!class_exists('WC_Product')) {
            return new WP_Error('no_woocommerce', __('WooCommerce is not active', 'thaiprompt-mlm'));
        }

        // Create product
        $product = new WC_Product_Simple();
        $product->set_name('Wallet Top-up: ฿' . number_format($amount, 2));
        $product->set_regular_price($amount);
        $product->set_virtual(true); // Virtual product
        $product->set_catalog_visibility('hidden'); // Hide from catalog
        $product->set_status('private'); // Private status

        // Add meta to identify as wallet product
        $product->add_meta_data('_mlm_wallet_topup', 'yes', true);
        $product->add_meta_data('_mlm_topup_amount', $amount, true);
        $product->add_meta_data('_mlm_exclude_commission', 'yes', true);

        // Save product
        $product_id = $product->save();

        Thaiprompt_MLM_Logger::info('Wallet top-up product created', array(
            'product_id' => $product_id,
            'amount' => $amount
        ));

        return $product_id;
    }

    /**
     * Get or create wallet product for specific amount
     *
     * @param float $amount Amount
     * @return int Product ID
     */
    public static function get_or_create_wallet_product($amount) {
        // Search for existing product
        $args = array(
            'post_type' => 'product',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_mlm_wallet_topup',
                    'value' => 'yes'
                ),
                array(
                    'key' => '_mlm_topup_amount',
                    'value' => $amount,
                    'type' => 'NUMERIC'
                )
            )
        );

        $products = get_posts($args);

        if (!empty($products)) {
            return $products[0]->ID;
        }

        // Create new product
        return self::create_topup_product($amount);
    }

    /**
     * Get wallet top-up URL for user
     *
     * @param int $user_id User ID
     * @param float $amount Amount to top-up
     * @return string Add to cart URL
     */
    public static function get_topup_url($user_id, $amount) {
        $product_id = self::get_or_create_wallet_product($amount);

        if (is_wp_error($product_id)) {
            return '';
        }

        // Build add to cart URL with user ID in meta
        $url = add_query_arg(array(
            'add-to-cart' => $product_id,
            'mlm_user_id' => $user_id,
            'mlm_topup' => 1
        ), wc_get_checkout_url());

        return $url;
    }

    /**
     * Hide wallet products from shop
     */
    public static function hide_wallet_products($visible, $product_id) {
        $is_wallet = get_post_meta($product_id, '_mlm_wallet_topup', true);

        if ($is_wallet === 'yes') {
            return false; // Hide from catalog
        }

        return $visible;
    }

    /**
     * Exclude wallet products from search
     */
    public static function exclude_from_search($query) {
        if (!is_admin() && $query->is_search() && $query->is_main_query()) {
            $meta_query = array(
                array(
                    'key' => '_mlm_wallet_topup',
                    'compare' => 'NOT EXISTS'
                )
            );

            $query->set('meta_query', $meta_query);
        }
    }

    /**
     * Process wallet top-up when order is completed
     */
    public static function process_wallet_topup($order_id) {
        // Check if already processed
        $processed = get_post_meta($order_id, '_mlm_wallet_processed', true);
        if ($processed) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $user_id = $order->get_user_id();
        if (!$user_id) {
            // Try to get from order meta
            $user_id = $order->get_meta('mlm_user_id');
        }

        if (!$user_id) {
            return;
        }

        $total_topup = 0;

        // Check each item
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $is_wallet = $product->get_meta('_mlm_wallet_topup');

            if ($is_wallet === 'yes') {
                $amount = $product->get_meta('_mlm_topup_amount');

                if ($amount) {
                    $total_topup += floatval($amount) * $item->get_quantity();
                }
            }
        }

        if ($total_topup > 0) {
            // Add to wallet
            $current_balance = Thaiprompt_MLM_Wallet::get_balance($user_id);
            $new_balance = $current_balance + $total_topup;

            Thaiprompt_MLM_Wallet::update_balance($user_id, $new_balance);

            // Record transaction
            global $wpdb;
            $table = $wpdb->prefix . 'mlm_wallet_transactions';

            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'type' => 'topup',
                'amount' => $total_topup,
                'balance_after' => $new_balance,
                'description' => 'เติมเงินผ่าน WooCommerce Order #' . $order_id,
                'reference_id' => $order_id,
                'status' => 'completed',
                'created_at' => current_time('mysql')
            ));

            // Mark as processed
            $order->update_meta_data('_mlm_wallet_processed', 'yes');
            $order->update_meta_data('_mlm_wallet_amount', $total_topup);
            $order->save();

            // Add order note
            $order->add_order_note(
                sprintf(
                    __('Wallet topped up: ฿%s. New balance: ฿%s', 'thaiprompt-mlm'),
                    number_format($total_topup, 2),
                    number_format($new_balance, 2)
                )
            );

            Thaiprompt_MLM_Logger::info('Wallet topped up via WooCommerce', array(
                'user_id' => $user_id,
                'order_id' => $order_id,
                'amount' => $total_topup,
                'new_balance' => $new_balance
            ));
        }
    }

    /**
     * Exclude wallet products from commission calculation
     */
    public static function exclude_from_commission($eligible, $order) {
        if (!$eligible) {
            return $eligible;
        }

        // Check if order contains wallet products
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $is_wallet = $product->get_meta('_mlm_wallet_topup');
            $exclude_commission = $product->get_meta('_mlm_exclude_commission');

            if ($is_wallet === 'yes' || $exclude_commission === 'yes') {
                return false; // Not eligible for commission
            }
        }

        return $eligible;
    }

    /**
     * Add wallet product type to selector (optional)
     */
    public static function add_wallet_product_type($types) {
        $types['mlm_wallet'] = __('MLM Wallet Top-up', 'thaiprompt-mlm');
        return $types;
    }

    /**
     * Get wallet top-up amounts (predefined)
     */
    public static function get_topup_amounts() {
        return apply_filters('thaiprompt_mlm_wallet_topup_amounts', array(
            100,
            500,
            1000,
            2000,
            5000,
            10000
        ));
    }

    /**
     * Create all predefined wallet products
     */
    public static function create_all_wallet_products() {
        $amounts = self::get_topup_amounts();
        $created = array();

        foreach ($amounts as $amount) {
            $product_id = self::get_or_create_wallet_product($amount);
            if (!is_wp_error($product_id)) {
                $created[] = $product_id;
            }
        }

        return $created;
    }

    /**
     * Get wallet products list
     */
    public static function get_wallet_products() {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'private',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_mlm_wallet_topup',
                    'value' => 'yes'
                )
            )
        );

        return get_posts($args);
    }
}

// Initialize
Thaiprompt_MLM_Wallet_Topup::init();
