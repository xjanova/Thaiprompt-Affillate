<?php
/**
 * Admin AI Configuration Page
 *
 * Complete AI integration settings for LINE chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['save_ai_settings']) && check_admin_referer('thaiprompt_mlm_ai_settings')) {
    // Save AI provider settings
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

    echo '<div class="notice notice-success"><p>✅ AI settings saved successfully!</p></div>';

    Thaiprompt_MLM_Logger::info('AI settings updated');
}

// Test AI connection
if (isset($_POST['test_ai_connection']) && check_admin_referer('thaiprompt_mlm_ai_test')) {
    $ai_handler = new Thaiprompt_MLM_AI_Handler();
    $test_result = $ai_handler->test_connection();

    if (is_wp_error($test_result)) {
        echo '<div class="notice notice-error"><p>❌ AI Connection test failed: ' . esc_html($test_result->get_error_message()) . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>✅ AI Connection successful! Response: ' . esc_html($test_result) . '</p></div>';
    }
}

// Get current AI settings
$ai_provider = get_option('thaiprompt_mlm_ai_provider', 'none');
$providers = Thaiprompt_MLM_AI_Handler::get_providers();
?>

<div class="wrap">
    <h1 style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 32px;">🤖</span>
        <?php _e('AI Configuration', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Quick Status -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card" style="<?php echo $ai_provider !== 'none' ? 'border-left: 4px solid #10b981;' : 'border-left: 4px solid #ef4444;'; ?>">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('AI Provider', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">🤖</span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php echo $ai_provider !== 'none' ? esc_html($providers[$ai_provider]) : 'Disabled'; ?>
            </div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Knowledge Sources', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📚</span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php
                $knowledge_sources = get_option('thaiprompt_mlm_ai_knowledge_sources', array('general'));
                echo is_array($knowledge_sources) ? count($knowledge_sources) . ' Active' : '0 Active';
                ?>
            </div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Response Mode', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⚙️</span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php
                $response_mode = get_option('thaiprompt_mlm_ai_response_mode', 'flexible');
                echo ucfirst($response_mode);
                ?>
            </div>
        </div>
    </div>

    <!-- Setup Guide (Collapsible) -->
    <div class="postbox" style="margin: 20px 0;">
        <div class="postbox-header" style="cursor: pointer;" onclick="jQuery('#ai-setup-guide').slideToggle();">
            <h2 style="margin: 0; padding: 10px 15px; display: flex; align-items: center; gap: 10px;">
                📚 <?php _e('AI Setup Guide', 'thaiprompt-mlm'); ?>
                <span style="font-size: 12px; color: #666; font-weight: normal;">(Click to expand)</span>
            </h2>
        </div>
        <div id="ai-setup-guide" class="inside" style="display: none; padding: 20px;">
            <div style="background: #f0f6fc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🎯 ChatGPT (OpenAI)</h3>
                <ol style="line-height: 2;">
                    <li>ไปที่ <a href="https://platform.openai.com/api-keys" target="_blank"><strong>OpenAI Platform</strong></a></li>
                    <li>สร้างบัญชีหรือ Login เข้าสู่ระบบ</li>
                    <li>คลิก <strong>"Create new secret key"</strong></li>
                    <li>ตั้งชื่อ API Key และคัดลอกเก็บไว้ (จะแสดงครั้งเดียว)</li>
                    <li>นำ API Key มาวางในช่อง ChatGPT API Key</li>
                    <li>เลือก Model ที่ต้องการใช้งาน (แนะนำ GPT-4o Mini)</li>
                </ol>
                <p style="margin: 10px 0 0; color: #0066cc;">
                    <strong>💰 ราคา:</strong> Pay-per-use, GPT-4o Mini ราคาถูกที่สุด
                </p>
            </div>

            <div style="background: #fff8e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">🎯 Google Gemini</h3>
                <ol style="line-height: 2;">
                    <li>ไปที่ <a href="https://aistudio.google.com/app/apikey" target="_blank"><strong>Google AI Studio</strong></a></li>
                    <li>Login ด้วย Google Account</li>
                    <li>คลิก <strong>"Create API Key"</strong></li>
                    <li>เลือก Google Cloud Project หรือสร้างใหม่</li>
                    <li>คัดลอก API Key ที่ได้</li>
                    <li>นำมาวางในช่อง Gemini API Key</li>
                    <li>เลือก Model (แนะนำ Gemini 2.0 Flash)</li>
                </ol>
                <p style="margin: 10px 0 0; color: #ea8600;">
                    <strong>💰 ราคา:</strong> มี Free tier ให้ใช้งาน, เหมาะสำหรับทดลอง
                </p>
            </div>

            <div style="background: #e7f3ff; padding: 20px; border-radius: 8px;">
                <h3 style="margin-top: 0;">🎯 DeepSeek</h3>
                <ol style="line-height: 2;">
                    <li>ไปที่ <a href="https://platform.deepseek.com/api_keys" target="_blank"><strong>DeepSeek Platform</strong></a></li>
                    <li>สร้างบัญชีหรือ Login</li>
                    <li>ไปที่หน้า API Keys</li>
                    <li>คลิก <strong>"Create API Key"</strong></li>
                    <li>คัดลอก API Key</li>
                    <li>นำมาวางในช่อง DeepSeek API Key</li>
                </ol>
                <p style="margin: 10px 0 0; color: #0066cc;">
                    <strong>💰 ราคา:</strong> ราคาถูกมาก, เหมาะสำหรับการใช้งานเยอะ
                </p>
            </div>
        </div>
    </div>

    <!-- AI Settings Form -->
    <form method="post" action="">
        <?php wp_nonce_field('thaiprompt_mlm_ai_settings'); ?>

        <div class="postbox">
            <div class="postbox-header">
                <h2>🤖 <?php _e('AI Provider Settings', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">

                <table class="form-table">
                    <!-- AI Provider Selection -->
                    <tr>
                        <th scope="row">
                            <label for="ai_provider"><?php _e('AI Provider', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <select name="ai_provider" id="ai_provider" class="regular-text">
                                <?php foreach ($providers as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($ai_provider, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('เลือก AI Provider ที่จะใช้ในการตอบกลับข้อความอัตโนมัติ', 'thaiprompt-mlm'); ?></p>
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
                </table>

            </div>
        </div>

        <!-- AI Knowledge Sources -->
        <div class="postbox ai-knowledge-section" style="<?php echo $ai_provider === 'none' ? 'display:none;' : ''; ?>">
            <div class="postbox-header">
                <h2>📚 <?php _e('AI Knowledge Sources', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <p style="color: #666; margin-top: 0;">
                    <?php _e('กำหนดแหล่งข้อมูลที่ AI จะใช้ในการตอบคำถาม (สามารถเลือกได้หลายแหล่ง)', 'thaiprompt-mlm'); ?>
                </p>

                <table class="form-table">
                    <!-- Enable Knowledge Sources -->
                    <tr>
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
                    <tr>
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
                    <tr>
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
                    <tr>
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

                    <!-- Response Mode -->
                    <tr>
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

            </div>
        </div>

        <p class="submit">
            <button type="submit" name="save_ai_settings" class="button button-primary button-large">
                💾 <?php _e('Save AI Settings', 'thaiprompt-mlm'); ?>
            </button>
        </p>
    </form>

    <!-- Test AI Connection -->
    <?php if ($ai_provider !== 'none'): ?>
    <form method="post" action="" style="margin-top: 20px;">
        <?php wp_nonce_field('thaiprompt_mlm_ai_test'); ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2>🧪 <?php _e('Test AI Connection', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <p><?php _e('ทดสอบการเชื่อมต่อกับ AI Provider ที่เลือก', 'thaiprompt-mlm'); ?></p>
                <button type="submit" name="test_ai_connection" class="button button-secondary">
                    🔍 <?php _e('Test AI Connection', 'thaiprompt-mlm'); ?>
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
        $('.ai-knowledge-section').hide();

        // Show relevant settings
        if (provider !== 'none') {
            $('.ai-settings:not([class*="-settings"])').show(); // Show system prompt
            $('.' + provider + '-settings').show(); // Show provider-specific settings
            $('.ai-knowledge-section').show(); // Show knowledge sources section
        }
    });
});
</script>
