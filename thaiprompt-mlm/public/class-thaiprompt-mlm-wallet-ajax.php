<?php
/**
 * Public Wallet AJAX Handlers
 *
 * Handles member AJAX actions for wallet operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_Public_Wallet_AJAX {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        add_action('wp_ajax_mlm_transfer_funds', array(__CLASS__, 'transfer_funds'));
        add_action('wp_ajax_mlm_request_withdrawal', array(__CLASS__, 'request_withdrawal'));
    }

    /**
     * AJAX handler for transferring funds between members
     */
    public static function transfer_funds() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mlm_wallet_action')) {
            wp_send_json_error('Invalid security token');
            return;
        }

        // Check user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to transfer funds');
            return;
        }

        $user_id = get_current_user_id();

        // Get data
        $recipient = isset($_POST['recipient']) ? sanitize_text_field($_POST['recipient']) : '';
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';

        if (empty($recipient)) {
            wp_send_json_error('Recipient is required');
            return;
        }

        if ($amount <= 0) {
            wp_send_json_error('Invalid transfer amount');
            return;
        }

        // Process transfer
        $result = Thaiprompt_MLM_Wallet::transfer_funds($user_id, $recipient, $amount, $note);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }

        // Build success message
        $message = sprintf(
            __('Transfer completed! %s sent to %s. Fee: %s', 'thaiprompt-mlm'),
            wc_price($result['amount']),
            $result['recipient'],
            wc_price($result['fee'])
        );

        wp_send_json_success(array(
            'message' => $message,
            'data' => $result
        ));
    }

    /**
     * AJAX handler for requesting withdrawal
     */
    public static function request_withdrawal() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mlm_wallet_action')) {
            wp_send_json_error('Invalid security token');
            return;
        }

        // Check user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to request withdrawal');
            return;
        }

        $user_id = get_current_user_id();

        // Get data
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

        if ($amount <= 0) {
            wp_send_json_error('Invalid withdrawal amount');
            return;
        }

        // Get bank details from user meta (simplified for now)
        $details = array(
            'bank_name' => get_user_meta($user_id, 'bank_name', true) ?: 'Not provided',
            'bank_account' => get_user_meta($user_id, 'bank_account', true) ?: 'Not provided'
        );

        // Process withdrawal request
        $result = Thaiprompt_MLM_Wallet::process_withdrawal($user_id, $amount, 'bank_transfer', $details);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }

        wp_send_json_success(array(
            'message' => $result['message']
        ));
    }
}

// Initialize
Thaiprompt_MLM_Public_Wallet_AJAX::init();
