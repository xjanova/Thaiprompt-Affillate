<?php
/**
 * MLM Scheduled Transfer Class
 *
 * Handles scheduled and recurring wallet transfers
 */

class Thaiprompt_MLM_Scheduled_Transfer {

    /**
     * Create scheduled transfers table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            from_user_id bigint(20) DEFAULT NULL,
            to_user_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            note text,
            schedule_datetime datetime NOT NULL,
            repeat_type varchar(20) DEFAULT 'once',
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) NOT NULL,
            executed_at datetime DEFAULT NULL,
            last_executed_at datetime DEFAULT NULL,
            next_execution_at datetime DEFAULT NULL,
            execution_count int DEFAULT 0,
            PRIMARY KEY  (id),
            KEY from_user_id (from_user_id),
            KEY to_user_id (to_user_id),
            KEY status (status),
            KEY schedule_datetime (schedule_datetime)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Schedule a new transfer
     */
    public static function schedule($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';

        // Create table if not exists
        self::create_table();

        // Validate required fields
        if (empty($data['to_user_id']) || empty($data['amount']) || empty($data['schedule_datetime'])) {
            return new WP_Error('missing_fields', __('Missing required fields', 'thaiprompt-mlm'));
        }

        // Prepare data
        $insert_data = array(
            'from_user_id' => !empty($data['from_user_id']) ? intval($data['from_user_id']) : NULL,
            'to_user_id' => intval($data['to_user_id']),
            'amount' => floatval($data['amount']),
            'note' => isset($data['note']) ? sanitize_textarea_field($data['note']) : '',
            'schedule_datetime' => sanitize_text_field($data['schedule_datetime']),
            'repeat_type' => isset($data['repeat_type']) ? sanitize_text_field($data['repeat_type']) : 'once',
            'status' => 'pending',
            'created_by' => get_current_user_id(),
            'next_execution_at' => sanitize_text_field($data['schedule_datetime'])
        );

        $result = $wpdb->insert($table, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to schedule transfer', 'thaiprompt-mlm'));
        }

        Thaiprompt_MLM_Logger::info('Transfer scheduled', array(
            'schedule_id' => $wpdb->insert_id,
            'from' => $insert_data['from_user_id'] ?: 'system',
            'to' => $insert_data['to_user_id'],
            'amount' => $insert_data['amount']
        ));

        return $wpdb->insert_id;
    }

    /**
     * Get all scheduled transfers
     */
    public static function get_all($status = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';

        $query = "SELECT * FROM $table WHERE 1=1";
        $params = array();

        if ($status) {
            $query .= " AND status = %s";
            $params[] = $status;
        }

        $query .= " ORDER BY schedule_datetime ASC";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get pending transfers that need to be executed
     */
    public static function get_due_transfers() {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';

        $current_time = current_time('mysql');

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
            WHERE status = 'pending'
            AND next_execution_at <= %s
            ORDER BY next_execution_at ASC",
            $current_time
        ));
    }

    /**
     * Execute a scheduled transfer
     */
    public static function execute($schedule_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';

        $schedule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $schedule_id
        ));

        if (!$schedule) {
            return new WP_Error('not_found', __('Scheduled transfer not found', 'thaiprompt-mlm'));
        }

        if ($schedule->status !== 'pending') {
            return new WP_Error('invalid_status', __('Transfer is not pending', 'thaiprompt-mlm'));
        }

        // Execute the transfer
        if ($schedule->from_user_id) {
            // Transfer between users
            $result = Thaiprompt_MLM_Wallet::transfer_funds(
                $schedule->from_user_id,
                $schedule->to_user_id,
                $schedule->amount,
                $schedule->note . ' [Scheduled Transfer #' . $schedule_id . ']'
            );
        } else {
            // System credit
            $result = Thaiprompt_MLM_Wallet::add_funds(
                $schedule->to_user_id,
                $schedule->amount,
                $schedule->note . ' [Scheduled Transfer #' . $schedule_id . ']'
            );
        }

        if (is_wp_error($result)) {
            Thaiprompt_MLM_Logger::error('Scheduled transfer execution failed', array(
                'schedule_id' => $schedule_id,
                'error' => $result->get_error_message()
            ));
            return $result;
        }

        // Update schedule status
        $execution_count = intval($schedule->execution_count) + 1;
        $update_data = array(
            'executed_at' => current_time('mysql'),
            'last_executed_at' => current_time('mysql'),
            'execution_count' => $execution_count
        );

        // Handle recurring transfers
        if ($schedule->repeat_type !== 'once') {
            $next_execution = self::calculate_next_execution($schedule->schedule_datetime, $schedule->repeat_type);
            $update_data['next_execution_at'] = $next_execution;
            $update_data['schedule_datetime'] = $next_execution;
        } else {
            $update_data['status'] = 'completed';
            $update_data['next_execution_at'] = NULL;
        }

        $wpdb->update($table, $update_data, array('id' => $schedule_id));

        Thaiprompt_MLM_Logger::info('Scheduled transfer executed', array(
            'schedule_id' => $schedule_id,
            'execution_count' => $execution_count,
            'repeat_type' => $schedule->repeat_type
        ));

        return true;
    }

    /**
     * Calculate next execution time for recurring transfers
     */
    private static function calculate_next_execution($current_datetime, $repeat_type) {
        $timestamp = strtotime($current_datetime);

        switch ($repeat_type) {
            case 'daily':
                $next = strtotime('+1 day', $timestamp);
                break;
            case 'weekly':
                $next = strtotime('+1 week', $timestamp);
                break;
            case 'monthly':
                $next = strtotime('+1 month', $timestamp);
                break;
            default:
                $next = $timestamp;
        }

        return date('Y-m-d H:i:s', $next);
    }

    /**
     * Cancel a scheduled transfer
     */
    public static function cancel($schedule_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';

        $result = $wpdb->update(
            $table,
            array('status' => 'cancelled'),
            array('id' => $schedule_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to cancel transfer', 'thaiprompt-mlm'));
        }

        Thaiprompt_MLM_Logger::info('Scheduled transfer cancelled', array('schedule_id' => $schedule_id));

        return true;
    }

    /**
     * Process all due transfers (called by cron)
     */
    public static function process_due_transfers() {
        $due_transfers = self::get_due_transfers();

        if (empty($due_transfers)) {
            return array('processed' => 0, 'failed' => 0);
        }

        $processed = 0;
        $failed = 0;

        foreach ($due_transfers as $transfer) {
            $result = self::execute($transfer->id);

            if (is_wp_error($result)) {
                $failed++;
            } else {
                $processed++;
            }
        }

        Thaiprompt_MLM_Logger::info('Processed scheduled transfers', array(
            'processed' => $processed,
            'failed' => $failed
        ));

        return array('processed' => $processed, 'failed' => $failed);
    }
}
