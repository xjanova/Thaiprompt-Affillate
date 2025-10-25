<?php
/**
 * Flex Message Builder
 *
 * Visual Flex Message creation and testing tool
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
$message_json = '';
$preview_mode = false;

if (isset($_POST['generate_flex_message']) && check_admin_referer('thaiprompt_mlm_flex_builder')) {
    $template_id = sanitize_text_field($_POST['template']);
    $data = array();

    // Get template to know which fields to collect
    $template = Thaiprompt_MLM_Flex_Message_Templates::get_template($template_id);

    if ($template && isset($template['fields'])) {
        foreach ($template['fields'] as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize_text_field($_POST[$field]);
            }
        }
    }

    // Generate Flex Message
    $flex_message = Thaiprompt_MLM_Flex_Message_Templates::generate($template_id, $data);

    if (!is_wp_error($flex_message)) {
        $message_json = json_encode($flex_message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $preview_mode = true;

        // Save to option for testing
        update_option('thaiprompt_mlm_last_flex_message', $flex_message);

        echo '<div class="notice notice-success"><p>✅ Flex Message generated successfully!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>❌ Error: ' . $flex_message->get_error_message() . '</p></div>';
    }
}

// Handle send test
if (isset($_POST['send_test_message']) && check_admin_referer('thaiprompt_mlm_send_test')) {
    $test_user_id = sanitize_text_field($_POST['test_line_user_id']);
    $flex_message = get_option('thaiprompt_mlm_last_flex_message');

    if ($flex_message && $test_user_id) {
        $result = Thaiprompt_MLM_LINE_Bot::push_message($test_user_id, array(
            array(
                'type' => 'flex',
                'altText' => 'Flex Message',
                'contents' => $flex_message
            )
        ));

        if (!is_wp_error($result)) {
            echo '<div class="notice notice-success"><p>✅ Test message sent successfully!</p></div>';
            Thaiprompt_MLM_Logger::info('Test Flex Message sent', array('user_id' => $test_user_id));
        } else {
            echo '<div class="notice notice-error"><p>❌ Failed to send: ' . $result->get_error_message() . '</p></div>';
        }
    }
}

$templates = Thaiprompt_MLM_Flex_Message_Templates::get_templates();
?>

<div style="max-width: 1400px;">
    <h1 style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 32px;">💬</span>
        <?php _e('Flex Message Builder', 'thaiprompt-mlm'); ?>
    </h1>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">

        <!-- Left: Builder -->
        <div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2>🎨 <?php _e('Build Your Message', 'thaiprompt-mlm'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <form method="post" id="flex-builder-form">
                        <?php wp_nonce_field('thaiprompt_mlm_flex_builder'); ?>

                        <!-- Template Selection -->
                        <div style="margin-bottom: 30px;">
                            <h3><?php _e('1. Choose Template', 'thaiprompt-mlm'); ?></h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                                <?php foreach ($templates as $id => $template): ?>
                                <label class="template-option">
                                    <input type="radio" name="template" value="<?php echo esc_attr($id); ?>" required <?php echo $id === 'product_card' ? 'checked' : ''; ?>>
                                    <div class="template-card-preview">
                                        <img src="<?php echo esc_url($template['preview_image']); ?>" alt="<?php echo esc_attr($template['name']); ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                                        <strong style="display: block; margin-bottom: 5px;"><?php echo esc_html($template['name']); ?></strong>
                                        <small style="color: #666;"><?php echo esc_html($template['description']); ?></small>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Dynamic Fields -->
                        <div style="margin-bottom: 30px;">
                            <h3><?php _e('2. Fill in Content', 'thaiprompt-mlm'); ?></h3>

                            <?php foreach ($templates as $id => $template): ?>
                            <div class="template-fields" data-template="<?php echo esc_attr($id); ?>" style="display: none;">
                                <table class="form-table">
                                    <?php foreach ($template['fields'] as $field): ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($field); ?>">
                                                <?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <?php if (strpos($field, 'url') !== false): ?>
                                                <input type="url"
                                                       name="<?php echo esc_attr($field); ?>"
                                                       id="<?php echo esc_attr($field); ?>"
                                                       class="regular-text"
                                                       placeholder="https://example.com">
                                            <?php elseif (strpos($field, 'description') !== false || strpos($field, 'message') !== false): ?>
                                                <textarea name="<?php echo esc_attr($field); ?>"
                                                          id="<?php echo esc_attr($field); ?>"
                                                          rows="3"
                                                          class="large-text"
                                                          placeholder="<?php echo esc_attr(ucwords(str_replace('_', ' ', $field))); ?>"></textarea>
                                            <?php else: ?>
                                                <input type="text"
                                                       name="<?php echo esc_attr($field); ?>"
                                                       id="<?php echo esc_attr($field); ?>"
                                                       class="regular-text"
                                                       placeholder="<?php echo esc_attr(ucwords(str_replace('_', ' ', $field))); ?>">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="submit">
                            <button type="submit" name="generate_flex_message" class="button button-primary button-large">
                                ✨ <?php _e('Generate Message', 'thaiprompt-mlm'); ?>
                            </button>
                        </p>
                    </form>
                </div>
            </div>

            <!-- JSON Output -->
            <?php if ($preview_mode && $message_json): ?>
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>📝 <?php _e('Generated JSON', 'thaiprompt-mlm'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <div style="position: relative;">
                        <button type="button" id="copy-json" class="button button-secondary" style="position: absolute; top: 10px; right: 10px;">
                            📋 <?php _e('Copy', 'thaiprompt-mlm'); ?>
                        </button>
                        <pre id="json-output" style="background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; max-height: 400px; font-size: 12px; line-height: 1.5;"><?php echo esc_html($message_json); ?></pre>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Preview & Test -->
        <div>
            <!-- LINE Simulator -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📱 <?php _e('LINE Preview', 'thaiprompt-mlm'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <div id="line-preview" style="background: #06C755; padding: 20px; border-radius: 16px;">
                        <div style="background: #fff; border-radius: 12px; padding: 20px; min-height: 400px; display: flex; align-items: center; justify-content: center;">
                            <?php if ($preview_mode): ?>
                                <iframe id="flex-preview-iframe"
                                        style="width: 100%; height: 500px; border: none; border-radius: 8px;"
                                        srcdoc='<!DOCTYPE html>
                                        <html>
                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <style>
                                                body { margin: 0; padding: 10px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f0f0f0; }
                                                .flex-message { max-width: 300px; margin: 0 auto; }
                                            </style>
                                        </head>
                                        <body>
                                            <div class="flex-message">
                                                <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 10px;">Preview</p>
                                                <div style="background: #fff; border-radius: 12px; padding: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    <p style="padding: 15px; margin: 0; text-align: center; color: #666; font-size: 13px;">
                                                        Flex Message preview will appear here when generated.<br>
                                                        For full preview, use LINE Flex Message Simulator:
                                                        <a href="https://developers.line.biz/flex-simulator/" target="_blank" style="color: #06C755;">Open Simulator</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </body>
                                        </html>'></iframe>
                            <?php else: ?>
                                <div style="text-align: center; color: #666;">
                                    <div style="font-size: 48px; margin-bottom: 15px;">💬</div>
                                    <p><?php _e('Generate a message to see preview', 'thaiprompt-mlm'); ?></p>
                                    <p style="font-size: 13px; margin-top: 10px;">
                                        <a href="https://developers.line.biz/flex-simulator/" target="_blank" style="color: #06C755;">
                                            <?php _e('Use LINE Flex Simulator for full preview →', 'thaiprompt-mlm'); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send Test -->
            <?php if ($preview_mode): ?>
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>🧪 <?php _e('Send Test Message', 'thaiprompt-mlm'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <form method="post">
                        <?php wp_nonce_field('thaiprompt_mlm_send_test'); ?>

                        <p>
                            <label for="test_line_user_id">
                                <strong><?php _e('LINE User ID:', 'thaiprompt-mlm'); ?></strong>
                            </label>
                        </p>
                        <input type="text"
                               name="test_line_user_id"
                               id="test_line_user_id"
                               class="regular-text"
                               placeholder="Uxxxxxxxxxxxxxxxxxxx"
                               required>
                        <p class="description">
                            <?php _e('Enter LINE User ID to send test message. Find your User ID by sending any message to the bot and checking the webhook logs.', 'thaiprompt-mlm'); ?>
                        </p>

                        <p class="submit">
                            <button type="submit" name="send_test_message" class="button button-secondary">
                                📨 <?php _e('Send Test', 'thaiprompt-mlm'); ?>
                            </button>
                        </p>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Guide -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>📚 <?php _e('How to Use', 'thaiprompt-mlm'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <ol style="line-height: 2;">
                        <li><?php _e('Choose a template that fits your needs', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Fill in the content fields', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Click "Generate Message" to create JSON', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Send test message to verify appearance', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Copy JSON to use in your bot code', 'thaiprompt-mlm'); ?></li>
                    </ol>

                    <div style="background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-top: 15px;">
                        <strong>💡 <?php _e('Tips:', 'thaiprompt-mlm'); ?></strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <li><?php _e('Use high-quality images (recommended: 1024x1024px)', 'thaiprompt-mlm'); ?></li>
                            <li><?php _e('Keep text concise for better readability', 'thaiprompt-mlm'); ?></li>
                            <li><?php _e('Test on actual LINE app for best results', 'thaiprompt-mlm'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-option {
    cursor: pointer;
    display: block;
}

.template-option input[type="radio"] {
    display: none;
}

.template-card-preview {
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-align: center;
}

.template-option:hover .template-card-preview {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.template-option input[type="radio"]:checked + .template-card-preview {
    border-color: #667eea;
    background: #f5f7ff;
}

.template-fields {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Show fields for selected template
    function updateFields() {
        var selectedTemplate = $('input[name="template"]:checked').val();
        $('.template-fields').hide();
        $('.template-fields[data-template="' + selectedTemplate + '"]').show();
    }

    $('input[name="template"]').on('change', updateFields);
    updateFields(); // Initial load

    // Copy JSON
    $('#copy-json').on('click', function() {
        var jsonText = $('#json-output').text();
        navigator.clipboard.writeText(jsonText).then(function() {
            var $btn = $('#copy-json');
            var originalText = $btn.text();
            $btn.text('✅ Copied!');
            setTimeout(function() {
                $btn.text(originalText);
            }, 2000);
        });
    });
});
</script>
