<?php
/**
 * Rich Menu Templates
 *
 * Provides predefined Rich Menu templates with button layouts and area coordinates
 *
 * @since 1.8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_Rich_Menu_Templates {

    /**
     * Get all available templates
     *
     * @return array Templates with size and area definitions
     */
    public static function get_templates() {
        return array(
            'template_2_horizontal' => array(
                'name' => '2 Buttons (Horizontal)',
                'description' => 'Two buttons side by side - perfect for simple menus',
                'size' => array(
                    'width' => 2500,
                    'height' => 843
                ),
                'areas' => array(
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 0,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 1',
                            'text' => 'Button 1'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1250,
                            'y' => 0,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 2',
                            'text' => 'Button 2'
                        )
                    )
                )
            ),

            'template_3_horizontal' => array(
                'name' => '3 Buttons (Horizontal)',
                'description' => 'Three buttons in a row',
                'size' => array(
                    'width' => 2500,
                    'height' => 843
                ),
                'areas' => array(
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 0,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 1',
                            'text' => 'Button 1'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 833,
                            'y' => 0,
                            'width' => 834,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 2',
                            'text' => 'Button 2'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1667,
                            'y' => 0,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 3',
                            'text' => 'Button 3'
                        )
                    )
                )
            ),

            'template_4_grid' => array(
                'name' => '4 Buttons (2x2 Grid)',
                'description' => 'Four buttons in a 2x2 grid layout',
                'size' => array(
                    'width' => 2500,
                    'height' => 1686
                ),
                'areas' => array(
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 0,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 1',
                            'text' => 'Button 1'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1250,
                            'y' => 0,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 2',
                            'text' => 'Button 2'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 843,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 3',
                            'text' => 'Button 3'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1250,
                            'y' => 843,
                            'width' => 1250,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 4',
                            'text' => 'Button 4'
                        )
                    )
                )
            ),

            'template_6_grid' => array(
                'name' => '6 Buttons (3x2 Grid)',
                'description' => 'Six buttons in a 3x2 grid - most popular layout',
                'size' => array(
                    'width' => 2500,
                    'height' => 1686
                ),
                'areas' => array(
                    // Top row
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 0,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 1',
                            'text' => 'Button 1'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 833,
                            'y' => 0,
                            'width' => 834,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 2',
                            'text' => 'Button 2'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1667,
                            'y' => 0,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 3',
                            'text' => 'Button 3'
                        )
                    ),
                    // Bottom row
                    array(
                        'bounds' => array(
                            'x' => 0,
                            'y' => 843,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 4',
                            'text' => 'Button 4'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 833,
                            'y' => 843,
                            'width' => 834,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 5',
                            'text' => 'Button 5'
                        )
                    ),
                    array(
                        'bounds' => array(
                            'x' => 1667,
                            'y' => 843,
                            'width' => 833,
                            'height' => 843
                        ),
                        'action' => array(
                            'type' => 'message',
                            'label' => 'Button 6',
                            'text' => 'Button 6'
                        )
                    )
                )
            )
        );
    }

    /**
     * Get a specific template
     *
     * @param string $template_id Template ID
     * @return array|false Template data or false if not found
     */
    public static function get_template($template_id) {
        $templates = self::get_templates();
        return isset($templates[$template_id]) ? $templates[$template_id] : false;
    }

    /**
     * Build Rich Menu data for LINE API
     *
     * @param string $template_id Template ID
     * @param string $name Rich Menu name
     * @param string $chat_bar_text Chat bar text (max 14 chars)
     * @param array $button_actions Array of button actions (overrides defaults)
     * @return array|WP_Error Rich Menu data or error
     */
    public static function build_rich_menu_data($template_id, $name, $chat_bar_text, $button_actions = array()) {
        $template = self::get_template($template_id);

        if (!$template) {
            return new WP_Error('invalid_template', __('Invalid template ID', 'thaiprompt-mlm'));
        }

        // Validate chat bar text length
        if (mb_strlen($chat_bar_text) > 14) {
            return new WP_Error('invalid_chat_bar_text', __('Chat bar text must be 14 characters or less', 'thaiprompt-mlm'));
        }

        // Build areas with custom actions if provided
        $areas = array();
        foreach ($template['areas'] as $index => $area) {
            $action = isset($button_actions[$index]) ? $button_actions[$index] : $area['action'];

            $areas[] = array(
                'bounds' => $area['bounds'],
                'action' => $action
            );
        }

        // Build Rich Menu data structure
        $rich_menu_data = array(
            'size' => $template['size'],
            'selected' => true,
            'name' => $name,
            'chatBarText' => $chat_bar_text,
            'areas' => $areas
        );

        return $rich_menu_data;
    }

    /**
     * Validate button action
     *
     * @param array $action Action data
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public static function validate_action($action) {
        if (!isset($action['type'])) {
            return new WP_Error('missing_type', __('Action type is required', 'thaiprompt-mlm'));
        }

        $valid_types = array('message', 'uri', 'postback');
        if (!in_array($action['type'], $valid_types)) {
            return new WP_Error('invalid_type', __('Invalid action type', 'thaiprompt-mlm'));
        }

        switch ($action['type']) {
            case 'message':
                if (!isset($action['text']) || empty($action['text'])) {
                    return new WP_Error('missing_text', __('Message text is required', 'thaiprompt-mlm'));
                }
                if (mb_strlen($action['text']) > 300) {
                    return new WP_Error('text_too_long', __('Message text must be 300 characters or less', 'thaiprompt-mlm'));
                }
                break;

            case 'uri':
                if (!isset($action['uri']) || empty($action['uri'])) {
                    return new WP_Error('missing_uri', __('URI is required', 'thaiprompt-mlm'));
                }
                if (!filter_var($action['uri'], FILTER_VALIDATE_URL)) {
                    return new WP_Error('invalid_uri', __('Invalid URI format', 'thaiprompt-mlm'));
                }
                break;

            case 'postback':
                if (!isset($action['data']) || empty($action['data'])) {
                    return new WP_Error('missing_data', __('Postback data is required', 'thaiprompt-mlm'));
                }
                if (mb_strlen($action['data']) > 300) {
                    return new WP_Error('data_too_long', __('Postback data must be 300 characters or less', 'thaiprompt-mlm'));
                }
                break;
        }

        return true;
    }

    /**
     * Get action types for dropdowns
     *
     * @return array Action type options
     */
    public static function get_action_types() {
        return array(
            'message' => __('Send Message', 'thaiprompt-mlm'),
            'uri' => __('Open URL', 'thaiprompt-mlm'),
            'postback' => __('Postback Data', 'thaiprompt-mlm')
        );
    }
}
