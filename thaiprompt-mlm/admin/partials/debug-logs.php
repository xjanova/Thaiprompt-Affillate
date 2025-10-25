<?php
/**
 * Admin Debug Logs View
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get log files
$log_files = Thaiprompt_MLM_Logger::get_log_files();
$stats = Thaiprompt_MLM_Logger::get_log_stats();

// Get selected log file or use the current one
$selected_log = isset($_GET['log_file']) ? sanitize_text_field($_GET['log_file']) : Thaiprompt_MLM_Logger::get_log_file();

// Read log content
$log_content = '';
if (file_exists($selected_log)) {
    $log_content = Thaiprompt_MLM_Logger::read_log($selected_log, 1000);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        🐛 <?php _e('Debug Logs', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Statistics -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Log Files', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📁</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats['total_files']); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Size', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">💾</span>
            </div>
            <div class="mlm-card-value"><?php echo esc_html($stats['total_size_formatted']); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Errors', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⚠️</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats['total_errors']); ?></div>
        </div>
    </div>

    <!-- Log Viewer Controls -->
    <div class="postbox" style="margin: 20px 0;">
        <div class="inside" style="padding: 20px;">
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: space-between;">
                <!-- Log File Selector -->
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <label for="log-file-select" style="font-weight: 600;">
                        <?php _e('Select Log File:', 'thaiprompt-mlm'); ?>
                    </label>
                    <select id="log-file-select" style="min-width: 300px;">
                        <?php foreach ($log_files as $log_file): ?>
                            <option value="<?php echo esc_attr($log_file); ?>" <?php selected($log_file, $selected_log); ?>>
                                <?php
                                $file_name = basename($log_file);
                                $file_size = size_format(filesize($log_file));
                                $file_date = date('Y-m-d H:i:s', filemtime($log_file));
                                echo esc_html($file_name . ' (' . $file_size . ' - ' . $file_date . ')');
                                ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($log_files)): ?>
                            <option value=""><?php _e('No log files found', 'thaiprompt-mlm'); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="btn-refresh-log" class="button">
                        🔄 <?php _e('Refresh', 'thaiprompt-mlm'); ?>
                    </button>
                    <button type="button" id="btn-download-log" class="button button-primary" <?php echo empty($log_files) ? 'disabled' : ''; ?>>
                        💾 <?php _e('Download', 'thaiprompt-mlm'); ?>
                    </button>
                    <button type="button" id="btn-clear-log" class="button" <?php echo empty($log_files) ? 'disabled' : ''; ?>>
                        🗑️ <?php _e('Clear This Log', 'thaiprompt-mlm'); ?>
                    </button>
                    <button type="button" id="btn-clear-all-logs" class="button" style="color: #dc3232;" <?php echo empty($log_files) ? 'disabled' : ''; ?>>
                        🗑️ <?php _e('Clear All Logs', 'thaiprompt-mlm'); ?>
                    </button>
                </div>
            </div>

            <!-- Instructions -->
            <div style="margin-top: 15px; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                <p style="margin: 0;">
                    <strong><?php _e('How to share logs:', 'thaiprompt-mlm'); ?></strong><br>
                    <?php _e('1. Click "Download" to save the log file to your computer', 'thaiprompt-mlm'); ?><br>
                    <?php _e('2. Send the downloaded file to support for debugging', 'thaiprompt-mlm'); ?><br>
                    <?php _e('3. Log files are protected and cannot be accessed directly via browser', 'thaiprompt-mlm'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Log Content Viewer -->
    <div class="postbox" style="margin: 20px 0;">
        <div class="inside" style="padding: 0;">
            <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    📝 <?php _e('Log Content (Last 1000 lines)', 'thaiprompt-mlm'); ?>
                </h3>
            </div>
            <div style="background: #1e1e1e; padding: 20px; overflow: auto; max-height: 600px;">
                <?php if (!empty($log_content)): ?>
                    <pre style="color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.5; margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html($log_content); ?></pre>
                <?php else: ?>
                    <p style="color: #888; text-align: center; padding: 40px;">
                        <?php _e('No log entries found. The log file is empty or does not exist.', 'thaiprompt-mlm'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Log Legend -->
    <div class="postbox" style="margin: 20px 0;">
        <div class="inside" style="padding: 20px;">
            <h3 style="margin-top: 0;"><?php _e('Log Level Legend', 'thaiprompt-mlm'); ?></h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa; border-radius: 4px;">
                    <strong style="color: #0073aa;">DEBUG</strong><br>
                    <small><?php _e('Detailed debug information', 'thaiprompt-mlm'); ?></small>
                </div>
                <div style="padding: 10px; background: #f0f9ff; border-left: 4px solid #00a0d2; border-radius: 4px;">
                    <strong style="color: #00a0d2;">INFO</strong><br>
                    <small><?php _e('Informational messages', 'thaiprompt-mlm'); ?></small>
                </div>
                <div style="padding: 10px; background: #fff8e1; border-left: 4px solid #ffb900; border-radius: 4px;">
                    <strong style="color: #ffb900;">WARNING</strong><br>
                    <small><?php _e('Warning messages', 'thaiprompt-mlm'); ?></small>
                </div>
                <div style="padding: 10px; background: #ffebee; border-left: 4px solid #dc3232; border-radius: 4px;">
                    <strong style="color: #dc3232;">ERROR</strong><br>
                    <small><?php _e('Error messages', 'thaiprompt-mlm'); ?></small>
                </div>
                <div style="padding: 10px; background: #fce4ec; border-left: 4px solid #c62828; border-radius: 4px;">
                    <strong style="color: #c62828;">CRITICAL</strong><br>
                    <small><?php _e('Critical errors', 'thaiprompt-mlm'); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Change log file
    $('#log-file-select').on('change', function() {
        const logFile = $(this).val();
        if (logFile) {
            window.location.href = '?page=thaiprompt-mlm-debug-logs&log_file=' + encodeURIComponent(logFile);
        }
    });

    // Refresh log
    $('#btn-refresh-log').on('click', function() {
        location.reload();
    });

    // Download log
    $('#btn-download-log').on('click', function() {
        const logFile = $('#log-file-select').val();
        if (logFile) {
            const downloadUrl = ajaxurl + '?action=thaiprompt_mlm_download_log&nonce=' + thaipromptMLM.nonce + '&log_file=' + encodeURIComponent(logFile);
            window.location.href = downloadUrl;
        }
    });

    // Clear log
    $('#btn-clear-log').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to clear this log file?', 'thaiprompt-mlm'); ?>')) {
            return;
        }

        const logFile = $('#log-file-select').val();
        const $btn = $(this);
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('<?php _e('Clearing...', 'thaiprompt-mlm'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_clear_log',
                nonce: thaipromptMLM.nonce,
                log_file: logFile
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'thaiprompt-mlm'); ?>');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Clear all logs
    $('#btn-clear-all-logs').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to clear ALL log files? This cannot be undone!', 'thaiprompt-mlm'); ?>')) {
            return;
        }

        const $btn = $(this);
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('<?php _e('Clearing...', 'thaiprompt-mlm'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_clear_all_logs',
                nonce: thaipromptMLM.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'thaiprompt-mlm'); ?>');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
