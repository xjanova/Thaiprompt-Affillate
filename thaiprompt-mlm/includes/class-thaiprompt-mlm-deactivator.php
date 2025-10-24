<?php
/**
 * Fired during plugin deactivation
 */
class Thaiprompt_MLM_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled events
        wp_clear_scheduled_hook('thaiprompt_mlm_daily_tasks');
        wp_clear_scheduled_hook('thaiprompt_mlm_calculate_ranks');
    }
}
