<?php
/**
 * Core plugin class
 */
class Thaiprompt_MLM {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Initialize the class and set its properties
     */
    public function __construct() {
        $this->version = THAIPROMPT_MLM_VERSION;
        $this->plugin_name = 'thaiprompt-mlm';

        $this->load_dependencies();
        $this->init_logger();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Initialize the logger
     */
    private function init_logger() {
        Thaiprompt_MLM_Logger::init();
        Thaiprompt_MLM_Logger::info('Plugin initialized successfully');
    }

    /**
     * Load the required dependencies
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-i18n.php';

        /**
         * The logger class for error tracking and debugging
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-logger.php';

        /**
         * LINE Bot Integration
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-line-bot.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-line-webhook.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-rich-menu-templates.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-flex-message-templates.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-ai-handler.php';

        /**
         * Core functionality classes
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-database.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-network.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-placement.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-commission.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-wallet.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-rank.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-referral.php';
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'includes/class-thaiprompt-mlm-integrations.php';

        /**
         * The class responsible for defining all actions in the admin area
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/class-thaiprompt-mlm-admin.php';

        /**
         * The class responsible for defining all actions on the public side
         */
        require_once THAIPROMPT_MLM_PLUGIN_DIR . 'public/class-thaiprompt-mlm-public.php';

        $this->loader = new Thaiprompt_MLM_Loader();
    }

    /**
     * Define the locale for internationalization
     */
    private function set_locale() {
        $plugin_i18n = new Thaiprompt_MLM_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new Thaiprompt_MLM_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new Thaiprompt_MLM_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Portal template registration
        $this->loader->add_filter('theme_page_templates', $plugin_public, 'register_portal_template');
        $this->loader->add_filter('template_include', $plugin_public, 'load_portal_template', 99);
    }

    /**
     * Run the loader to execute all hooks
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin
     */
    public function get_version() {
        return $this->version;
    }
}
