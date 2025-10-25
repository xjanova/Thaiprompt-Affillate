<?php
/**
 * Admin LINE Settings Page
 *
 * Complete LINE OA configuration with step-by-step guide
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['save_line_settings']) && check_admin_referer('thaiprompt_mlm_line_settings')) {
    // Save LINE settings
    update_option('thaiprompt_mlm_line_channel_id', sanitize_text_field($_POST['line_channel_id']));
    update_option('thaiprompt_mlm_line_channel_secret', sanitize_text_field($_POST['line_channel_secret']));
    update_option('thaiprompt_mlm_line_channel_access_token', sanitize_textarea_field($_POST['line_channel_access_token']));
    update_option('thaiprompt_mlm_line_oa_id', sanitize_text_field($_POST['line_oa_id']));
    update_option('thaiprompt_mlm_line_webhook_enabled', isset($_POST['line_webhook_enabled']) ? 1 : 0);
    update_option('thaiprompt_mlm_line_auto_register', isset($_POST['line_auto_register']) ? 1 : 0);
    update_option('thaiprompt_mlm_line_welcome_message', sanitize_textarea_field($_POST['line_welcome_message']));

    echo '<div class="notice notice-success"><p>✅ LINE settings saved successfully!</p></div>';

    Thaiprompt_MLM_Logger::info('LINE settings updated');
}

// Test connection
if (isset($_POST['test_line_connection']) && check_admin_referer('thaiprompt_mlm_line_test')) {
    $test_result = Thaiprompt_MLM_LINE_Bot::get_profile('test');

    if (is_wp_error($test_result)) {
        echo '<div class="notice notice-error"><p>❌ Connection test failed: ' . esc_html($test_result->get_error_message()) . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>✅ Connection successful!</p></div>';
    }
}

// Get current settings
$channel_id = get_option('thaiprompt_mlm_line_channel_id', '');
$channel_secret = get_option('thaiprompt_mlm_line_channel_secret', '');
$channel_access_token = get_option('thaiprompt_mlm_line_channel_access_token', '');
$oa_id = get_option('thaiprompt_mlm_line_oa_id', '');
$webhook_enabled = get_option('thaiprompt_mlm_line_webhook_enabled', 0);
$auto_register = get_option('thaiprompt_mlm_line_auto_register', 1);
$welcome_message = get_option('thaiprompt_mlm_line_welcome_message', 'สวัสดีครับ! ยินดีต้อนรับสู่ระบบ MLM 🎉');

// Generate webhook URL
$webhook_url = home_url('/wp-json/thaiprompt-mlm/v1/line-webhook');
?>

<div class="wrap">
    <h1 style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 32px;">💬</span>
        <?php _e('LINE OA Settings', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Quick Status -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card" style="<?php echo !empty($channel_id) ? 'border-left: 4px solid #10b981;' : 'border-left: 4px solid #ef4444;'; ?>">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Configuration Status', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon"><?php echo !empty($channel_id) ? '✅' : '⚠️'; ?></span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php echo !empty($channel_id) && !empty($channel_secret) && !empty($channel_access_token) ? 'Configured' : 'Not Configured'; ?>
            </div>
        </div>
        <div class="mlm-card" style="<?php echo $webhook_enabled ? 'border-left: 4px solid #10b981;' : 'border-left: 4px solid #f59e0b;'; ?>">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Webhook', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">🔗</span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php echo $webhook_enabled ? 'Enabled' : 'Disabled'; ?>
            </div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Auto Registration', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">👤</span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php echo $auto_register ? 'Enabled' : 'Disabled'; ?>
            </div>
        </div>
    </div>

    <!-- Setup Guide (Collapsible) -->
    <div class="postbox" style="margin: 20px 0;">
        <div class="postbox-header" style="cursor: pointer;" onclick="jQuery('#setup-guide').slideToggle();">
            <h2 style="margin: 0; padding: 10px 15px; display: flex; align-items: center; gap: 10px;">
                📚 <?php _e('Complete Setup Guide', 'thaiprompt-mlm'); ?>
                <span style="font-size: 12px; color: #666; font-weight: normal;">(Click to expand)</span>
            </h2>
        </div>
        <div id="setup-guide" class="inside" style="display: none; padding: 20px;">
            <div style="background: #f0f6fc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🎯 ขั้นตอนที่ 1: สร้าง LINE Official Account</h3>
                <ol style="line-height: 2;">
                    <li>ไปที่ <a href="https://developers.line.biz/console/" target="_blank"><strong>LINE Developers Console</strong></a></li>
                    <li>คลิก <strong>"Create a new provider"</strong> (ถ้ายังไม่มี) หรือเลือก Provider ที่มีอยู่</li>
                    <li>คลิก <strong>"Create a new channel"</strong></li>
                    <li>เลือก <strong>"Messaging API"</strong></li>
                    <li>กรอกข้อมูล:
                        <ul>
                            <li><strong>Channel name:</strong> ชื่อ LINE OA ของคุณ</li>
                            <li><strong>Channel description:</strong> คำอธิบายสั้นๆ</li>
                            <li><strong>Category:</strong> เลือกหมวดหมู่ที่เหมาะสม</li>
                            <li><strong>Subcategory:</strong> เลือกหมวดหมู่ย่อย</li>
                        </ul>
                    </li>
                    <li>ยอมรับเงื่อนไข และคลิก <strong>"Create"</strong></li>
                </ol>
            </div>

            <div style="background: #fff8e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🔑 ขั้นตอนที่ 2: รับ Channel ID และ Channel Secret</h3>
                <ol style="line-height: 2;">
                    <li>เข้าไปที่ Channel ที่สร้างไว้</li>
                    <li>คลิกแท็บ <strong>"Basic settings"</strong></li>
                    <li>หา <strong>Channel ID</strong> (เป็นตัวเลข เช่น 1234567890)</li>
                    <li>หา <strong>Channel secret</strong> แล้วคลิก "Show" เพื่อดูค่า</li>
                    <li>คัดลอกทั้งสองค่ามาวางในฟอร์มด้านล่าง</li>
                </ol>
            </div>

            <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🎫 ขั้นตอนที่ 3: สร้าง Channel Access Token</h3>
                <ol style="line-height: 2;">
                    <li>คลิกแท็บ <strong>"Messaging API"</strong></li>
                    <li>เลื่อนลงมาหา <strong>"Channel access token (long-lived)"</strong></li>
                    <li>คลิก <strong>"Issue"</strong> เพื่อสร้าง Token ใหม่</li>
                    <li>คัดลอก Token ที่ได้มา (ยาวประมาณ 170+ ตัวอักษร)</li>
                    <li><strong>⚠️ สำคัญ:</strong> เก็บ Token นี้ไว้ในที่ปลอดภัย ห้ามแชร์ให้ใคร</li>
                </ol>
            </div>

            <div style="background: #f3e5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🆔 ขั้นตอนที่ 4: หา LINE OA ID</h3>
                <ol style="line-height: 2;">
                    <li>ในแท็บ <strong>"Messaging API"</strong> เลื่อนลงมาหา <strong>"Bot basic ID"</strong></li>
                    <li>จะเป็นรูปแบบ <code>@abc1234</code> (มี @ นำหน้า)</li>
                    <li>คัดลอกมาวางในฟอร์มด้านล่าง (รวม @ ด้วย)</li>
                </ol>
            </div>

            <div style="background: #e8f5e9; padding: 20px; border-radius: 8px;">
                <h3 style="margin-top: 0;">🔗 ขั้นตอนที่ 5: ตั้งค่า Webhook</h3>
                <ol style="line-height: 2;">
                    <li>ในแท็บ <strong>"Messaging API"</strong> หาส่วน <strong>"Webhook settings"</strong></li>
                    <li>คลิก <strong>"Edit"</strong> ที่ Webhook URL</li>
                    <li>วาง URL นี้: <code style="background: #fff; padding: 4px 8px; border-radius: 4px;"><?php echo esc_html($webhook_url); ?></code></li>
                    <li>คลิก <strong>"Update"</strong></li>
                    <li>เปิด <strong>"Use webhook"</strong> (toggle เป็นสีเขียว)</li>
                    <li>คลิก <strong>"Verify"</strong> เพื่อทดสอบ Webhook</li>
                </ol>

                <div style="margin-top: 15px; padding: 15px; background: #fff; border-left: 4px solid #4caf50; border-radius: 4px;">
                    <strong>💡 Webhook URL ของคุณ:</strong><br>
                    <code style="font-size: 14px; background: #f5f5f5; padding: 8px; display: block; margin-top: 5px; border-radius: 4px;">
                        <?php echo esc_html($webhook_url); ?>
                    </code>
                    <button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js($webhook_url); ?>'); alert('Copied!');" style="margin-top: 10px;">
                        📋 Copy Webhook URL
                    </button>
                </div>
            </div>

            <div style="background: #ffebee; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <h3 style="margin-top: 0;">⚙️ ขั้นตอนที่ 6: ตั้งค่าเพิ่มเติม (สำคัญ!)</h3>
                <ol style="line-height: 2;">
                    <li>ในแท็บ <strong>"Messaging API"</strong> ตั้งค่าดังนี้:
                        <ul>
                            <li><strong>Auto-reply messages:</strong> ปิด (Disabled)</li>
                            <li><strong>Greeting messages:</strong> ปิด (Disabled)</li>
                            <li><strong>Allow bot to join group chats:</strong> เปิด (ถ้าต้องการ)</li>
                        </ul>
                    </li>
                    <li>บันทึกการตั้งค่า</li>
                </ol>
                <p style="margin: 10px 0 0; color: #d32f2f;">
                    <strong>⚠️ หมายเหตุ:</strong> ต้องปิด Auto-reply และ Greeting messages เพื่อให้บอทของเราทำงานได้ถูกต้อง
                </p>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <form method="post" action="">
        <?php wp_nonce_field('thaiprompt_mlm_line_settings'); ?>

        <div class="postbox">
            <div class="postbox-header">
                <h2>⚙️ <?php _e('LINE Channel Configuration', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">

                <!-- Channel ID -->
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="line_channel_id"><?php _e('Channel ID', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="line_channel_id" id="line_channel_id" value="<?php echo esc_attr($channel_id); ?>" class="regular-text" placeholder="1234567890" required>
                            <p class="description"><?php _e('ตัวเลข Channel ID จาก LINE Developers Console (Basic settings)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="line_channel_secret"><?php _e('Channel Secret', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="line_channel_secret" id="line_channel_secret" value="<?php echo esc_attr($channel_secret); ?>" class="regular-text" placeholder="abcdef1234567890..." required>
                            <p class="description"><?php _e('Channel Secret จาก Basic settings (กด Show เพื่อดู)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="line_channel_access_token"><?php _e('Channel Access Token', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                        </th>
                        <td>
                            <textarea name="line_channel_access_token" id="line_channel_access_token" rows="3" class="large-text" placeholder="eyJhbGciOiJIUzI1..." required><?php echo esc_textarea($channel_access_token); ?></textarea>
                            <p class="description"><?php _e('Long-lived Channel Access Token จาก Messaging API (กด Issue เพื่อสร้าง)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="line_oa_id"><?php _e('LINE OA ID', 'thaiprompt-mlm'); ?> <span style="color: #dc3232;">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="line_oa_id" id="line_oa_id" value="<?php echo esc_attr($oa_id); ?>" class="regular-text" placeholder="@abc1234" required>
                            <p class="description"><?php _e('Bot basic ID (รวม @ ด้วย) เช่น @abc1234', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e('Webhook URL', 'thaiprompt-mlm'); ?>
                        </th>
                        <td>
                            <input type="text" value="<?php echo esc_attr($webhook_url); ?>" class="large-text" readonly>
                            <button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js($webhook_url); ?>'); alert('Copied!');">
                                📋 <?php _e('Copy', 'thaiprompt-mlm'); ?>
                            </button>
                            <p class="description"><?php _e('วาง URL นี้ใน LINE Developers Console → Messaging API → Webhook URL', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e('Enable Webhook', 'thaiprompt-mlm'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="line_webhook_enabled" value="1" <?php checked($webhook_enabled, 1); ?>>
                                <?php _e('เปิดใช้งาน Webhook (รับข้อความจาก LINE)', 'thaiprompt-mlm'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e('Auto Registration', 'thaiprompt-mlm'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="line_auto_register" value="1" <?php checked($auto_register, 1); ?>>
                                <?php _e('สมัครสมาชิกอัตโนมัติเมื่อ Add Friend', 'thaiprompt-mlm'); ?>
                            </label>
                            <p class="description"><?php _e('ระบบจะสร้างบัญชีให้อัตโนมัติจากข้อมูล LINE Profile', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="line_welcome_message"><?php _e('Welcome Message', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <textarea name="line_welcome_message" id="line_welcome_message" rows="4" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea>
                            <p class="description"><?php _e('ข้อความต้อนรับเมื่อผู้ใช้ Add Friend', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="save_line_settings" class="button button-primary button-large">
                        💾 <?php _e('Save Settings', 'thaiprompt-mlm'); ?>
                    </button>
                </p>
            </div>
        </div>
    </form>

    <!-- Test Connection -->
    <?php if (!empty($channel_access_token)): ?>
    <form method="post" action="" style="margin-top: 20px;">
        <?php wp_nonce_field('thaiprompt_mlm_line_test'); ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2>🧪 <?php _e('Test Connection', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <p><?php _e('ทดสอบการเชื่อมต่อกับ LINE Messaging API', 'thaiprompt-mlm'); ?></p>
                <button type="submit" name="test_line_connection" class="button button-secondary">
                    🔍 <?php _e('Test Connection', 'thaiprompt-mlm'); ?>
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

</div>

<style>
.mlm-dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.mlm-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.mlm-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.mlm-card-title {
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.mlm-card-icon {
    font-size: 24px;
}

.mlm-card-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}
</style>
