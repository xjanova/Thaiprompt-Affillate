<?php
/**
 * Flex Message Templates
 *
 * Provides predefined Flex Message templates for LINE messaging
 *
 * @since 1.8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_Flex_Message_Templates {

    /**
     * Get all available templates
     *
     * @return array Templates with structure definitions
     */
    public static function get_templates() {
        return array(
            'product_card' => array(
                'name' => 'Product Card',
                'description' => 'Showcase a product with image, description, and price',
                'preview_image' => 'https://via.placeholder.com/300x200/667eea/ffffff?text=Product+Card',
                'fields' => array('title', 'description', 'price', 'image_url', 'button_text', 'button_url'),
                'generator' => array(__CLASS__, 'generate_product_card')
            ),
            'service_card' => array(
                'name' => 'Service Card',
                'description' => 'Promote a service with features and call-to-action',
                'preview_image' => 'https://via.placeholder.com/300x200/10b981/ffffff?text=Service+Card',
                'fields' => array('title', 'subtitle', 'feature1', 'feature2', 'feature3', 'button_text', 'button_url'),
                'generator' => array(__CLASS__, 'generate_service_card')
            ),
            'announcement' => array(
                'name' => 'Announcement',
                'description' => 'Important announcement with icon and message',
                'preview_image' => 'https://via.placeholder.com/300x200/f59e0b/ffffff?text=Announcement',
                'fields' => array('title', 'message', 'icon_url', 'button_text', 'button_url'),
                'generator' => array(__CLASS__, 'generate_announcement')
            ),
            'referral_card' => array(
                'name' => 'Referral Card',
                'description' => 'Share referral link with benefits',
                'preview_image' => 'https://via.placeholder.com/300x200/8b5cf6/ffffff?text=Referral+Card',
                'fields' => array('member_name', 'referral_code', 'referral_link', 'benefit_text'),
                'generator' => array(__CLASS__, 'generate_referral_card')
            ),
            'profile_card' => array(
                'name' => 'Profile Card',
                'description' => 'Member profile with stats and rank',
                'preview_image' => 'https://via.placeholder.com/300x200/ef4444/ffffff?text=Profile+Card',
                'fields' => array('name', 'rank', 'total_referrals', 'total_earnings', 'avatar_url'),
                'generator' => array(__CLASS__, 'generate_profile_card')
            )
        );
    }

    /**
     * Generate Product Card
     */
    public static function generate_product_card($data) {
        return array(
            'type' => 'bubble',
            'hero' => array(
                'type' => 'image',
                'url' => $data['image_url'] ?? 'https://via.placeholder.com/800x400',
                'size' => 'full',
                'aspectRatio' => '20:13',
                'aspectMode' => 'cover'
            ),
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array(
                    array(
                        'type' => 'text',
                        'text' => $data['title'] ?? 'Product Name',
                        'weight' => 'bold',
                        'size' => 'xl',
                        'wrap' => true
                    ),
                    array(
                        'type' => 'box',
                        'layout' => 'baseline',
                        'margin' => 'md',
                        'contents' => array(
                            array(
                                'type' => 'text',
                                'text' => $data['price'] ?? '฿0',
                                'size' => 'xxl',
                                'color' => '#667eea',
                                'weight' => 'bold',
                                'flex' => 0
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'text' => $data['description'] ?? 'Product description',
                        'size' => 'sm',
                        'color' => '#666666',
                        'margin' => 'md',
                        'wrap' => true
                    )
                )
            ),
            'footer' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array(
                    array(
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'action' => array(
                            'type' => 'uri',
                            'label' => $data['button_text'] ?? 'View Details',
                            'uri' => $data['button_url'] ?? 'https://example.com'
                        ),
                        'color' => '#667eea'
                    )
                )
            )
        );
    }

    /**
     * Generate Service Card
     */
    public static function generate_service_card($data) {
        $features = array();
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data['feature' . $i])) {
                $features[] = array(
                    'type' => 'box',
                    'layout' => 'baseline',
                    'spacing' => 'sm',
                    'contents' => array(
                        array(
                            'type' => 'text',
                            'text' => '✓',
                            'color' => '#10b981',
                            'size' => 'lg',
                            'flex' => 0
                        ),
                        array(
                            'type' => 'text',
                            'text' => $data['feature' . $i],
                            'wrap' => true,
                            'color' => '#666666',
                            'size' => 'sm',
                            'flex' => 1
                        )
                    )
                );
            }
        }

        return array(
            'type' => 'bubble',
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array_merge(
                    array(
                        array(
                            'type' => 'text',
                            'text' => $data['title'] ?? 'Service Name',
                            'weight' => 'bold',
                            'size' => 'xl',
                            'color' => '#10b981'
                        ),
                        array(
                            'type' => 'text',
                            'text' => $data['subtitle'] ?? 'Service description',
                            'size' => 'sm',
                            'color' => '#666666',
                            'margin' => 'md',
                            'wrap' => true
                        ),
                        array(
                            'type' => 'separator',
                            'margin' => 'xl'
                        ),
                        array(
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'lg',
                            'spacing' => 'sm',
                            'contents' => $features
                        )
                    )
                )
            ),
            'footer' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array(
                    array(
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'action' => array(
                            'type' => 'uri',
                            'label' => $data['button_text'] ?? 'Learn More',
                            'uri' => $data['button_url'] ?? 'https://example.com'
                        ),
                        'color' => '#10b981'
                    )
                )
            )
        );
    }

    /**
     * Generate Announcement
     */
    public static function generate_announcement($data) {
        return array(
            'type' => 'bubble',
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array(
                    array(
                        'type' => 'image',
                        'url' => $data['icon_url'] ?? 'https://via.placeholder.com/100x100/f59e0b/ffffff?text=!',
                        'size' => 'xs',
                        'aspectRatio' => '1:1',
                        'aspectMode' => 'cover',
                        'margin' => 'none',
                        'align' => 'center'
                    ),
                    array(
                        'type' => 'text',
                        'text' => $data['title'] ?? 'Important Announcement',
                        'weight' => 'bold',
                        'size' => 'xl',
                        'margin' => 'md',
                        'align' => 'center',
                        'color' => '#f59e0b'
                    ),
                    array(
                        'type' => 'text',
                        'text' => $data['message'] ?? 'Announcement message here',
                        'size' => 'sm',
                        'color' => '#666666',
                        'margin' => 'md',
                        'wrap' => true,
                        'align' => 'center'
                    )
                )
            ),
            'footer' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array(
                    array(
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'action' => array(
                            'type' => 'uri',
                            'label' => $data['button_text'] ?? 'Read More',
                            'uri' => $data['button_url'] ?? 'https://example.com'
                        ),
                        'color' => '#f59e0b'
                    )
                )
            )
        );
    }

    /**
     * Generate Referral Card
     */
    public static function generate_referral_card($data) {
        return array(
            'type' => 'bubble',
            'hero' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array(
                    array(
                        'type' => 'text',
                        'text' => '🎁',
                        'size' => '4xl',
                        'align' => 'center'
                    )
                ),
                'backgroundColor' => '#8b5cf6',
                'paddingAll' => 'xl'
            ),
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array(
                    array(
                        'type' => 'text',
                        'text' => 'Invite Friends & Earn!',
                        'weight' => 'bold',
                        'size' => 'xl',
                        'color' => '#8b5cf6',
                        'align' => 'center'
                    ),
                    array(
                        'type' => 'text',
                        'text' => $data['member_name'] ?? 'Member',
                        'size' => 'sm',
                        'color' => '#666666',
                        'margin' => 'md',
                        'align' => 'center'
                    ),
                    array(
                        'type' => 'separator',
                        'margin' => 'lg'
                    ),
                    array(
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'lg',
                        'spacing' => 'sm',
                        'contents' => array(
                            array(
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'sm',
                                'contents' => array(
                                    array(
                                        'type' => 'text',
                                        'text' => 'Code:',
                                        'color' => '#aaaaaa',
                                        'size' => 'sm',
                                        'flex' => 1
                                    ),
                                    array(
                                        'type' => 'text',
                                        'text' => $data['referral_code'] ?? 'XXXX',
                                        'wrap' => true,
                                        'color' => '#8b5cf6',
                                        'size' => 'lg',
                                        'weight' => 'bold',
                                        'flex' => 3
                                    )
                                )
                            ),
                            array(
                                'type' => 'text',
                                'text' => $data['benefit_text'] ?? 'Join now and get exclusive benefits!',
                                'wrap' => true,
                                'color' => '#666666',
                                'size' => 'xs',
                                'margin' => 'md'
                            )
                        )
                    )
                )
            ),
            'footer' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array(
                    array(
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'action' => array(
                            'type' => 'uri',
                            'label' => 'Join Now',
                            'uri' => $data['referral_link'] ?? 'https://example.com'
                        ),
                        'color' => '#8b5cf6'
                    )
                )
            )
        );
    }

    /**
     * Generate Profile Card
     */
    public static function generate_profile_card($data) {
        return array(
            'type' => 'bubble',
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => array(
                    array(
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => array(
                            array(
                                'type' => 'image',
                                'url' => $data['avatar_url'] ?? 'https://via.placeholder.com/100x100',
                                'size' => 'lg',
                                'aspectRatio' => '1:1',
                                'aspectMode' => 'cover',
                                'flex' => 0
                            ),
                            array(
                                'type' => 'box',
                                'layout' => 'vertical',
                                'flex' => 1,
                                'margin' => 'md',
                                'contents' => array(
                                    array(
                                        'type' => 'text',
                                        'text' => $data['name'] ?? 'Member Name',
                                        'weight' => 'bold',
                                        'size' => 'lg'
                                    ),
                                    array(
                                        'type' => 'text',
                                        'text' => $data['rank'] ?? 'Member',
                                        'size' => 'sm',
                                        'color' => '#ef4444',
                                        'margin' => 'xs'
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'type' => 'separator',
                        'margin' => 'lg'
                    ),
                    array(
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'lg',
                        'spacing' => 'sm',
                        'contents' => array(
                            array(
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'sm',
                                'contents' => array(
                                    array(
                                        'type' => 'text',
                                        'text' => 'Total Referrals',
                                        'color' => '#aaaaaa',
                                        'size' => 'sm',
                                        'flex' => 2
                                    ),
                                    array(
                                        'type' => 'text',
                                        'text' => $data['total_referrals'] ?? '0',
                                        'wrap' => true,
                                        'color' => '#666666',
                                        'size' => 'md',
                                        'weight' => 'bold',
                                        'flex' => 1,
                                        'align' => 'end'
                                    )
                                )
                            ),
                            array(
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'sm',
                                'contents' => array(
                                    array(
                                        'type' => 'text',
                                        'text' => 'Total Earnings',
                                        'color' => '#aaaaaa',
                                        'size' => 'sm',
                                        'flex' => 2
                                    ),
                                    array(
                                        'type' => 'text',
                                        'text' => $data['total_earnings'] ?? '฿0',
                                        'wrap' => true,
                                        'color' => '#10b981',
                                        'size' => 'md',
                                        'weight' => 'bold',
                                        'flex' => 1,
                                        'align' => 'end'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Get template by ID
     */
    public static function get_template($template_id) {
        $templates = self::get_templates();
        return isset($templates[$template_id]) ? $templates[$template_id] : false;
    }

    /**
     * Generate Flex Message from template
     */
    public static function generate($template_id, $data) {
        $template = self::get_template($template_id);

        if (!$template) {
            return new WP_Error('invalid_template', __('Invalid template ID', 'thaiprompt-mlm'));
        }

        if (!isset($template['generator']) || !is_callable($template['generator'])) {
            return new WP_Error('no_generator', __('Template generator not found', 'thaiprompt-mlm'));
        }

        return call_user_func($template['generator'], $data);
    }
}
