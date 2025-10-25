<?php
/**
 * Admin Settings View
 */

if (!defined('ABSPATH')) {
    exit;
}

// Save settings
if (isset($_POST['thaiprompt_mlm_settings_submit'])) {
    check_admin_referer('thaiprompt_mlm_settings');

    // Handle logo upload
    $portal_logo = get_option('thaiprompt_mlm_portal_logo', '');
    if (!empty($_FILES['portal_logo']['name'])) {
        $upload = wp_handle_upload($_FILES['portal_logo'], array('test_form' => false));
        if (isset($upload['url'])) {
            $portal_logo = $upload['url'];
            update_option('thaiprompt_mlm_portal_logo', $portal_logo);
        }
    }

    // Handle slideshow images upload
    $slideshow_images = get_option('thaiprompt_mlm_portal_slideshow', array());
    if (!empty($_FILES['slideshow_images']['name'][0])) {
        $slideshow_images = array();
        $files = $_FILES['slideshow_images'];

        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );

                $upload = wp_handle_upload($file, array('test_form' => false));
                if (isset($upload['url'])) {
                    $slideshow_images[] = $upload['url'];
                }
            }
        }
        update_option('thaiprompt_mlm_portal_slideshow', $slideshow_images);
    }

    $settings = array(
        'placement_type' => sanitize_text_field($_POST['placement_type']),
        'max_level' => intval($_POST['max_level']),
        'commission_type' => sanitize_text_field($_POST['commission_type']),
        'fast_start_enabled' => isset($_POST['fast_start_enabled']),
        'fast_start_percentage' => floatval($_POST['fast_start_percentage']),
        'fast_start_days' => intval($_POST['fast_start_days']),
        'rank_bonus_enabled' => isset($_POST['rank_bonus_enabled']),
        'payout_minimum' => floatval($_POST['payout_minimum']),
        'payout_schedule' => sanitize_text_field($_POST['payout_schedule']),
        'currency' => sanitize_text_field($_POST['currency']),
        'genealogy_animation' => isset($_POST['genealogy_animation']),
        'woocommerce_integration' => isset($_POST['woocommerce_integration']),
        'dokan_integration' => isset($_POST['dokan_integration']),
        'portal_header_text' => sanitize_text_field($_POST['portal_header_text']),
        'portal_subtitle_text' => sanitize_text_field($_POST['portal_subtitle_text']),
        'portal_slideshow_enabled' => isset($_POST['portal_slideshow_enabled']),
        'portal_slideshow_speed' => intval($_POST['portal_slideshow_speed']),
        'level_commissions' => array()
    );

    // Save level commissions
    for ($i = 1; $i <= $settings['max_level']; $i++) {
        $settings['level_commissions'][$i] = isset($_POST['level_commission_' . $i]) ? floatval($_POST['level_commission_' . $i]) : 0;
    }

    update_option('thaiprompt_mlm_settings', $settings);

    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'thaiprompt-mlm') . '</p></div>';
}

$settings = get_option('thaiprompt_mlm_settings', array());
$defaults = array(
    'placement_type' => 'auto',
    'max_level' => 10,
    'commission_type' => 'percentage',
    'fast_start_enabled' => true,
    'fast_start_percentage' => 10,
    'fast_start_days' => 30,
    'rank_bonus_enabled' => true,
    'payout_minimum' => 100,
    'payout_schedule' => 'monthly',
    'currency' => 'THB',
    'genealogy_animation' => true,
    'woocommerce_integration' => true,
    'dokan_integration' => true,
    'portal_header_text' => 'MLM Portal',
    'portal_subtitle_text' => 'Welcome back, {name}!',
    'portal_slideshow_enabled' => false,
    'portal_slideshow_speed' => 5,
    'level_commissions' => array()
);
$settings = wp_parse_args($settings, $defaults);

// Get portal logo and slideshow
$portal_logo = get_option('thaiprompt_mlm_portal_logo', '');
$slideshow_images = get_option('thaiprompt_mlm_portal_slideshow', array());
?>

<div class="wrap">
    <h1><?php _e('MLM Settings', 'thaiprompt-mlm'); ?></h1>

    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('thaiprompt_mlm_settings'); ?>

        <div class="mlm-settings-form">
            <!-- Network Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Network Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label for="placement_type">
                        <?php _e('Placement Type', 'thaiprompt-mlm'); ?>
                    </label>
                    <select name="placement_type" id="placement_type">
                        <option value="auto" <?php selected($settings['placement_type'], 'auto'); ?>><?php _e('Auto (Find First Available)', 'thaiprompt-mlm'); ?></option>
                        <option value="left" <?php selected($settings['placement_type'], 'left'); ?>><?php _e('Left Spillover', 'thaiprompt-mlm'); ?></option>
                        <option value="right" <?php selected($settings['placement_type'], 'right'); ?>><?php _e('Right Spillover', 'thaiprompt-mlm'); ?></option>
                        <option value="balanced" <?php selected($settings['placement_type'], 'balanced'); ?>><?php _e('Balanced', 'thaiprompt-mlm'); ?></option>
                    </select>
                    <p class="mlm-form-field-description">
                        <?php _e('How new members are placed in the network tree', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="max_level">
                        <?php _e('Maximum Commission Level', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="number" name="max_level" id="max_level" value="<?php echo esc_attr($settings['max_level']); ?>" min="1" max="20">
                    <p class="mlm-form-field-description">
                        <?php _e('Maximum depth for level commissions (1-20)', 'thaiprompt-mlm'); ?>
                    </p>
                </div>
            </div>

            <!-- Commission Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Commission Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label for="commission_type">
                        <?php _e('Commission Type', 'thaiprompt-mlm'); ?>
                    </label>
                    <select name="commission_type" id="commission_type">
                        <option value="percentage" <?php selected($settings['commission_type'], 'percentage'); ?>><?php _e('Percentage', 'thaiprompt-mlm'); ?></option>
                        <option value="fixed" <?php selected($settings['commission_type'], 'fixed'); ?>><?php _e('Fixed Amount', 'thaiprompt-mlm'); ?></option>
                    </select>
                </div>

                <div class="mlm-form-field">
                    <h3><?php _e('Level Commission Percentages', 'thaiprompt-mlm'); ?></h3>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                                <th><?php _e('Percentage (%)', 'thaiprompt-mlm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= $settings['max_level']; $i++): ?>
                            <tr>
                                <td><strong><?php echo sprintf(__('Level %d', 'thaiprompt-mlm'), $i); ?></strong></td>
                                <td>
                                    <input type="number" name="level_commission_<?php echo $i; ?>" value="<?php echo esc_attr($settings['level_commissions'][$i] ?? 0); ?>" step="0.01" min="0" max="100" style="width: 100px;">
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fast Start Bonus -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Fast Start Bonus', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="fast_start_enabled" value="1" <?php checked($settings['fast_start_enabled']); ?>>
                        <?php _e('Enable Fast Start Bonus', 'thaiprompt-mlm'); ?>
                    </label>
                    <p class="mlm-form-field-description">
                        <?php _e('Give bonus commission for sales from newly registered members', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="fast_start_percentage">
                        <?php _e('Fast Start Percentage (%)', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="number" name="fast_start_percentage" id="fast_start_percentage" value="<?php echo esc_attr($settings['fast_start_percentage']); ?>" step="0.01" min="0" max="100">
                </div>

                <div class="mlm-form-field">
                    <label for="fast_start_days">
                        <?php _e('Fast Start Period (Days)', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="number" name="fast_start_days" id="fast_start_days" value="<?php echo esc_attr($settings['fast_start_days']); ?>" min="1">
                    <p class="mlm-form-field-description">
                        <?php _e('Number of days after registration to qualify for fast start bonus', 'thaiprompt-mlm'); ?>
                    </p>
                </div>
            </div>

            <!-- Rank Bonus -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Rank Bonus', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="rank_bonus_enabled" value="1" <?php checked($settings['rank_bonus_enabled']); ?>>
                        <?php _e('Enable Rank Achievement Bonuses', 'thaiprompt-mlm'); ?>
                    </label>
                    <p class="mlm-form-field-description">
                        <?php _e('Award bonuses when members achieve new ranks', 'thaiprompt-mlm'); ?>
                    </p>
                </div>
            </div>

            <!-- Payout Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Payout Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label for="payout_minimum">
                        <?php _e('Minimum Payout Amount', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="number" name="payout_minimum" id="payout_minimum" value="<?php echo esc_attr($settings['payout_minimum']); ?>" step="0.01" min="0">
                    <p class="mlm-form-field-description">
                        <?php _e('Minimum amount required for withdrawal requests', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="payout_schedule">
                        <?php _e('Payout Schedule', 'thaiprompt-mlm'); ?>
                    </label>
                    <select name="payout_schedule" id="payout_schedule">
                        <option value="weekly" <?php selected($settings['payout_schedule'], 'weekly'); ?>><?php _e('Weekly', 'thaiprompt-mlm'); ?></option>
                        <option value="biweekly" <?php selected($settings['payout_schedule'], 'biweekly'); ?>><?php _e('Bi-weekly', 'thaiprompt-mlm'); ?></option>
                        <option value="monthly" <?php selected($settings['payout_schedule'], 'monthly'); ?>><?php _e('Monthly', 'thaiprompt-mlm'); ?></option>
                    </select>
                </div>

                <div class="mlm-form-field">
                    <label for="currency">
                        <?php _e('Currency', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="text" name="currency" id="currency" value="<?php echo esc_attr($settings['currency']); ?>" maxlength="3">
                    <p class="mlm-form-field-description">
                        <?php _e('Currency code (e.g., THB, USD)', 'thaiprompt-mlm'); ?>
                    </p>
                </div>
            </div>

            <!-- Portal Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Portal Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label for="portal_header_text">
                        <?php _e('Portal Header Text', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="text" name="portal_header_text" id="portal_header_text" value="<?php echo esc_attr($settings['portal_header_text']); ?>" class="regular-text">
                    <p class="mlm-form-field-description">
                        <?php _e('Main title shown in portal header', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="portal_subtitle_text">
                        <?php _e('Portal Subtitle Text', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="text" name="portal_subtitle_text" id="portal_subtitle_text" value="<?php echo esc_attr($settings['portal_subtitle_text']); ?>" class="regular-text">
                    <p class="mlm-form-field-description">
                        <?php _e('Subtitle text. Use {name} to display user\'s name', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="portal_logo">
                        <?php _e('Portal Logo', 'thaiprompt-mlm'); ?>
                    </label>
                    <?php if ($portal_logo): ?>
                        <div style="margin: 10px 0;">
                            <img src="<?php echo esc_url($portal_logo); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; background: #fff;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="portal_logo" id="portal_logo" accept="image/*">
                    <p class="mlm-form-field-description">
                        <?php _e('Upload a logo for the portal header (recommended size: 200x60px)', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="portal_slideshow_enabled" value="1" <?php checked($settings['portal_slideshow_enabled']); ?>>
                        <?php _e('Enable Portal Slideshow', 'thaiprompt-mlm'); ?>
                    </label>
                    <p class="mlm-form-field-description">
                        <?php _e('Show slideshow in portal dashboard', 'thaiprompt-mlm'); ?>
                    </p>
                </div>

                <div class="mlm-form-field">
                    <label for="portal_slideshow_speed">
                        <?php _e('Slideshow Speed (seconds)', 'thaiprompt-mlm'); ?>
                    </label>
                    <input type="number" name="portal_slideshow_speed" id="portal_slideshow_speed" value="<?php echo esc_attr($settings['portal_slideshow_speed']); ?>" min="1" max="30" style="width: 100px;">
                </div>

                <div class="mlm-form-field">
                    <label for="slideshow_images">
                        <?php _e('Slideshow Images', 'thaiprompt-mlm'); ?>
                    </label>
                    <?php if (!empty($slideshow_images)): ?>
                        <div style="display: flex; gap: 10px; margin: 10px 0; flex-wrap: wrap;">
                            <?php foreach ($slideshow_images as $image): ?>
                                <img src="<?php echo esc_url($image); ?>" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px; background: #fff;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="slideshow_images[]" id="slideshow_images" accept="image/*" multiple>
                    <p class="mlm-form-field-description">
                        <?php _e('Upload multiple images for the portal slideshow (recommended size: 1200x400px)', 'thaiprompt-mlm'); ?>
                    </p>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Display Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="genealogy_animation" value="1" <?php checked($settings['genealogy_animation']); ?>>
                        <?php _e('Enable Genealogy Tree Animations (GSAP)', 'thaiprompt-mlm'); ?>
                    </label>
                </div>
            </div>

            <!-- Integration Settings -->
            <div class="mlm-settings-section">
                <h2 class="mlm-settings-section-title"><?php _e('Integration Settings', 'thaiprompt-mlm'); ?></h2>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="woocommerce_integration" value="1" <?php checked($settings['woocommerce_integration']); ?>>
                        <?php _e('Enable WooCommerce Integration', 'thaiprompt-mlm'); ?>
                    </label>
                </div>

                <div class="mlm-form-field">
                    <label>
                        <input type="checkbox" name="dokan_integration" value="1" <?php checked($settings['dokan_integration']); ?>>
                        <?php _e('Enable Dokan Integration', 'thaiprompt-mlm'); ?>
                    </label>
                </div>
            </div>
        </div>

        <p class="submit">
            <button type="submit" name="thaiprompt_mlm_settings_submit" class="button button-primary button-large">
                <?php _e('Save Settings', 'thaiprompt-mlm'); ?>
            </button>
        </p>
    </form>
</div>
