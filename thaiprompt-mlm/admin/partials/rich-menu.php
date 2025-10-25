<?php
/**
 * Admin Rich Menu Management
 *
 * Visual Rich Menu builder and manager
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle Rich Menu actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$error = '';

// Delete Rich Menu
if (isset($_POST['delete_rich_menu']) && check_admin_referer('thaiprompt_mlm_rich_menu')) {
    $rich_menu_id = sanitize_text_field($_POST['rich_menu_id']);
    $result = Thaiprompt_MLM_LINE_Bot::delete_rich_menu($rich_menu_id);

    if (!is_wp_error($result)) {
        $message = '✅ Rich Menu deleted successfully!';
        Thaiprompt_MLM_Logger::info('Rich Menu deleted', array('rich_menu_id' => $rich_menu_id));
    } else {
        $error = '❌ Failed to delete: ' . $result->get_error_message();
    }
}

// Set as default
if (isset($_POST['set_default_rich_menu']) && check_admin_referer('thaiprompt_mlm_rich_menu')) {
    $rich_menu_id = sanitize_text_field($_POST['rich_menu_id']);
    $result = Thaiprompt_MLM_LINE_Bot::set_default_rich_menu($rich_menu_id);

    if (!is_wp_error($result)) {
        $message = '✅ Rich Menu set as default!';
        update_option('thaiprompt_mlm_default_rich_menu_id', $rich_menu_id);
    } else {
        $error = '❌ Failed to set default: ' . $result->get_error_message();
    }
}

// Get Rich Menu list
$rich_menus_data = Thaiprompt_MLM_LINE_Bot::get_rich_menu_list();
$rich_menus = array();

if (!is_wp_error($rich_menus_data) && isset($rich_menus_data['richmenus'])) {
    $rich_menus = $rich_menus_data['richmenus'];
}

$default_rich_menu_id = get_option('thaiprompt_mlm_default_rich_menu_id', '');
?>

<div class="wrap">
    <h1 style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 32px;">🎨</span>
        <?php _e('Rich Menu Manager', 'thaiprompt-mlm'); ?>
        <a href="?page=thaiprompt-mlm-rich-menu-builder" class="page-title-action">
            ➕ <?php _e('Create New', 'thaiprompt-mlm'); ?>
        </a>
    </h1>
    <hr class="wp-header-end">

    <?php if ($message): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="notice notice-error is-dismissible"><p><?php echo $error; ?></p></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Rich Menu List -->
        <div class="postbox" style="margin: 20px 0;">
            <div class="postbox-header">
                <h2>📋 <?php _e('Rich Menus', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 0;">
                <?php if (empty($rich_menus)): ?>
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <p style="font-size: 48px; margin: 0;">📱</p>
                        <p style="font-size: 18px; margin: 10px 0;"><?php _e('No Rich Menus yet', 'thaiprompt-mlm'); ?></p>
                        <a href="?page=thaiprompt-mlm-rich-menu&action=create" class="button button-primary">
                            ➕ <?php _e('Create Your First Rich Menu', 'thaiprompt-mlm'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th><?php _e('Name', 'thaiprompt-mlm'); ?></th>
                                <th><?php _e('Size', 'thaiprompt-mlm'); ?></th>
                                <th><?php _e('Areas', 'thaiprompt-mlm'); ?></th>
                                <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                                <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rich_menus as $menu): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <?php if ($menu['richMenuId'] === $default_rich_menu_id): ?>
                                            <span style="font-size: 24px;" title="Default">⭐</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($menu['name']); ?></strong><br>
                                        <small style="color: #666;">ID: <?php echo esc_html($menu['richMenuId']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $size = $menu['size'];
                                        echo $size['width'] . ' x ' . $size['height'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo count($menu['areas']); ?> <?php _e('buttons', 'thaiprompt-mlm'); ?>
                                    </td>
                                    <td>
                                        <?php if ($menu['richMenuId'] === $default_rich_menu_id): ?>
                                            <span style="background: #10b981; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                                                <?php _e('Default', 'thaiprompt-mlm'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #6b7280; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                                                <?php _e('Inactive', 'thaiprompt-mlm'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <?php if ($menu['richMenuId'] !== $default_rich_menu_id): ?>
                                                <form method="post" style="display: inline;">
                                                    <?php wp_nonce_field('thaiprompt_mlm_rich_menu'); ?>
                                                    <input type="hidden" name="rich_menu_id" value="<?php echo esc_attr($menu['richMenuId']); ?>">
                                                    <button type="submit" name="set_default_rich_menu" class="button" onclick="return confirm('Set as default Rich Menu?');">
                                                        ⭐ <?php _e('Set Default', 'thaiprompt-mlm'); ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="post" style="display: inline;">
                                                <?php wp_nonce_field('thaiprompt_mlm_rich_menu'); ?>
                                                <input type="hidden" name="rich_menu_id" value="<?php echo esc_attr($menu['richMenuId']); ?>">
                                                <button type="submit" name="delete_rich_menu" class="button" style="color: #dc3232;" onclick="return confirm('Delete this Rich Menu?');">
                                                    🗑️ <?php _e('Delete', 'thaiprompt-mlm'); ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Guide -->
        <div class="postbox">
            <div class="postbox-header" style="cursor: pointer;" onclick="jQuery('#rich-menu-guide').slideToggle();">
                <h2>📚 <?php _e('Rich Menu Guide', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div id="rich-menu-guide" class="inside" style="display: none; padding: 20px;">
                <h3><?php _e('What is Rich Menu?', 'thaiprompt-mlm'); ?></h3>
                <p><?php _e('Rich Menu คือเมนูที่แสดงที่ด้านล่างของหน้าแชท LINE ช่วยให้ผู้ใช้เข้าถึงฟีเจอร์ต่างๆ ได้ง่าย', 'thaiprompt-mlm'); ?></p>

                <h3><?php _e('Rich Menu Sizes', 'thaiprompt-mlm'); ?></h3>
                <ul>
                    <li><strong>Full (2500 x 1686):</strong> <?php _e('เมนูใหญ่ เต็มหน้าจอ', 'thaiprompt-mlm'); ?></li>
                    <li><strong>Half (2500 x 843):</strong> <?php _e('เมนูครึ่งหน้าจอ (แนะนำ)', 'thaiprompt-mlm'); ?></li>
                </ul>

                <h3><?php _e('Button Layouts', 'thaiprompt-mlm'); ?></h3>
                <ul>
                    <li><strong>2 ปุ่ม:</strong> แบ่งครึ่งซ้าย-ขวา</li>
                    <li><strong>3 ปุ่ม:</strong> แบ่ง 3 ส่วนเท่าๆ กัน</li>
                    <li><strong>4 ปุ่ม:</strong> 2x2 Grid</li>
                    <li><strong>6 ปุ่ม:</strong> 2x3 Grid (แนะนำ)</li>
                </ul>

                <h3><?php _e('Tips', 'thaiprompt-mlm'); ?></h3>
                <ul>
                    <li>✅ ใช้รูปภาพ PNG หรือ JPEG ขนาดไม่เกิน 1MB</li>
                    <li>✅ ความละเอียดต้องตรงตามขนาดที่กำหนด</li>
                    <li>✅ ใช้ Tools ออกแบบ: <a href="https://www.figma.com/templates/rich-menu-line/" target="_blank">Figma</a>, <a href="https://developers.line.biz/en/docs/messaging-api/using-rich-menus/" target="_blank">LINE Rich Menu Maker</a></li>
                </ul>
            </div>
        </div>

    <?php elseif ($action === 'create'): ?>
        <!-- Create Rich Menu -->
        <?php include_once THAIPROMPT_MLM_PLUGIN_DIR . 'admin/partials/rich-menu-builder.php'; ?>
    <?php endif; ?>
</div>

<style>
.wp-list-table tbody tr:hover {
    background-color: #f5f5f5;
}
</style>
