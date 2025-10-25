<?php
/**
 * Withdrawal AJAX Handlers
 *
 * Handles admin AJAX actions for withdrawal management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_Withdrawal_AJAX {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        add_action('wp_ajax_mlm_approve_withdrawal', array(__CLASS__, 'approve_withdrawal'));
        add_action('wp_ajax_mlm_reject_withdrawal', array(__CLASS__, 'reject_withdrawal'));
    }

    /**
     * AJAX handler for approving withdrawal
     */
    public static function approve_withdrawal() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mlm_withdrawal_action')) {
            wp_send_json_error('Invalid security token');
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        // Get data
        $withdrawal_id = isset($_POST['withdrawal_id']) ? intval($_POST['withdrawal_id']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        if (!$withdrawal_id) {
            wp_send_json_error('Invalid withdrawal ID');
            return;
        }

        // Handle slip upload
        $slip_attachment_id = 0;

        if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $uploaded = media_handle_upload('slip', 0, array(
                'test_form' => false,
                'post_title' => 'Withdrawal Slip #' . $withdrawal_id
            ));

            if (is_wp_error($uploaded)) {
                wp_send_json_error('Failed to upload slip: ' . $uploaded->get_error_message());
                return;
            }

            $slip_attachment_id = $uploaded;

            Thaiprompt_MLM_Logger::info('Withdrawal slip uploaded', array(
                'withdrawal_id' => $withdrawal_id,
                'attachment_id' => $slip_attachment_id
            ));
        } else {
            wp_send_json_error('Transfer slip is required');
            return;
        }

        // Approve withdrawal
        $result = Thaiprompt_MLM_Wallet::approve_withdrawal($withdrawal_id, $slip_attachment_id, $notes);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }

        wp_send_json_success(array(
            'message' => __('Withdrawal approved and notification sent via LINE successfully!', 'thaiprompt-mlm')
        ));
    }

    /**
     * AJAX handler for rejecting withdrawal
     */
    public static function reject_withdrawal() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mlm_withdrawal_action')) {
            wp_send_json_error('Invalid security token');
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        // Get data
        $withdrawal_id = isset($_POST['withdrawal_id']) ? intval($_POST['withdrawal_id']) : 0;
        $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';

        if (!$withdrawal_id) {
            wp_send_json_error('Invalid withdrawal ID');
            return;
        }

        if (empty($reason)) {
            wp_send_json_error('Rejection reason is required');
            return;
        }

        // Reject withdrawal
        $result = Thaiprompt_MLM_Wallet::reject_withdrawal($withdrawal_id, $reason);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }

        wp_send_json_success(array(
            'message' => __('Withdrawal rejected successfully', 'thaiprompt-mlm')
        ));
    }
}

// Initialize
Thaiprompt_MLM_Withdrawal_AJAX::init();
