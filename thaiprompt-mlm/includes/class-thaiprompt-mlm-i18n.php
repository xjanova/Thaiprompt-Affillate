<?php
/**
 * Define the internationalization functionality
 */
class Thaiprompt_MLM_i18n {

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'thaiprompt-mlm',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
