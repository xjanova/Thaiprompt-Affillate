<?php
/**
 * Rich Menu Builder
 *
 * Visual Rich Menu creation tool
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['create_rich_menu']) && check_admin_referer('thaiprompt_mlm_create_rich_menu')) {
    $name = sanitize_text_field($_POST['menu_name']);
    $chat_bar_text = sanitize_text_field($_POST['chat_bar_text']);
    $size_type = sanitize_text_field($_POST['size_type']);
    $template = sanitize_text_field($_POST['template']);

    // Get template configuration
    $templates = Thaiprompt_MLM_Rich_Menu_Templates::get_templates();
    $template_config = $templates[$template] ?? $templates['template_6_grid'];

    // Create Rich Menu
    $rich_menu_data = array(
        'size' => $template_config['size'],
        'selected' => true,
        'name' => $name,
        'chatBarText' => $chat_bar_text,
        'areas' => $template_config['areas']
    );

    $result = Thaiprompt_MLM_LINE_Bot::create_rich_menu($rich_menu_data);

    if (!is_wp_error($result)) {
        $rich_menu_id = $result;

        // Upload image if provided
        if (!empty($_FILES['rich_menu_image']['name'])) {
            $upload = wp_handle_upload($_FILES['rich_menu_image'], array('test_form' => false));

            if (isset($upload['file'])) {
                $upload_result = Thaiprompt_MLM_LINE_Bot::upload_rich_menu_image($rich_menu_id, $upload['file']);

                if (!is_wp_error($upload_result)) {
                    echo '<div class="notice notice-success"><p>✅ Rich Menu created and image uploaded successfully!</p></div>';
                    Thaiprompt_MLM_Logger::info('Rich Menu created', array('id' => $rich_menu_id, 'name' => $name));

                    echo '<script>window.location.href = "?page=thaiprompt-mlm-rich-menu";</script>';
                } else {
                    echo '<div class="notice notice-warning"><p>⚠️ Rich Menu created but image upload failed: ' . $upload_result->get_error_message() . '</p></div>';
                }
            }
        } else {
            echo '<div class="notice notice-success"><p>✅ Rich Menu created! Please upload an image.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>❌ Failed to create Rich Menu: ' . $result->get_error_message() . '</p></div>';
    }
}
?>

<div style="max-width: 1200px;">
    <a href="?page=thaiprompt-mlm-rich-menu" class="button" style="margin-bottom: 20px;">
        ← <?php _e('Back to List', 'thaiprompt-mlm'); ?>
    </a>

    <div class="postbox">
        <div class="postbox-header">
            <h2>🎨 <?php _e('Create Rich Menu', 'thaiprompt-mlm'); ?></h2>
        </div>
        <div class="inside" style="padding: 20px;">
            <form method="post" enctype="multipart/form-data" id="rich-menu-form">
                <?php wp_nonce_field('thaiprompt_mlm_create_rich_menu'); ?>

                <!-- Step 1: Basic Info -->
                <div class="rich-menu-step">
                    <h3>📝 <?php _e('Step 1: Basic Information', 'thaiprompt-mlm'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="menu_name"><?php _e('Menu Name', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="menu_name" id="menu_name" class="regular-text" required placeholder="<?php _e('e.g., Main Menu', 'thaiprompt-mlm'); ?>">
                                <p class="description"><?php _e('Internal name for management (not shown to users)', 'thaiprompt-mlm'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="chat_bar_text"><?php _e('Chat Bar Text', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="chat_bar_text" id="chat_bar_text" class="regular-text" required maxlength="14" placeholder="<?php _e('e.g., Menu', 'thaiprompt-mlm'); ?>">
                                <p class="description"><?php _e('Text shown on chat bar (max 14 characters)', 'thaiprompt-mlm'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Step 2: Choose Template -->
                <div class="rich-menu-step" style="margin-top: 30px;">
                    <h3>🎯 <?php _e('Step 2: Choose Template', 'thaiprompt-mlm'); ?></h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
                        <!-- 2 Buttons -->
                        <label class="template-card">
                            <input type="radio" name="template" value="template_2_horizontal" required>
                            <div class="template-preview">
                                <div style="font-size: 24px; margin-bottom: 10px;">📱</div>
                                <div class="template-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; background: #f0f0f0; padding: 10px; border-radius: 8px; aspect-ratio: 2500/843;">
                                    <div style="background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">1</div>
                                    <div style="background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">2</div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong><?php _e('2 Buttons (Horizontal)', 'thaiprompt-mlm'); ?></strong><br>
                                    <small style="color: #666;">2500 x 843px</small>
                                </div>
                            </div>
                        </label>

                        <!-- 3 Buttons -->
                        <label class="template-card">
                            <input type="radio" name="template" value="template_3_horizontal" required>
                            <div class="template-preview">
                                <div style="font-size: 24px; margin-bottom: 10px;">📱</div>
                                <div class="template-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px; background: #f0f0f0; padding: 10px; border-radius: 8px; aspect-ratio: 2500/843;">
                                    <div style="background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">1</div>
                                    <div style="background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">2</div>
                                    <div style="background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">3</div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong><?php _e('3 Buttons (Horizontal)', 'thaiprompt-mlm'); ?></strong><br>
                                    <small style="color: #666;">2500 x 843px</small>
                                </div>
                            </div>
                        </label>

                        <!-- 4 Buttons -->
                        <label class="template-card">
                            <input type="radio" name="template" value="template_4_grid" required>
                            <div class="template-preview">
                                <div style="font-size: 24px; margin-bottom: 10px;">📱</div>
                                <div class="template-grid" style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 4px; background: #f0f0f0; padding: 10px; border-radius: 8px; aspect-ratio: 2500/843;">
                                    <div style="background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">1</div>
                                    <div style="background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">2</div>
                                    <div style="background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">3</div>
                                    <div style="background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">4</div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong><?php _e('4 Buttons (2x2 Grid)', 'thaiprompt-mlm'); ?></strong><br>
                                    <small style="color: #666;">2500 x 843px</small>
                                </div>
                            </div>
                        </label>

                        <!-- 6 Buttons (Most Popular) -->
                        <label class="template-card">
                            <input type="radio" name="template" value="template_6_grid" required checked>
                            <div class="template-preview">
                                <div style="font-size: 24px; margin-bottom: 10px;">📱 <span style="background: #10b981; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 5px;">แนะนำ</span></div>
                                <div class="template-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 4px; background: #f0f0f0; padding: 10px; border-radius: 8px; aspect-ratio: 2500/843;">
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">1</div>
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">2</div>
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">3</div>
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">4</div>
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">5</div>
                                    <div style="background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px;">6</div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong><?php _e('6 Buttons (3x2 Grid)', 'thaiprompt-mlm'); ?></strong><br>
                                    <small style="color: #666;">2500 x 843px</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <input type="hidden" name="size_type" value="half">
                </div>

                <!-- Step 3: Upload Image -->
                <div class="rich-menu-step" style="margin-top: 30px;">
                    <h3>🖼️ <?php _e('Step 3: Upload Rich Menu Image', 'thaiprompt-mlm'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rich_menu_image"><?php _e('Menu Image', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                            </th>
                            <td>
                                <input type="file" name="rich_menu_image" id="rich_menu_image" accept="image/png,image/jpeg" required>
                                <p class="description">
                                    <?php _e('Image size: 2500 x 843 pixels (Half) | Format: PNG or JPEG | Max size: 1MB', 'thaiprompt-mlm'); ?><br>
                                    <a href="https://www.figma.com/community/search?model_type=hub_files&q=line%20rich%20menu" target="_blank">
                                        <?php _e('Download templates from Figma →', 'thaiprompt-mlm'); ?>
                                    </a>
                                </p>

                                <!-- Preview -->
                                <div id="image-preview" style="margin-top: 15px; display: none;">
                                    <img src="" alt="Preview" style="max-width: 100%; height: auto; border: 2px solid #ddd; border-radius: 8px;">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Submit -->
                <p class="submit" style="margin-top: 30px;">
                    <button type="submit" name="create_rich_menu" class="button button-primary button-large">
                        ✨ <?php _e('Create Rich Menu', 'thaiprompt-mlm'); ?>
                    </button>
                    <a href="?page=thaiprompt-mlm-rich-menu" class="button button-large">
                        <?php _e('Cancel', 'thaiprompt-mlm'); ?>
                    </a>
                </p>
            </form>
        </div>
    </div>

    <!-- Download Templates -->
    <div class="postbox" style="margin-top: 20px;">
        <div class="postbox-header">
            <h2>📥 <?php _e('Download Rich Menu Templates', 'thaiprompt-mlm'); ?></h2>
        </div>
        <div class="inside" style="padding: 20px;">
            <p><?php _e('ดาวน์โหลด Template สำเร็จรูปเพื่อแก้ไขใน Photoshop หรือ Figma:', 'thaiprompt-mlm'); ?></p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <a href="https://www.figma.com/community/file/1234567890/LINE-Rich-Menu-Template" target="_blank" class="button button-secondary" style="height: auto; padding: 15px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;">🎨</div>
                    <strong>Figma Templates</strong><br>
                    <small>แนะนำ - ใช้งานง่าย</small>
                </a>

                <a href="https://developers.line.biz/en/docs/messaging-api/using-rich-menus/" target="_blank" class="button button-secondary" style="height: auto; padding: 15px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;">📖</div>
                    <strong>LINE Docs</strong><br>
                    <small>คู่มือและตัวอย่าง</small>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.template-card {
    cursor: pointer;
    display: block;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.template-card:hover {
    border-color: #8b5cf6;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.template-card input[type="radio"] {
    display: none;
}

.template-card input[type="radio"]:checked + .template-preview {
    border-color: #8b5cf6;
}

.template-card input[type="radio"]:checked ~ .template-preview {
    border: 3px solid #8b5cf6;
    border-radius: 8px;
}

.template-preview {
    text-align: center;
    padding: 10px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: border-color 0.3s ease;
}

.rich-menu-step {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #8b5cf6;
}

.rich-menu-step h3 {
    margin-top: 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Image preview
    $('#rich_menu_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').show().find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Chat bar text character counter
    $('#chat_bar_text').on('input', function() {
        const length = $(this).val().length;
        const max = 14;
        if (length > max) {
            $(this).val($(this).val().substring(0, max));
        }
    });
});
</script>
