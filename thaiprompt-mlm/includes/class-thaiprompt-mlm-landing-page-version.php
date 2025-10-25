<?php
/**
 * Landing Page Version Control Class
 *
 * Handles version tracking for landing page edits
 */

class Thaiprompt_MLM_Landing_Page_Version {

    /**
     * Create landing page versions table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            landing_page_id bigint(20) NOT NULL,
            version int NOT NULL DEFAULT 1,
            title varchar(255) NOT NULL,
            headline text NOT NULL,
            description longtext NOT NULL,
            image1_url varchar(500) DEFAULT NULL,
            image2_url varchar(500) DEFAULT NULL,
            image3_url varchar(500) DEFAULT NULL,
            cta_text varchar(100) DEFAULT NULL,
            edited_by bigint(20) NOT NULL,
            edited_at datetime DEFAULT CURRENT_TIMESTAMP,
            change_note text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY landing_page_id (landing_page_id),
            KEY version (version),
            KEY edited_at (edited_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create a new version when landing page is edited
     */
    public static function create_version($landing_page_id, $old_data, $new_data, $user_id) {
        global $wpdb;
        $versions_table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';

        // Create table if not exists
        self::create_table();

        // Get current version number
        $current_version = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(version) FROM $versions_table WHERE landing_page_id = %d",
            $landing_page_id
        ));

        $next_version = intval($current_version) + 1;

        // Detect changes
        $changes = array();
        $fields_to_check = array('title', 'headline', 'description', 'image1_url', 'image2_url', 'image3_url', 'cta_text');

        foreach ($fields_to_check as $field) {
            if (isset($old_data->$field) && isset($new_data[$field])) {
                if ($old_data->$field != $new_data[$field]) {
                    $changes[] = $field;
                }
            }
        }

        $change_note = !empty($changes)
            ? sprintf(__('Modified: %s', 'thaiprompt-mlm'), implode(', ', $changes))
            : __('Content updated', 'thaiprompt-mlm');

        // Store the OLD version in history
        $version_data = array(
            'landing_page_id' => $landing_page_id,
            'version' => $current_version ?: 1,
            'title' => $old_data->title,
            'headline' => $old_data->headline,
            'description' => $old_data->description,
            'image1_url' => $old_data->image1_url,
            'image2_url' => $old_data->image2_url,
            'image3_url' => $old_data->image3_url,
            'cta_text' => $old_data->cta_text,
            'edited_by' => $user_id,
            'change_note' => $change_note
        );

        $result = $wpdb->insert($versions_table, $version_data);

        if ($result) {
            Thaiprompt_MLM_Logger::info('Landing page version created', array(
                'landing_page_id' => $landing_page_id,
                'version' => $current_version ?: 1,
                'changes' => $changes
            ));

            return $next_version;
        }

        return false;
    }

    /**
     * Get all versions for a landing page
     */
    public static function get_versions($landing_page_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT v.*, u.display_name as editor_name
            FROM $table v
            LEFT JOIN {$wpdb->users} u ON v.edited_by = u.ID
            WHERE v.landing_page_id = %d
            ORDER BY v.version DESC",
            $landing_page_id
        ));
    }

    /**
     * Get a specific version
     */
    public static function get_version($version_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT v.*, u.display_name as editor_name
            FROM $table v
            LEFT JOIN {$wpdb->users} u ON v.edited_by = u.ID
            WHERE v.id = %d",
            $version_id
        ));
    }

    /**
     * Restore a previous version
     */
    public static function restore_version($landing_page_id, $version_id, $user_id) {
        global $wpdb;
        $versions_table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';
        $landing_table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

        // Get the version to restore
        $version = self::get_version($version_id);
        if (!$version) {
            return new WP_Error('not_found', __('Version not found', 'thaiprompt-mlm'));
        }

        // Get current landing page data
        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $landing_table WHERE id = %d",
            $landing_page_id
        ));

        if (!$current) {
            return new WP_Error('not_found', __('Landing page not found', 'thaiprompt-mlm'));
        }

        // Create a version of the current state before restoring
        $restore_data = array(
            'title' => $version->title,
            'headline' => $version->headline,
            'description' => $version->description,
            'image1_url' => $version->image1_url,
            'image2_url' => $version->image2_url,
            'image3_url' => $version->image3_url,
            'cta_text' => $version->cta_text
        );

        self::create_version($landing_page_id, $current, $restore_data, $user_id);

        // Restore the version
        $restore_data['status'] = 'pending'; // Always pending after restore for re-approval

        $result = $wpdb->update(
            $landing_table,
            $restore_data,
            array('id' => $landing_page_id)
        );

        if ($result !== false) {
            Thaiprompt_MLM_Logger::info('Landing page version restored', array(
                'landing_page_id' => $landing_page_id,
                'restored_version' => $version->version,
                'restored_by' => $user_id
            ));

            return true;
        }

        return new WP_Error('db_error', __('Failed to restore version', 'thaiprompt-mlm'));
    }

    /**
     * Get version count for a landing page
     */
    public static function get_version_count($landing_page_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE landing_page_id = %d",
            $landing_page_id
        )));
    }

    /**
     * Delete old versions (keep last N versions)
     */
    public static function cleanup_old_versions($landing_page_id, $keep_count = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';

        // Get IDs to keep
        $keep_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $table
            WHERE landing_page_id = %d
            ORDER BY version DESC
            LIMIT %d",
            $landing_page_id,
            $keep_count
        ));

        if (empty($keep_ids)) {
            return 0;
        }

        // Delete old versions
        $placeholders = implode(',', array_fill(0, count($keep_ids), '%d'));
        $query = $wpdb->prepare(
            "DELETE FROM $table
            WHERE landing_page_id = %d
            AND id NOT IN ($placeholders)",
            array_merge(array($landing_page_id), $keep_ids)
        );

        return $wpdb->query($query);
    }
}
