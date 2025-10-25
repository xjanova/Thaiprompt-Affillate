<?php
/**
 * Plugin Name: Thaiprompt MLM
 * Plugin URI: https://thaiprompt.com
 * Description: ระบบจัดการ MLM ครบวงจร พร้อม Genealogy Tree แบบ GSAP, ระบบ Wallet, รองรับ WooCommerce และ Dokan
 * Version: 1.7.0
 * Author: Thaiprompt
 * Author URI: https://thaiprompt.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: thaiprompt-mlm
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin version
 */
define('THAIPROMPT_MLM_VERSION', '1.7.0');
define('THAIPROMPT_MLM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THAIPROMPT_MLM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('THAIPROMPT_MLM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Database version
 */
define('THAIPROMPT_MLM_DB_VERSION', '1.3.0');

/**
 * Activation hook
 */
function activate_thaiprompt_mlm() {
    require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-activator.php';
    Thaiprompt_MLM_Activator::activate();
}

/**
 * Check for database updates
 */
function thaiprompt_mlm_check_db_update() {
    // Only run in admin context to avoid performance issues
    if (!is_admin()) {
        return;
    }

    $current_db_version = get_option('thaiprompt_mlm_db_version', '0');

    if (version_compare($current_db_version, THAIPROMPT_MLM_DB_VERSION, '<')) {
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-activator.php';

        // Only update database tables, don't flush rewrite rules on every load
        Thaiprompt_MLM_Activator::upgrade_database();
    }
}
add_action('admin_init', 'thaiprompt_mlm_check_db_update');

/**
 * Deactivation hook
 */
function deactivate_thaiprompt_mlm() {
    require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-deactivator.php';
    Thaiprompt_MLM_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_thaiprompt_mlm');
register_deactivation_hook(__FILE__, 'deactivate_thaiprompt_mlm');

/**
 * Core plugin class
 */
require THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm.php';

/**
 * Begin execution
 */
function run_thaiprompt_mlm() {
    $plugin = new Thaiprompt_MLM();
    $plugin->run();
}
run_thaiprompt_mlm();
