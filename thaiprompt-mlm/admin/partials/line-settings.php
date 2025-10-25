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

    // Save AI settings
    update_option('thaiprompt_mlm_ai_provider', sanitize_text_field($_POST['ai_provider'] ?? 'none'));
    update_option('thaiprompt_mlm_chatgpt_api_key', sanitize_text_field($_POST['chatgpt_api_key'] ?? ''));
    update_option('thaiprompt_mlm_chatgpt_model', sanitize_text_field($_POST['chatgpt_model'] ?? 'gpt-4o-mini'));
    update_option('thaiprompt_mlm_gemini_api_key', sanitize_text_field($_POST['gemini_api_key'] ?? ''));
    update_option('thaiprompt_mlm_gemini_model', sanitize_text_field($_POST['gemini_model'] ?? 'gemini-2.0-flash-exp'));
    update_option('thaiprompt_mlm_deepseek_api_key', sanitize_text_field($_POST['deepseek_api_key'] ?? ''));
    update_option('thaiprompt_mlm_deepseek_model', sanitize_text_field($_POST['deepseek_model'] ?? 'deepseek-chat'));
    update_option('thaiprompt_mlm_ai_system_prompt', sanitize_textarea_field($_POST['ai_system_prompt'] ?? ''));

    // Save AI Knowledge Sources
    $knowledge_sources = isset($_POST['ai_knowledge_sources']) && is_array($_POST['ai_knowledge_sources'])
        ? array_map('sanitize_text_field', $_POST['ai_knowledge_sources'])
        : array('general');
    update_option('thaiprompt_mlm_ai_knowledge_sources', $knowledge_sources);

    $knowledge_posts = isset($_POST['ai_knowledge_posts']) && is_array($_POST['ai_knowledge_posts'])
        ? array_map('intval', $_POST['ai_knowledge_posts'])
        : array();
    update_option('thaiprompt_mlm_ai_knowledge_posts', $knowledge_posts);

    update_option('thaiprompt_mlm_ai_knowledge_links', sanitize_textarea_field($_POST['ai_knowledge_links'] ?? ''));
    update_option('thaiprompt_mlm_ai_knowledge_custom', sanitize_textarea_field($_POST['ai_knowledge_custom'] ?? ''));
    update_option('thaiprompt_mlm_ai_response_mode', sanitize_text_field($_POST['ai_response_mode'] ?? 'flexible'));

    echo '<div class="notice notice-success"><p>✅ LINE & AI settings saved successfully!</p></div>';

    Thaiprompt_MLM_Logger::info('LINE and AI settings updated');
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

                <!-- AI Integration Settings -->
                <h2 style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #ddd;">
                    🤖 <?php _e('AI Integration', 'thaiprompt-mlm'); ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ai_provider"><?php _e('AI Provider', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $ai_provider = get_option('thaiprompt_mlm_ai_provider', 'none');
                            $providers = Thaiprompt_MLM_AI_Handler::get_providers();
                            ?>
                            <select name="ai_provider" id="ai_provider" class="regular-text">
                                <?php foreach ($providers as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($ai_provider, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('เลือก AI ที่จะใช้ในการตอบกลับข้อความอัตโนมัติ', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <!-- ChatGPT Settings -->
                    <tr class="ai-settings chatgpt-settings" style="<?php echo $ai_provider !== 'chatgpt' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="chatgpt_api_key"><?php _e('ChatGPT API Key', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="chatgpt_api_key" id="chatgpt_api_key" class="regular-text" value="<?php echo esc_attr(get_option('thaiprompt_mlm_chatgpt_api_key', '')); ?>" placeholder="sk-...">
                            <p class="description">
                                <?php _e('Get your API key from', 'thaiprompt-mlm'); ?>
                                <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                            </p>
                        </td>
                    </tr>
                    <tr class="ai-settings chatgpt-settings" style="<?php echo $ai_provider !== 'chatgpt' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="chatgpt_model"><?php _e('ChatGPT Model', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php $chatgpt_model = get_option('thaiprompt_mlm_chatgpt_model', 'gpt-4o-mini'); ?>
                            <select name="chatgpt_model" id="chatgpt_model" class="regular-text">
                                <option value="gpt-4o" <?php selected($chatgpt_model, 'gpt-4o'); ?>>GPT-4o</option>
                                <option value="gpt-4o-mini" <?php selected($chatgpt_model, 'gpt-4o-mini'); ?>>GPT-4o Mini (Recommended)</option>
                                <option value="gpt-4-turbo" <?php selected($chatgpt_model, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            </select>
                        </td>
                    </tr>

                    <!-- Gemini Settings -->
                    <tr class="ai-settings gemini-settings" style="<?php echo $ai_provider !== 'gemini' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="gemini_api_key"><?php _e('Gemini API Key', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="gemini_api_key" id="gemini_api_key" class="regular-text" value="<?php echo esc_attr(get_option('thaiprompt_mlm_gemini_api_key', '')); ?>" placeholder="AIza...">
                            <p class="description">
                                <?php _e('Get your API key from', 'thaiprompt-mlm'); ?>
                                <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>
                            </p>
                        </td>
                    </tr>
                    <tr class="ai-settings gemini-settings" style="<?php echo $ai_provider !== 'gemini' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="gemini_model"><?php _e('Gemini Model', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php $gemini_model = get_option('thaiprompt_mlm_gemini_model', 'gemini-2.0-flash-exp'); ?>
                            <select name="gemini_model" id="gemini_model" class="regular-text">
                                <option value="gemini-2.0-flash-exp" <?php selected($gemini_model, 'gemini-2.0-flash-exp'); ?>>Gemini 2.0 Flash (Recommended)</option>
                                <option value="gemini-1.5-flash" <?php selected($gemini_model, 'gemini-1.5-flash'); ?>>Gemini 1.5 Flash</option>
                                <option value="gemini-1.5-pro" <?php selected($gemini_model, 'gemini-1.5-pro'); ?>>Gemini 1.5 Pro</option>
                            </select>
                        </td>
                    </tr>

                    <!-- DeepSeek Settings -->
                    <tr class="ai-settings deepseek-settings" style="<?php echo $ai_provider !== 'deepseek' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="deepseek_api_key"><?php _e('DeepSeek API Key', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="deepseek_api_key" id="deepseek_api_key" class="regular-text" value="<?php echo esc_attr(get_option('thaiprompt_mlm_deepseek_api_key', '')); ?>" placeholder="sk-...">
                            <p class="description">
                                <?php _e('Get your API key from', 'thaiprompt-mlm'); ?>
                                <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek Platform</a>
                            </p>
                        </td>
                    </tr>
                    <tr class="ai-settings deepseek-settings" style="<?php echo $ai_provider !== 'deepseek' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="deepseek_model"><?php _e('DeepSeek Model', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php $deepseek_model = get_option('thaiprompt_mlm_deepseek_model', 'deepseek-chat'); ?>
                            <select name="deepseek_model" id="deepseek_model" class="regular-text">
                                <option value="deepseek-chat" <?php selected($deepseek_model, 'deepseek-chat'); ?>>DeepSeek Chat (Recommended)</option>
                            </select>
                        </td>
                    </tr>

                    <!-- System Prompt -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ai_system_prompt"><?php _e('System Prompt', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $default_prompt = "You are a helpful MLM assistant for " . get_bloginfo('name') . ". Help members with their referral program, earnings, and network building.";
                            $system_prompt = get_option('thaiprompt_mlm_ai_system_prompt', $default_prompt);
                            ?>
                            <textarea name="ai_system_prompt" id="ai_system_prompt" rows="6" class="large-text"><?php echo esc_textarea($system_prompt); ?></textarea>
                            <p class="description"><?php _e('กำหนดบุคลิกและบทบาทของ AI (System Prompt)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <!-- AI Knowledge Sources -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row" colspan="2" style="padding-top: 30px;">
                            <h3 style="margin: 0; padding: 15px 0; border-top: 1px solid #ddd;">
                                📚 <?php _e('AI Knowledge Sources', 'thaiprompt-mlm'); ?>
                            </h3>
                            <p style="font-weight: normal; color: #666; margin-top: 10px;">
                                <?php _e('กำหนดแหล่งข้อมูลที่ AI จะใช้ในการตอบคำถาม (สามารถเลือกได้หลายแหล่ง)', 'thaiprompt-mlm'); ?>
                            </p>
                        </th>
                    </tr>

                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label><?php _e('Enable Knowledge Sources', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $knowledge_sources = get_option('thaiprompt_mlm_ai_knowledge_sources', array('general'));
                            if (!is_array($knowledge_sources)) {
                                $knowledge_sources = array('general');
                            }
                            ?>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="ai_knowledge_sources[]" value="general" <?php checked(in_array('general', $knowledge_sources)); ?>>
                                    <?php _e('General Knowledge (AI default)', 'thaiprompt-mlm'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="ai_knowledge_sources[]" value="website" <?php checked(in_array('website', $knowledge_sources)); ?>>
                                    <?php _e('Website Information (Site name, description)', 'thaiprompt-mlm'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="ai_knowledge_sources[]" value="posts" <?php checked(in_array('posts', $knowledge_sources)); ?>>
                                    <?php _e('Selected Posts/Articles', 'thaiprompt-mlm'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="ai_knowledge_sources[]" value="links" <?php checked(in_array('links', $knowledge_sources)); ?>>
                                    <?php _e('External Links', 'thaiprompt-mlm'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="ai_knowledge_sources[]" value="custom" <?php checked(in_array('custom', $knowledge_sources)); ?>>
                                    <?php _e('Custom Knowledge Base', 'thaiprompt-mlm'); ?>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php _e('เลือกแหล่งข้อมูลที่ต้องการให้ AI ใช้ในการตอบคำถาม', 'thaiprompt-mlm'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Selected Posts -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ai_knowledge_posts"><?php _e('Selected Posts', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $selected_posts = get_option('thaiprompt_mlm_ai_knowledge_posts', array());
                            if (!is_array($selected_posts)) {
                                $selected_posts = array();
                            }

                            $posts = get_posts(array(
                                'numberposts' => -1,
                                'post_type' => 'post',
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ));
                            ?>
                            <select name="ai_knowledge_posts[]" id="ai_knowledge_posts" multiple class="regular-text" style="height: 150px;">
                                <?php if (!empty($posts)): ?>
                                    <?php foreach ($posts as $post): ?>
                                        <option value="<?php echo $post->ID; ?>" <?php echo in_array($post->ID, $selected_posts) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($post->post_title); ?> (<?php echo date('Y-m-d', strtotime($post->post_date)); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option disabled><?php _e('No posts available', 'thaiprompt-mlm'); ?></option>
                                <?php endif; ?>
                            </select>
                            <p class="description">
                                <?php _e('เลือกบทความที่ต้องการให้ AI ใช้ในการตอบคำถาม (กด Ctrl/Cmd เพื่อเลือกหลายรายการ)', 'thaiprompt-mlm'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- External Links -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ai_knowledge_links"><?php _e('External Links', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $knowledge_links = get_option('thaiprompt_mlm_ai_knowledge_links', '');
                            ?>
                            <textarea name="ai_knowledge_links" id="ai_knowledge_links" rows="6" class="large-text" placeholder="https://example.com/page1&#10;https://example.com/page2&#10;https://example.com/page3"><?php echo esc_textarea($knowledge_links); ?></textarea>
                            <p class="description">
                                <?php _e('ใส่ URL ของหน้าเว็บที่ต้องการให้ AI ใช้อ้างอิง (แยกด้วยบรรทัดใหม่)', 'thaiprompt-mlm'); ?><br>
                                <strong><?php _e('Note:', 'thaiprompt-mlm'); ?></strong> <?php _e('AI will be instructed to answer based on these links', 'thaiprompt-mlm'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Custom Knowledge Base -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ai_knowledge_custom"><?php _e('Custom Knowledge Base', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $knowledge_custom = get_option('thaiprompt_mlm_ai_knowledge_custom', '');
                            ?>
                            <textarea name="ai_knowledge_custom" id="ai_knowledge_custom" rows="10" class="large-text" placeholder="<?php _e('Enter custom information, FAQs, product details, or any data you want AI to know...', 'thaiprompt-mlm'); ?>"><?php echo esc_textarea($knowledge_custom); ?></textarea>
                            <p class="description">
                                <?php _e('ใส่ข้อมูลเพิ่มเติมที่ต้องการให้ AI รู้และใช้ในการตอบคำถาม (FAQ, รายละเอียดสินค้า, ข้อมูลบริษัท ฯลฯ)', 'thaiprompt-mlm'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Response Boundaries -->
                    <tr class="ai-settings" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ai_response_mode"><?php _e('Response Mode', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <?php
                            $response_mode = get_option('thaiprompt_mlm_ai_response_mode', 'flexible');
                            ?>
                            <select name="ai_response_mode" id="ai_response_mode" class="regular-text">
                                <option value="flexible" <?php selected($response_mode, 'flexible'); ?>>
                                    <?php _e('Flexible - Use all available knowledge', 'thaiprompt-mlm'); ?>
                                </option>
                                <option value="strict" <?php selected($response_mode, 'strict'); ?>>
                                    <?php _e('Strict - Only answer from configured sources', 'thaiprompt-mlm'); ?>
                                </option>
                                <option value="moderate" <?php selected($response_mode, 'moderate'); ?>>
                                    <?php _e('Moderate - Prefer configured sources, supplement with general knowledge', 'thaiprompt-mlm'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('กำหนดว่า AI ควรตอบคำถามอย่างไร:', 'thaiprompt-mlm'); ?><br>
                                <strong><?php _e('Flexible:', 'thaiprompt-mlm'); ?></strong> <?php _e('ใช้ความรู้ทั้งหมดที่มี', 'thaiprompt-mlm'); ?><br>
                                <strong><?php _e('Strict:', 'thaiprompt-mlm'); ?></strong> <?php _e('ตอบเฉพาะจากข้อมูลที่กำหนดเท่านั้น', 'thaiprompt-mlm'); ?><br>
                                <strong><?php _e('Moderate:', 'thaiprompt-mlm'); ?></strong> <?php _e('ใช้ข้อมูลที่กำหนดเป็นหลัก แต่สามารถเสริมด้วยความรู้ทั่วไปได้', 'thaiprompt-mlm'); ?>
                            </p>
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

<script>
jQuery(document).ready(function($) {
    // AI Provider selection
    $('#ai_provider').on('change', function() {
        var provider = $(this).val();

        // Hide all AI settings
        $('.ai-settings').hide();

        // Show relevant settings
        if (provider !== 'none') {
            $('.ai-settings:not([class*="-settings"])').show(); // Show system prompt
            $('.' + provider + '-settings').show(); // Show provider-specific settings
        }
    });
});
</script>
