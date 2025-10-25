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

        // Check LINE KYC verification
        if (!self::is_line_verified($user_id)) {
            return new WP_Error(
                'kyc_required',
                __('LINE verification required. Please add our LINE Official Account and ensure you are following us to withdraw funds.', 'thaiprompt-mlm')
            );
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
            'message' => __('Withdrawal request submitted successfully. Admin will process your request and notify you via LINE.', 'thaiprompt-mlm')
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
            processed_by bigint(20) DEFAULT NULL,
            slip_attachment_id bigint(20) DEFAULT NULL,
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
     * Approve withdrawal with slip attachment and LINE notification
     *
     * @param int $withdrawal_id Withdrawal ID
     * @param int $slip_attachment_id WordPress attachment ID of slip image
     * @param string $notes Admin notes
     * @return bool|WP_Error True on success, error on failure
     */
    public static function approve_withdrawal($withdrawal_id, $slip_attachment_id = 0, $notes = '') {
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
        $update_data = array(
            'status' => 'completed',
            'processed_at' => current_time('mysql'),
            'processed_by' => get_current_user_id()
        );

        if ($slip_attachment_id) {
            $update_data['slip_attachment_id'] = $slip_attachment_id;
        }

        if ($notes) {
            $update_data['notes'] = $notes;
        }

        $wpdb->update(
            $table,
            $update_data,
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

        // Send LINE notification
        self::send_withdrawal_notification($withdrawal->user_id, $withdrawal, $slip_attachment_id);

        do_action('thaiprompt_mlm_withdrawal_approved', $withdrawal_id, $withdrawal);

        Thaiprompt_MLM_Logger::info('Withdrawal approved', array(
            'withdrawal_id' => $withdrawal_id,
            'user_id' => $withdrawal->user_id,
            'amount' => $withdrawal->amount,
            'has_slip' => !empty($slip_attachment_id)
        ));

        return true;
    }

    /**
     * Send withdrawal notification via LINE
     *
     * @param int $user_id User ID
     * @param object $withdrawal Withdrawal data
     * @param int $slip_attachment_id Slip image attachment ID
     */
    private static function send_withdrawal_notification($user_id, $withdrawal, $slip_attachment_id = 0) {
        // Check if LINE Bot is available
        if (!class_exists('Thaiprompt_MLM_LINE_Bot')) {
            return;
        }

        // Get LINE user ID
        $line_user_id = get_user_meta($user_id, 'line_user_id', true);

        if (empty($line_user_id)) {
            Thaiprompt_MLM_Logger::warning('Cannot send withdrawal notification: No LINE user ID', array(
                'user_id' => $user_id
            ));
            return;
        }

        // Prepare messages
        $messages = array();

        // Text message
        $text_message = "✅ การถอนเงินของคุณสำเร็จแล้ว!\n\n" .
                       "💰 จำนวน: " . number_format($withdrawal->amount, 2) . " บาท\n" .
                       "📅 วันที่: " . date('d/m/Y H:i', strtotime($withdrawal->processed_at)) . "\n\n";

        if (!empty($withdrawal->notes)) {
            $text_message .= "📝 หมายเหตุ: " . $withdrawal->notes . "\n\n";
        }

        $text_message .= "กรุณาตรวจสอบบัญชีของคุณ 🙏";

        $messages[] = Thaiprompt_MLM_LINE_Bot::build_text_message($text_message);

        // Add slip image if available
        if ($slip_attachment_id) {
            $slip_url = wp_get_attachment_url($slip_attachment_id);

            if ($slip_url) {
                // Make sure URL is HTTPS for LINE
                $slip_url = str_replace('http://', 'https://', $slip_url);

                $messages[] = Thaiprompt_MLM_LINE_Bot::build_image_message($slip_url);

                Thaiprompt_MLM_Logger::info('Sending withdrawal notification with slip', array(
                    'user_id' => $user_id,
                    'line_user_id' => $line_user_id,
                    'slip_url' => $slip_url
                ));
            }
        }

        // Send push message
        $result = Thaiprompt_MLM_LINE_Bot::push_message($line_user_id, $messages);

        if (is_wp_error($result)) {
            Thaiprompt_MLM_Logger::error('Failed to send withdrawal notification', array(
                'user_id' => $user_id,
                'line_user_id' => $line_user_id,
                'error' => $result->get_error_message()
            ));
        }
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

    /**
     * Transfer funds between members
     *
     * @param int $from_user_id Sender user ID
     * @param int $to_user_id Receiver user ID (or username/referral code)
     * @param float $amount Amount to transfer
     * @param string $note Optional note
     * @return array|WP_Error Transfer result or error
     */
    public static function transfer_funds($from_user_id, $to_user_id, $amount, $note = '') {
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        // Get receiver user ID if username or referral code provided
        if (!is_numeric($to_user_id)) {
            $to_user_id = self::resolve_user_identifier($to_user_id);

            if (!$to_user_id) {
                return new WP_Error('user_not_found', __('Recipient not found', 'thaiprompt-mlm'));
            }
        }

        // Validate users exist
        $from_user = get_userdata($from_user_id);
        $to_user = get_userdata($to_user_id);

        if (!$from_user || !$to_user) {
            return new WP_Error('invalid_users', __('Invalid sender or recipient', 'thaiprompt-mlm'));
        }

        // Cannot transfer to self
        if ($from_user_id === $to_user_id) {
            return new WP_Error('self_transfer', __('Cannot transfer to yourself', 'thaiprompt-mlm'));
        }

        // Validate amount
        if ($amount <= 0) {
            return new WP_Error('invalid_amount', __('Invalid transfer amount', 'thaiprompt-mlm'));
        }

        // Get minimum transfer amount
        $settings = get_option('thaiprompt_mlm_settings', array());
        $minimum_transfer = isset($settings['wallet_minimum_transfer']) ? floatval($settings['wallet_minimum_transfer']) : 10;

        if ($amount < $minimum_transfer) {
            return new WP_Error(
                'amount_too_low',
                sprintf(__('Minimum transfer amount is %s', 'thaiprompt-mlm'), wc_price($minimum_transfer))
            );
        }

        // Calculate transfer fee
        $fee = self::calculate_transfer_fee($amount);
        $total_deduction = $amount + $fee;

        // Check sender balance
        $from_wallet = self::get_balance($from_user_id);
        if (!$from_wallet || floatval($from_wallet->balance) < $total_deduction) {
            return new WP_Error(
                'insufficient_balance',
                sprintf(__('Insufficient balance. Required: %s (including fee: %s)', 'thaiprompt-mlm'), wc_price($total_deduction), wc_price($fee))
            );
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Deduct from sender (amount + fee)
            $result1 = $wpdb->query($wpdb->prepare(
                "UPDATE $wallet_table SET balance = balance - %f WHERE user_id = %d",
                $total_deduction,
                $from_user_id
            ));

            // Add to receiver (amount only)
            $result2 = $wpdb->query($wpdb->prepare(
                "UPDATE $wallet_table SET balance = balance + %f WHERE user_id = %d",
                $amount,
                $to_user_id
            ));

            if ($result1 === false || $result2 === false) {
                throw new Exception('Database update failed');
            }

            // Record sender transaction
            self::add_transaction($from_user_id, array(
                'transaction_type' => 'transfer_sent',
                'amount' => -$amount,
                'balance_before' => floatval($from_wallet->balance),
                'balance_after' => floatval($from_wallet->balance) - $total_deduction,
                'description' => sprintf(
                    __('Transfer to %s%s', 'thaiprompt-mlm'),
                    $to_user->display_name,
                    $note ? ' - ' . $note : ''
                ),
                'status' => 'completed',
                'related_user_id' => $to_user_id
            ));

            // Record transfer fee (if applicable)
            if ($fee > 0) {
                self::add_transaction($from_user_id, array(
                    'transaction_type' => 'transfer_fee',
                    'amount' => -$fee,
                    'balance_before' => floatval($from_wallet->balance) - $amount,
                    'balance_after' => floatval($from_wallet->balance) - $total_deduction,
                    'description' => __('Transfer fee', 'thaiprompt-mlm'),
                    'status' => 'completed'
                ));
            }

            // Record receiver transaction
            $to_wallet = self::get_balance($to_user_id);
            self::add_transaction($to_user_id, array(
                'transaction_type' => 'transfer_received',
                'amount' => $amount,
                'balance_before' => floatval($to_wallet->balance) - $amount,
                'balance_after' => floatval($to_wallet->balance),
                'description' => sprintf(
                    __('Transfer from %s%s', 'thaiprompt-mlm'),
                    $from_user->display_name,
                    $note ? ' - ' . $note : ''
                ),
                'status' => 'completed',
                'related_user_id' => $from_user_id
            ));

            // Commit transaction
            $wpdb->query('COMMIT');

            // Log the transfer
            Thaiprompt_MLM_Logger::info('Wallet transfer completed', array(
                'from' => $from_user_id,
                'to' => $to_user_id,
                'amount' => $amount,
                'fee' => $fee
            ));

            // Trigger action hook for notifications
            do_action('thaiprompt_mlm_wallet_transfer', $from_user_id, $to_user_id, $amount, $fee);

            return array(
                'success' => true,
                'amount' => $amount,
                'fee' => $fee,
                'total' => $total_deduction,
                'recipient' => $to_user->display_name,
                'message' => __('Transfer completed successfully', 'thaiprompt-mlm')
            );

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');

            Thaiprompt_MLM_Logger::error('Wallet transfer failed', array(
                'error' => $e->getMessage(),
                'from' => $from_user_id,
                'to' => $to_user_id,
                'amount' => $amount
            ));

            return new WP_Error('transfer_failed', __('Transfer failed. Please try again.', 'thaiprompt-mlm'));
        }
    }

    /**
     * Calculate transfer fee
     *
     * @param float $amount Transfer amount
     * @return float Fee amount
     */
    public static function calculate_transfer_fee($amount) {
        $settings = get_option('thaiprompt_mlm_settings', array());

        // Get fee configuration
        $fee_type = isset($settings['wallet_transfer_fee_type']) ? $settings['wallet_transfer_fee_type'] : 'percentage';
        $fee_value = isset($settings['wallet_transfer_fee_value']) ? floatval($settings['wallet_transfer_fee_value']) : 0;
        $fee_min = isset($settings['wallet_transfer_fee_min']) ? floatval($settings['wallet_transfer_fee_min']) : 0;
        $fee_max = isset($settings['wallet_transfer_fee_max']) ? floatval($settings['wallet_transfer_fee_max']) : 0;

        $fee = 0;

        if ($fee_type === 'fixed') {
            // Fixed fee
            $fee = $fee_value;
        } elseif ($fee_type === 'percentage') {
            // Percentage fee
            $fee = ($amount * $fee_value) / 100;

            // Apply min/max limits
            if ($fee_min > 0 && $fee < $fee_min) {
                $fee = $fee_min;
            }
            if ($fee_max > 0 && $fee > $fee_max) {
                $fee = $fee_max;
            }
        }

        return round($fee, 2);
    }

    /**
     * Resolve user identifier (username, email, or referral code) to user ID
     *
     * @param string $identifier Username, email, or referral code
     * @return int|false User ID or false
     */
    private static function resolve_user_identifier($identifier) {
        // Try username
        $user = get_user_by('login', $identifier);
        if ($user) {
            return $user->ID;
        }

        // Try email
        $user = get_user_by('email', $identifier);
        if ($user) {
            return $user->ID;
        }

        // Try referral code
        if (class_exists('Thaiprompt_MLM_Referral')) {
            $user_id = Thaiprompt_MLM_Referral::get_user_id_by_code($identifier);
            if ($user_id) {
                return $user_id;
            }
        }

        return false;
    }

    /**
     * Check if user has LINE OA connected (KYC verification)
     *
     * @param int $user_id User ID
     * @return bool True if verified
     */
    public static function is_line_verified($user_id) {
        $line_user_id = get_user_meta($user_id, 'line_user_id', true);
        $line_following = get_user_meta($user_id, 'line_following', true);

        return !empty($line_user_id) && $line_following == 1;
    }
}
