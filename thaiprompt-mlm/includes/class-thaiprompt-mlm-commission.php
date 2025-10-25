<?php
/**
 * MLM Commission calculation class
 */
class Thaiprompt_MLM_Commission {

    /**
     * Calculate and distribute commissions for an order
     */
    public static function process_order_commissions($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $customer_id = $order->get_customer_id();
        if (!$customer_id) {
            return false;
        }

        $settings = get_option('thaiprompt_mlm_settings', array());
        $max_level = $settings['max_level'] ?? 10;

        // Get order total
        $order_total = $order->get_total();

        // Get user's network position
        $network_data = Thaiprompt_MLM_Database::get_user_network($customer_id);
        if (!$network_data || !$network_data->sponsor_id) {
            return false;
        }

        // Update personal sales
        Thaiprompt_MLM_Database::update_user_sales($customer_id, $order_total, 'personal');

        // Process each product in order
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_total = $item->get_total();

            self::process_product_commissions($product_id, $product_total, $customer_id, $order_id);
        }

        // Calculate level commissions
        self::calculate_level_commissions($customer_id, $order_total, $order_id, $max_level);

        // Check for fast start bonus
        if ($settings['fast_start_enabled'] ?? false) {
            self::check_fast_start_bonus($customer_id, $order_total, $order_id);
        }

        // Trigger rank calculation
        do_action('thaiprompt_mlm_order_processed', $order_id, $customer_id);

        return true;
    }

    /**
     * Process commissions for a specific product
     */
    private static function process_product_commissions($product_id, $amount, $customer_id, $order_id) {
        // Check if product has custom MLM settings
        $product_settings = Thaiprompt_MLM_Database::get_product_settings($product_id);

        if ($product_settings && !$product_settings->mlm_enabled) {
            return; // MLM disabled for this product
        }

        if ($product_settings && $product_settings->settings_override) {
            // Use product-specific settings
            $override = json_decode($product_settings->settings_override, true);
            // Process with override settings if needed
        }
    }

    /**
     * Calculate level commissions up the sponsor chain
     */
    private static function calculate_level_commissions($customer_id, $amount, $order_id, $max_level) {
        $settings = get_option('thaiprompt_mlm_settings', array());
        $level_commissions = $settings['level_commissions'] ?? array();

        $current_user_id = $customer_id;
        $network_data = Thaiprompt_MLM_Database::get_user_network($current_user_id);

        if (!$network_data || !$network_data->sponsor_id) {
            return;
        }

        $current_sponsor_id = $network_data->sponsor_id;
        $level = 1;

        while ($current_sponsor_id && $level <= $max_level) {
            // Get commission percentage for this level
            $commission_percentage = isset($level_commissions[$level]) ? floatval($level_commissions[$level]) : 0;

            if ($commission_percentage > 0) {
                $commission_amount = ($amount * $commission_percentage) / 100;

                // Add commission record
                Thaiprompt_MLM_Database::add_commission($current_sponsor_id, array(
                    'from_user_id' => $customer_id,
                    'order_id' => $order_id,
                    'commission_type' => 'level_' . $level,
                    'amount' => $commission_amount,
                    'percentage' => $commission_percentage,
                    'level' => $level,
                    'status' => 'pending',
                    'description' => sprintf(
                        __('Level %d commission from order #%d', 'thaiprompt-mlm'),
                        $level,
                        $order_id
                    )
                ));

                // Update wallet pending balance
                self::add_to_wallet($current_sponsor_id, $commission_amount, 'pending');
            }

            // Move up one level
            $sponsor_data = Thaiprompt_MLM_Database::get_user_network($current_sponsor_id);
            if (!$sponsor_data || !$sponsor_data->sponsor_id) {
                break;
            }

            $current_sponsor_id = $sponsor_data->sponsor_id;
            $level++;
        }
    }

    /**
     * Check and award fast start bonus
     */
    private static function check_fast_start_bonus($customer_id, $amount, $order_id) {
        $settings = get_option('thaiprompt_mlm_settings', array());
        $fast_start_percentage = $settings['fast_start_percentage'] ?? 10;
        $fast_start_days = $settings['fast_start_days'] ?? 30;

        $network_data = Thaiprompt_MLM_Database::get_user_network($customer_id);
        if (!$network_data || !$network_data->sponsor_id) {
            return;
        }

        // Check if customer is within fast start period
        $user = get_userdata($customer_id);
        if (!$user) {
            return;
        }

        $registration_date = strtotime($user->user_registered);
        $current_date = current_time('timestamp');
        $days_since_registration = ($current_date - $registration_date) / (60 * 60 * 24);

        if ($days_since_registration <= $fast_start_days) {
            $bonus_amount = ($amount * $fast_start_percentage) / 100;
            $sponsor_id = $network_data->sponsor_id;

            // Add fast start bonus commission
            Thaiprompt_MLM_Database::add_commission($sponsor_id, array(
                'from_user_id' => $customer_id,
                'order_id' => $order_id,
                'commission_type' => 'fast_start',
                'amount' => $bonus_amount,
                'percentage' => $fast_start_percentage,
                'status' => 'pending',
                'description' => sprintf(
                    __('Fast Start Bonus from order #%d', 'thaiprompt-mlm'),
                    $order_id
                )
            ));

            self::add_to_wallet($sponsor_id, $bonus_amount, 'pending');
        }
    }

    /**
     * Approve commission (move from pending to available)
     */
    public static function approve_commission($commission_id) {
        global $wpdb;
        $commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $commission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $commissions_table WHERE id = %d",
            $commission_id
        ));

        if (!$commission || $commission->status !== 'pending') {
            return false;
        }

        // Update commission status
        $wpdb->update(
            $commissions_table,
            array('status' => 'approved'),
            array('id' => $commission_id)
        );

        // Move from pending to available balance
        self::move_pending_to_balance($commission->user_id, $commission->amount);

        do_action('thaiprompt_mlm_commission_approved', $commission_id, $commission);

        return true;
    }

    /**
     * Approve all pending commissions for an order
     */
    public static function approve_order_commissions($order_id) {
        global $wpdb;
        $commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $commissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $commissions_table WHERE order_id = %d AND status = 'pending'",
            $order_id
        ));

        foreach ($commissions as $commission) {
            self::approve_commission($commission->id);
        }

        return count($commissions);
    }

    /**
     * Add amount to wallet
     */
    private static function add_to_wallet($user_id, $amount, $type = 'balance') {
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        // Get or create wallet
        $wallet = Thaiprompt_MLM_Database::get_wallet_balance($user_id);

        if ($type === 'pending') {
            $wpdb->query($wpdb->prepare(
                "UPDATE $wallet_table SET pending_balance = pending_balance + %f WHERE user_id = %d",
                $amount,
                $user_id
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "UPDATE $wallet_table
                SET balance = balance + %f,
                    total_earned = total_earned + %f
                WHERE user_id = %d",
                $amount,
                $amount,
                $user_id
            ));

            // Add transaction record
            Thaiprompt_MLM_Wallet::add_transaction($user_id, array(
                'transaction_type' => 'commission',
                'amount' => $amount,
                'balance_before' => floatval($wallet->balance),
                'balance_after' => floatval($wallet->balance) + $amount,
                'description' => __('Commission earned', 'thaiprompt-mlm'),
                'status' => 'completed'
            ));
        }
    }

    /**
     * Move pending balance to available balance
     */
    private static function move_pending_to_balance($user_id, $amount) {
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wallet = Thaiprompt_MLM_Database::get_wallet_balance($user_id);

        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance + %f,
                pending_balance = pending_balance - %f,
                total_earned = total_earned + %f
            WHERE user_id = %d",
            $amount,
            $amount,
            $amount,
            $user_id
        ));

        // Add transaction
        Thaiprompt_MLM_Wallet::add_transaction($user_id, array(
            'transaction_type' => 'commission_approved',
            'amount' => $amount,
            'balance_before' => floatval($wallet->balance),
            'balance_after' => floatval($wallet->balance) + $amount,
            'description' => __('Commission approved', 'thaiprompt-mlm'),
            'status' => 'completed'
        ));
    }

    /**
     * Calculate binary commission (for binary compensation plan)
     */
    public static function calculate_binary_commission($user_id) {
        $settings = get_option('thaiprompt_mlm_settings', array());
        $binary_percentage = $settings['binary_percentage'] ?? 10;

        $team_stats = Thaiprompt_MLM_Network::get_team_stats($user_id);

        // Calculate based on weaker leg
        $weaker_leg = min($team_stats['left_leg_sales'], $team_stats['right_leg_sales']);
        $commission_amount = ($weaker_leg * $binary_percentage) / 100;

        if ($commission_amount > 0) {
            // Add binary commission
            Thaiprompt_MLM_Database::add_commission($user_id, array(
                'commission_type' => 'binary',
                'amount' => $commission_amount,
                'percentage' => $binary_percentage,
                'status' => 'approved',
                'description' => __('Binary commission', 'thaiprompt-mlm')
            ));

            self::add_to_wallet($user_id, $commission_amount, 'balance');

            return $commission_amount;
        }

        return 0;
    }

    /**
     * Get commission summary for user
     */
    public static function get_commission_summary($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $summary = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_earned,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN commission_type LIKE 'level%%' THEN amount ELSE 0 END) as level_commissions,
                SUM(CASE WHEN commission_type = 'fast_start' THEN amount ELSE 0 END) as fast_start,
                SUM(CASE WHEN commission_type = 'binary' THEN amount ELSE 0 END) as `binary_commission`,
                SUM(CASE WHEN commission_type = 'rank_bonus' THEN amount ELSE 0 END) as rank_bonus,
                COUNT(*) as total_transactions
            FROM $table WHERE user_id = %d",
            $user_id
        ));

        return array(
            'total_earned' => floatval($summary->total_earned ?? 0),
            'pending' => floatval($summary->pending ?? 0),
            'level_commissions' => floatval($summary->level_commissions ?? 0),
            'fast_start' => floatval($summary->fast_start ?? 0),
            'binary' => floatval($summary->binary_commission ?? 0),
            'rank_bonus' => floatval($summary->rank_bonus ?? 0),
            'total_transactions' => intval($summary->total_transactions ?? 0)
        );
    }
}
