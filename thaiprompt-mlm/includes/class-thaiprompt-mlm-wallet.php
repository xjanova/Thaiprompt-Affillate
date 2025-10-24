<?php
/**
 * MLM Wallet management class
 */
class Thaiprompt_MLM_Wallet {

    /**
     * Get wallet balance for user
     */
    public static function get_balance($user_id) {
        return Thaiprompt_MLM_Database::get_wallet_balance($user_id);
    }

    /**
     * Add transaction to wallet
     */
    public static function add_transaction($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_transactions';

        $defaults = array(
            'user_id' => $user_id,
            'status' => 'completed',
            'created_at' => current_time('mysql')
        );

        $data = wp_parse_args($data, $defaults);

        return $wpdb->insert($table, $data);
    }

    /**
     * Process withdrawal request
     */
    public static function process_withdrawal($user_id, $amount, $method = 'bank_transfer', $details = array()) {
        $wallet = self::get_balance($user_id);

        if (!$wallet) {
            return new WP_Error('wallet_not_found', __('Wallet not found', 'thaiprompt-mlm'));
        }

        // Check minimum withdrawal
        $settings = get_option('thaiprompt_mlm_settings', array());
        $minimum_withdrawal = $settings['payout_minimum'] ?? 100;

        if ($amount < $minimum_withdrawal) {
            return new WP_Error(
                'amount_too_low',
                sprintf(__('Minimum withdrawal amount is %s', 'thaiprompt-mlm'), $minimum_withdrawal)
            );
        }

        // Check sufficient balance
        if ($amount > floatval($wallet->balance)) {
            return new WP_Error('insufficient_balance', __('Insufficient balance', 'thaiprompt-mlm'));
        }

        // Deduct from balance
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance - %f,
                total_withdrawn = total_withdrawn + %f
            WHERE user_id = %d",
            $amount,
            $amount,
            $user_id
        ));

        // Add transaction
        self::add_transaction($user_id, array(
            'transaction_type' => 'withdrawal',
            'amount' => -$amount,
            'balance_before' => floatval($wallet->balance),
            'balance_after' => floatval($wallet->balance) - $amount,
            'description' => sprintf(__('Withdrawal via %s', 'thaiprompt-mlm'), $method),
            'status' => 'processing'
        ));

        // Create withdrawal request
        $withdrawal_id = self::create_withdrawal_request($user_id, $amount, $method, $details);

        do_action('thaiprompt_mlm_withdrawal_requested', $user_id, $amount, $withdrawal_id);

        return array(
            'success' => true,
            'withdrawal_id' => $withdrawal_id,
            'message' => __('Withdrawal request submitted successfully', 'thaiprompt-mlm')
        );
    }

    /**
     * Create withdrawal request
     */
    private static function create_withdrawal_request($user_id, $amount, $method, $details) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';

        // Create table if not exists
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            method varchar(50) NOT NULL,
            details text,
            status varchar(20) DEFAULT 'pending',
            requested_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime DEFAULT NULL,
            notes text,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Insert withdrawal request
        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'amount' => $amount,
            'method' => $method,
            'details' => json_encode($details),
            'status' => 'pending'
        ));

        return $wpdb->insert_id;
    }

    /**
     * Approve withdrawal
     */
    public static function approve_withdrawal($withdrawal_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';

        $withdrawal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $withdrawal_id
        ));

        if (!$withdrawal) {
            return new WP_Error('withdrawal_not_found', __('Withdrawal not found', 'thaiprompt-mlm'));
        }

        if ($withdrawal->status !== 'pending') {
            return new WP_Error('invalid_status', __('Withdrawal already processed', 'thaiprompt-mlm'));
        }

        // Update withdrawal status
        $wpdb->update(
            $table,
            array(
                'status' => 'completed',
                'processed_at' => current_time('mysql')
            ),
            array('id' => $withdrawal_id)
        );

        // Update transaction status
        $transactions_table = $wpdb->prefix . 'thaiprompt_mlm_transactions';
        $wpdb->update(
            $transactions_table,
            array('status' => 'completed'),
            array(
                'user_id' => $withdrawal->user_id,
                'transaction_type' => 'withdrawal',
                'amount' => -$withdrawal->amount,
                'status' => 'processing'
            )
        );

        do_action('thaiprompt_mlm_withdrawal_approved', $withdrawal_id, $withdrawal);

        return true;
    }

    /**
     * Reject withdrawal
     */
    public static function reject_withdrawal($withdrawal_id, $reason = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';

        $withdrawal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $withdrawal_id
        ));

        if (!$withdrawal) {
            return new WP_Error('withdrawal_not_found', __('Withdrawal not found', 'thaiprompt-mlm'));
        }

        // Refund to wallet
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';
        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance + %f,
                total_withdrawn = total_withdrawn - %f
            WHERE user_id = %d",
            $withdrawal->amount,
            $withdrawal->amount,
            $withdrawal->user_id
        ));

        // Update withdrawal status
        $wpdb->update(
            $table,
            array(
                'status' => 'rejected',
                'processed_at' => current_time('mysql'),
                'notes' => $reason
            ),
            array('id' => $withdrawal_id)
        );

        // Add refund transaction
        $wallet = self::get_balance($withdrawal->user_id);
        self::add_transaction($withdrawal->user_id, array(
            'transaction_type' => 'withdrawal_refund',
            'amount' => $withdrawal->amount,
            'balance_before' => floatval($wallet->balance) - $withdrawal->amount,
            'balance_after' => floatval($wallet->balance),
            'description' => sprintf(__('Withdrawal refund: %s', 'thaiprompt-mlm'), $reason),
            'status' => 'completed'
        ));

        do_action('thaiprompt_mlm_withdrawal_rejected', $withdrawal_id, $withdrawal, $reason);

        return true;
    }

    /**
     * Get user transactions
     */
    public static function get_transactions($user_id, $args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_transactions';

        $defaults = array(
            'limit' => 50,
            'offset' => 0,
            'transaction_type' => null
        );

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM $table WHERE user_id = %d";
        $params = array($user_id);

        if ($args['transaction_type']) {
            $query .= " AND transaction_type = %s";
            $params[] = $args['transaction_type'];
        }

        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get withdrawal requests
     */
    public static function get_withdrawals($user_id = null, $status = null, $limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';

        $query = "SELECT * FROM $table WHERE 1=1";
        $params = array();

        if ($user_id) {
            $query .= " AND user_id = %d";
            $params[] = $user_id;
        }

        if ($status) {
            $query .= " AND status = %s";
            $params[] = $status;
        }

        $query .= " ORDER BY requested_at DESC LIMIT %d";
        $params[] = $limit;

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }

        return $wpdb->get_results($query);
    }

    /**
     * Add funds to wallet (admin function)
     */
    public static function add_funds($user_id, $amount, $description = '') {
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wallet = self::get_balance($user_id);

        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance + %f,
                total_earned = total_earned + %f
            WHERE user_id = %d",
            $amount,
            $amount,
            $user_id
        ));

        self::add_transaction($user_id, array(
            'transaction_type' => 'admin_credit',
            'amount' => $amount,
            'balance_before' => floatval($wallet->balance),
            'balance_after' => floatval($wallet->balance) + $amount,
            'description' => $description ?: __('Admin credit', 'thaiprompt-mlm'),
            'status' => 'completed'
        ));

        return true;
    }

    /**
     * Deduct funds from wallet (admin function)
     */
    public static function deduct_funds($user_id, $amount, $description = '') {
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wallet = self::get_balance($user_id);

        if ($amount > floatval($wallet->balance)) {
            return new WP_Error('insufficient_balance', __('Insufficient balance', 'thaiprompt-mlm'));
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance - %f
            WHERE user_id = %d",
            $amount,
            $user_id
        ));

        self::add_transaction($user_id, array(
            'transaction_type' => 'admin_debit',
            'amount' => -$amount,
            'balance_before' => floatval($wallet->balance),
            'balance_after' => floatval($wallet->balance) - $amount,
            'description' => $description ?: __('Admin debit', 'thaiprompt-mlm'),
            'status' => 'completed'
        ));

        return true;
    }

    /**
     * Get wallet statistics
     */
    public static function get_wallet_stats($user_id) {
        $wallet = self::get_balance($user_id);
        $commission_stats = Thaiprompt_MLM_Commission::get_commission_summary($user_id);

        return array(
            'balance' => floatval($wallet->balance ?? 0),
            'pending_balance' => floatval($wallet->pending_balance ?? 0),
            'total_earned' => floatval($wallet->total_earned ?? 0),
            'total_withdrawn' => floatval($wallet->total_withdrawn ?? 0),
            'commission_breakdown' => $commission_stats
        );
    }
}
