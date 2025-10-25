<?php
/**
 * Admin Wallet & Withdrawals View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$withdrawals_table = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';

// Status filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'pending';

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Query
$where = $status_filter ? $wpdb->prepare("status = %s", $status_filter) : '1=1';
$total = $wpdb->get_var("SELECT COUNT(*) FROM $withdrawals_table WHERE $where");
$withdrawals = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $withdrawals_table WHERE $where ORDER BY requested_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_pages = ceil($total / $per_page);

// Statistics
$stats = $wpdb->get_row("
    SELECT
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as rejected
    FROM $withdrawals_table
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Wallet & Withdrawals', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Stats -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Pending Withdrawals', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⏳</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->pending ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Completed', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">✅</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->completed ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Rejected', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">❌</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->rejected ?? 0); ?></div>
        </div>
    </div>

    <!-- Main Tabs -->
    <?php $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'withdrawals'; ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=thaiprompt-mlm-wallet&tab=withdrawals" class="nav-tab <?php echo $current_tab === 'withdrawals' ? 'nav-tab-active' : ''; ?>">
            💸 <?php _e('Withdrawal Requests', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&tab=manage" class="nav-tab <?php echo $current_tab === 'manage' ? 'nav-tab-active' : ''; ?>">
            ⚙️ <?php _e('Manage Wallets', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&tab=scheduled" class="nav-tab <?php echo $current_tab === 'scheduled' ? 'nav-tab-active' : ''; ?>">
            ⏰ <?php _e('Scheduled Transfers', 'thaiprompt-mlm'); ?>
        </a>
    </h2>

    <?php if ($current_tab === 'withdrawals'): ?>
    <!-- Status Sub-Tabs -->
    <h3 class="nav-tab-wrapper" style="margin-top: 20px;">
        <a href="?page=thaiprompt-mlm-wallet&tab=withdrawals&status=pending" class="nav-tab <?php echo $status_filter === 'pending' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Pending', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&tab=withdrawals&status=completed" class="nav-tab <?php echo $status_filter === 'completed' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Completed', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&tab=withdrawals&status=rejected" class="nav-tab <?php echo $status_filter === 'rejected' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Rejected', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&tab=withdrawals&status=" class="nav-tab <?php echo $status_filter === '' ? 'nav-tab-active' : ''; ?>">
            <?php _e('All', 'thaiprompt-mlm'); ?>
        </a>
    </h3>

    <!-- Withdrawals Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="5%"><?php _e('ID', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Method', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Details', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Requested', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Processed', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($withdrawals): ?>
                <?php foreach ($withdrawals as $withdrawal): ?>
                    <?php
                    $user = get_userdata($withdrawal->user_id);
                    $details = json_decode($withdrawal->details, true);
                    ?>
                    <tr>
                        <td><?php echo $withdrawal->id; ?></td>
                        <td>
                            <?php if ($user): ?>
                                <?php echo get_avatar($user->ID, 32, '', '', array('class' => 'mlm-user-avatar')); ?>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo wc_price($withdrawal->amount); ?></strong></td>
                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $withdrawal->method))); ?></td>
                        <td>
                            <?php if ($details && is_array($details)): ?>
                                <small>
                                    <?php foreach ($details as $key => $value): ?>
                                        <strong><?php echo esc_html($key); ?>:</strong> <?php echo esc_html($value); ?><br>
                                    <?php endforeach; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="mlm-status-badge <?php echo esc_attr($withdrawal->status); ?>">
                                <?php echo esc_html(ucfirst($withdrawal->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($withdrawal->requested_at)); ?>
                        </td>
                        <td>
                            <?php if ($withdrawal->processed_at): ?>
                                <?php echo date_i18n(get_option('date_format'), strtotime($withdrawal->processed_at)); ?>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($withdrawal->status === 'pending'): ?>
                                <button class="button button-primary button-small mlm-approve-withdrawal"
                                    data-withdrawal-id="<?php echo $withdrawal->id; ?>"
                                    data-user-id="<?php echo $withdrawal->user_id; ?>"
                                    data-amount="<?php echo $withdrawal->amount; ?>"
                                    data-user-name="<?php echo esc_attr($user ? $user->display_name : ''); ?>">
                                    <?php _e('Approve & Send', 'thaiprompt-mlm'); ?>
                                </button>
                                <button class="button button-small mlm-reject-withdrawal"
                                    data-withdrawal-id="<?php echo $withdrawal->id; ?>"
                                    data-user-name="<?php echo esc_attr($user ? $user->display_name : ''); ?>">
                                    <?php _e('Reject', 'thaiprompt-mlm'); ?>
                                </button>
                            <?php else: ?>
                                <?php if ($withdrawal->slip_attachment_id): ?>
                                    <button class="button button-small" onclick="window.open('<?php echo wp_get_attachment_url($withdrawal->slip_attachment_id); ?>', '_blank')">
                                        <?php _e('View Slip', 'thaiprompt-mlm'); ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ($withdrawal->notes): ?>
                                    <button class="button button-small" onclick="alert('<?php echo esc_js($withdrawal->notes); ?>')">
                                        <?php _e('View Notes', 'thaiprompt-mlm'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        <strong><?php _e('No withdrawal requests found', 'thaiprompt-mlm'); ?></strong>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">
                <?php printf(__('%s items', 'thaiprompt-mlm'), number_format($total)); ?>
            </span>
            <span class="pagination-links">
                <?php
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page,
                    'type' => 'plain'
                ));
                echo $page_links;
                ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($current_tab === 'manage'): ?>
    <!-- Manage Wallets Tab -->
    <div style="margin-top: 20px;">
        <!-- Search User -->
        <div class="postbox">
            <div class="postbox-header">
                <h2>🔍 <?php _e('Search Member', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" id="mlm-search-user" class="regular-text" placeholder="<?php _e('Enter username, email, or referral code...', 'thaiprompt-mlm'); ?>">
                    <button type="button" id="mlm-search-user-btn" class="button button-primary">
                        🔍 <?php _e('Search', 'thaiprompt-mlm'); ?>
                    </button>
                </div>
                <div id="mlm-user-search-results" style="margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Quick Transfer -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2>💸 <?php _e('Quick Transfer (Admin)', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <form id="mlm-admin-transfer-form">
                    <table class="form-table">
                        <tr>
                            <th><label><?php _e('From User', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <input type="text" id="admin-transfer-from" class="regular-text" placeholder="<?php _e('Username or User ID', 'thaiprompt-mlm'); ?>" required>
                                <p class="description"><?php _e('Leave empty for system credit (no sender)', 'thaiprompt-mlm'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('To User', 'thaiprompt-mlm'); ?>: <span style="color:red;">*</span></label></th>
                            <td>
                                <input type="text" id="admin-transfer-to" class="regular-text" placeholder="<?php _e('Username or User ID', 'thaiprompt-mlm'); ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Amount', 'thaiprompt-mlm'); ?>: <span style="color:red;">*</span></label></th>
                            <td>
                                <input type="number" id="admin-transfer-amount" step="0.01" min="0.01" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Note', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <textarea id="admin-transfer-note" class="large-text" rows="3" placeholder="<?php _e('Reason for transfer...', 'thaiprompt-mlm'); ?>"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Transaction Type', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <select id="admin-transfer-type" class="regular-text">
                                    <option value="add"><?php _e('➕ Add Funds (Credit)', 'thaiprompt-mlm'); ?></option>
                                    <option value="deduct"><?php _e('➖ Deduct Funds (Debit)', 'thaiprompt-mlm'); ?></option>
                                    <option value="transfer"><?php _e('💸 Transfer Between Users', 'thaiprompt-mlm'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            ✅ <?php _e('Execute Transaction', 'thaiprompt-mlm'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <?php elseif ($current_tab === 'scheduled'): ?>
    <!-- Scheduled Transfers Tab -->
    <div style="margin-top: 20px;">
        <div class="postbox">
            <div class="postbox-header">
                <h2>⏰ <?php _e('Schedule New Transfer', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <form id="mlm-schedule-transfer-form">
                    <table class="form-table">
                        <tr>
                            <th><label><?php _e('From User', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <input type="text" id="schedule-from" class="regular-text" placeholder="<?php _e('Username or User ID (empty for system)', 'thaiprompt-mlm'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('To User', 'thaiprompt-mlm'); ?>: <span style="color:red;">*</span></label></th>
                            <td>
                                <input type="text" id="schedule-to" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Amount', 'thaiprompt-mlm'); ?>: <span style="color:red;">*</span></label></th>
                            <td>
                                <input type="number" id="schedule-amount" step="0.01" min="0.01" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Schedule Date & Time', 'thaiprompt-mlm'); ?>: <span style="color:red;">*</span></label></th>
                            <td>
                                <input type="datetime-local" id="schedule-datetime" class="regular-text" required>
                                <p class="description"><?php _e('Transfer will be executed automatically at this time', 'thaiprompt-mlm'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Repeat', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <select id="schedule-repeat" class="regular-text">
                                    <option value="once"><?php _e('Once Only', 'thaiprompt-mlm'); ?></option>
                                    <option value="daily"><?php _e('Daily', 'thaiprompt-mlm'); ?></option>
                                    <option value="weekly"><?php _e('Weekly', 'thaiprompt-mlm'); ?></option>
                                    <option value="monthly"><?php _e('Monthly', 'thaiprompt-mlm'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Note', 'thaiprompt-mlm'); ?>:</label></th>
                            <td>
                                <textarea id="schedule-note" class="large-text" rows="3"></textarea>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            ⏰ <?php _e('Schedule Transfer', 'thaiprompt-mlm'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Scheduled Transfers List -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2>📋 <?php _e('Scheduled Transfers', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <div id="mlm-scheduled-transfers-list">
                    <p style="text-align: center; color: #666;">
                        <em><?php _e('Loading scheduled transfers...', 'thaiprompt-mlm'); ?></em>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Approve Withdrawal Modal -->
<div id="mlm-approve-modal" class="mlm-modal" style="display: none;">
    <div class="mlm-modal-content" style="max-width: 600px;">
        <span class="mlm-modal-close">&times;</span>
        <h2><?php _e('Approve Withdrawal & Send via LINE', 'thaiprompt-mlm'); ?></h2>

        <form id="mlm-approve-form" enctype="multipart/form-data">
            <input type="hidden" id="approve-withdrawal-id" name="withdrawal_id">

            <div class="mlm-form-group">
                <label><?php _e('User', 'thaiprompt-mlm'); ?>:</label>
                <p id="approve-user-name" style="font-weight: bold;"></p>
            </div>

            <div class="mlm-form-group">
                <label><?php _e('Amount', 'thaiprompt-mlm'); ?>:</label>
                <p id="approve-amount" style="font-weight: bold; font-size: 18px; color: #2ecc71;"></p>
            </div>

            <div class="mlm-form-group">
                <label for="slip-upload">
                    <?php _e('Upload Transfer Slip', 'thaiprompt-mlm'); ?>
                    <span style="color: #e74c3c;">*</span>
                </label>
                <input type="file" id="slip-upload" name="slip" accept="image/*" required>
                <p class="description"><?php _e('Required: Upload the transfer slip to send to member via LINE', 'thaiprompt-mlm'); ?></p>

                <div id="slip-preview" style="margin-top: 10px; display: none;">
                    <img id="slip-preview-img" src="" style="max-width: 100%; max-height: 300px; border: 2px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="mlm-form-group">
                <label for="approve-notes"><?php _e('Notes (Optional)', 'thaiprompt-mlm'); ?>:</label>
                <textarea id="approve-notes" name="notes" rows="3" class="widefat" placeholder="<?php _e('Add any notes for the member...', 'thaiprompt-mlm'); ?>"></textarea>
            </div>

            <div class="mlm-modal-actions">
                <button type="submit" class="button button-primary">
                    <?php _e('✅ Approve & Send via LINE', 'thaiprompt-mlm'); ?>
                </button>
                <button type="button" class="button mlm-modal-cancel">
                    <?php _e('Cancel', 'thaiprompt-mlm'); ?>
                </button>
            </div>
        </form>

        <div id="approve-loading" style="display: none; text-align: center; padding: 20px;">
            <div class="spinner is-active" style="float: none; margin: 0 auto;"></div>
            <p><?php _e('Processing and sending notification via LINE...', 'thaiprompt-mlm'); ?></p>
        </div>
    </div>
</div>

<!-- Reject Withdrawal Modal -->
<div id="mlm-reject-modal" class="mlm-modal" style="display: none;">
    <div class="mlm-modal-content" style="max-width: 500px;">
        <span class="mlm-modal-close">&times;</span>
        <h2><?php _e('Reject Withdrawal', 'thaiprompt-mlm'); ?></h2>

        <form id="mlm-reject-form">
            <input type="hidden" id="reject-withdrawal-id" name="withdrawal_id">

            <div class="mlm-form-group">
                <label><?php _e('User', 'thaiprompt-mlm'); ?>:</label>
                <p id="reject-user-name" style="font-weight: bold;"></p>
            </div>

            <div class="mlm-form-group">
                <label for="reject-reason">
                    <?php _e('Reason for Rejection', 'thaiprompt-mlm'); ?>
                    <span style="color: #e74c3c;">*</span>
                </label>
                <textarea id="reject-reason" name="reason" rows="4" class="widefat" required placeholder="<?php _e('Please provide a reason for rejection...', 'thaiprompt-mlm'); ?>"></textarea>
            </div>

            <div class="mlm-modal-actions">
                <button type="submit" class="button button-primary">
                    <?php _e('Reject Withdrawal', 'thaiprompt-mlm'); ?>
                </button>
                <button type="button" class="button mlm-modal-cancel">
                    <?php _e('Cancel', 'thaiprompt-mlm'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.mlm-modal {
    display: none;
    position: fixed;
    z-index: 999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
}

.mlm-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    position: relative;
}

.mlm-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.mlm-modal-close:hover,
.mlm-modal-close:focus {
    color: #000;
}

.mlm-form-group {
    margin-bottom: 20px;
}

.mlm-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.mlm-modal-actions {
    margin-top: 25px;
    text-align: right;
    border-top: 1px solid #ddd;
    padding-top: 20px;
}

.mlm-modal-actions button {
    margin-left: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Approve Modal
    $('.mlm-approve-withdrawal').on('click', function() {
        var withdrawalId = $(this).data('withdrawal-id');
        var userName = $(this).data('user-name');
        var amount = $(this).data('amount');

        $('#approve-withdrawal-id').val(withdrawalId);
        $('#approve-user-name').text(userName);
        $('#approve-amount').text('฿' + parseFloat(amount).toLocaleString('th-TH', {minimumFractionDigits: 2}));
        $('#slip-preview').hide();
        $('#mlm-approve-form')[0].reset();
        $('#mlm-approve-modal').fadeIn();
    });

    // Reject Modal
    $('.mlm-reject-withdrawal').on('click', function() {
        var withdrawalId = $(this).data('withdrawal-id');
        var userName = $(this).data('user-name');

        $('#reject-withdrawal-id').val(withdrawalId);
        $('#reject-user-name').text(userName);
        $('#mlm-reject-form')[0].reset();
        $('#mlm-reject-modal').fadeIn();
    });

    // Close modals
    $('.mlm-modal-close, .mlm-modal-cancel').on('click', function() {
        $(this).closest('.mlm-modal').fadeOut();
    });

    // Close on outside click
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('mlm-modal')) {
            $('.mlm-modal').fadeOut();
        }
    });

    // Slip preview
    $('#slip-upload').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#slip-preview-img').attr('src', e.target.result);
                $('#slip-preview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Approve Form Submit
    $('#mlm-approve-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'mlm_approve_withdrawal');
        formData.append('nonce', '<?php echo wp_create_nonce('mlm_withdrawal_action'); ?>');

        $('#mlm-approve-form').hide();
        $('#approve-loading').show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.data);
                    $('#mlm-approve-form').show();
                    $('#approve-loading').hide();
                }
            },
            error: function() {
                alert('❌ An error occurred. Please try again.');
                $('#mlm-approve-form').show();
                $('#approve-loading').hide();
            }
        });
    });

    // Reject Form Submit
    $('#mlm-reject-form').on('submit', function(e) {
        e.preventDefault();

        if (!confirm('<?php _e('Are you sure you want to reject this withdrawal?', 'thaiprompt-mlm'); ?>')) {
            return;
        }

        var data = {
            action: 'mlm_reject_withdrawal',
            nonce: '<?php echo wp_create_nonce('mlm_withdrawal_action'); ?>',
            withdrawal_id: $('#reject-withdrawal-id').val(),
            reason: $('#reject-reason').val()
        };

        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                location.reload();
            } else {
                alert('❌ Error: ' + response.data);
            }
        });
    });

    // === MANAGE WALLETS TAB ===
    // Search User
    $('#mlm-search-user-btn').on('click', function() {
        const query = $('#mlm-search-user').val().trim();
        if (!query) {
            alert('<?php _e('Please enter a search term', 'thaiprompt-mlm'); ?>');
            return;
        }

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('<?php _e('Searching...', 'thaiprompt-mlm'); ?>');

        $.post(ajaxurl, {
            action: 'mlm_admin_search_user',
            nonce: thaipromptMLM.nonce,
            query: query
        }, function(response) {
            $btn.prop('disabled', false).text(originalText);

            if (response.success && response.data.users) {
                let html = '<div class="mlm-users-grid" style="display: grid; gap: 15px;">';

                response.data.users.forEach(function(user) {
                    html += `
                        <div class="mlm-user-card" style="background: #fff; border: 2px solid #ddd; border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0 0 5px;">${user.display_name}</h3>
                                <p style="margin: 0; color: #666; font-size: 13px;">
                                    <strong>ID:</strong> ${user.ID} |
                                    <strong>Email:</strong> ${user.user_email}<br>
                                    <strong>Balance:</strong> <span style="color: #27ae60; font-size: 18px; font-weight: bold;">${user.balance}</span>
                                </p>
                            </div>
                            <div>
                                <button class="button button-small mlm-set-transfer-user" data-user-id="${user.ID}" data-username="${user.user_login}">
                                    <?php _e('Use in Transfer', 'thaiprompt-mlm'); ?>
                                </button>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                $('#mlm-user-search-results').html(html);
            } else {
                $('#mlm-user-search-results').html('<p style="color: #e74c3c;"><?php _e('No users found', 'thaiprompt-mlm'); ?></p>');
            }
        });
    });

    // Set user in transfer form
    $(document).on('click', '.mlm-set-transfer-user', function() {
        const username = $(this).data('username');
        $('#admin-transfer-to').val(username);
        $('html, body').animate({
            scrollTop: $('#mlm-admin-transfer-form').offset().top - 100
        }, 500);
    });

    // Admin Transfer Form
    $('#mlm-admin-transfer-form').on('submit', function(e) {
        e.preventDefault();

        const type = $('#admin-transfer-type').val();
        const from = $('#admin-transfer-from').val().trim();
        const to = $('#admin-transfer-to').val().trim();
        const amount = parseFloat($('#admin-transfer-amount').val());
        const note = $('#admin-transfer-note').val().trim();

        if (!to || amount <= 0) {
            alert('<?php _e('Please fill in all required fields', 'thaiprompt-mlm'); ?>');
            return;
        }

        if (type === 'transfer' && !from) {
            alert('<?php _e('Sender is required for transfers', 'thaiprompt-mlm'); ?>');
            return;
        }

        if (!confirm('<?php _e('Are you sure you want to execute this transaction?', 'thaiprompt-mlm'); ?>')) {
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).text('<?php _e('Processing...', 'thaiprompt-mlm'); ?>');

        $.post(ajaxurl, {
            action: 'mlm_admin_wallet_operation',
            nonce: thaipromptMLM.nonce,
            type: type,
            from_user: from,
            to_user: to,
            amount: amount,
            note: note
        }, function(response) {
            $submitBtn.prop('disabled', false).text('✅ <?php _e('Execute Transaction', 'thaiprompt-mlm'); ?>');

            if (response.success) {
                alert('✅ ' + response.data.message);
                $('#mlm-admin-transfer-form')[0].reset();
            } else {
                alert('❌ ' + response.data);
            }
        });
    });

    // === SCHEDULED TRANSFERS TAB ===
    // Schedule Transfer Form
    $('#mlm-schedule-transfer-form').on('submit', function(e) {
        e.preventDefault();

        const from = $('#schedule-from').val().trim();
        const to = $('#schedule-to').val().trim();
        const amount = parseFloat($('#schedule-amount').val());
        const datetime = $('#schedule-datetime').val();
        const repeat = $('#schedule-repeat').val();
        const note = $('#schedule-note').val().trim();

        if (!to || amount <= 0 || !datetime) {
            alert('<?php _e('Please fill in all required fields', 'thaiprompt-mlm'); ?>');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).text('<?php _e('Scheduling...', 'thaiprompt-mlm'); ?>');

        $.post(ajaxurl, {
            action: 'mlm_schedule_transfer',
            nonce: thaipromptMLM.nonce,
            from_user: from,
            to_user: to,
            amount: amount,
            schedule_datetime: datetime,
            repeat: repeat,
            note: note
        }, function(response) {
            $submitBtn.prop('disabled', false).text('⏰ <?php _e('Schedule Transfer', 'thaiprompt-mlm'); ?>');

            if (response.success) {
                alert('✅ ' + response.data.message);
                $('#mlm-schedule-transfer-form')[0].reset();
                loadScheduledTransfers();
            } else {
                alert('❌ ' + response.data);
            }
        });
    });

    // Load Scheduled Transfers
    function loadScheduledTransfers() {
        $.post(ajaxurl, {
            action: 'mlm_get_scheduled_transfers',
            nonce: thaipromptMLM.nonce
        }, function(response) {
            if (response.success && response.data.transfers) {
                let html = '<table class="wp-list-table widefat fixed striped"><thead><tr>' +
                    '<th><?php _e('ID', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('From', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('To', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('Scheduled Time', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('Repeat', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('Status', 'thaiprompt-mlm'); ?></th>' +
                    '<th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>' +
                    '</tr></thead><tbody>';

                response.data.transfers.forEach(function(transfer) {
                    html += `
                        <tr>
                            <td>${transfer.id}</td>
                            <td>${transfer.from_user || '<em>System</em>'}</td>
                            <td><strong>${transfer.to_user}</strong></td>
                            <td><strong>${transfer.amount}</strong></td>
                            <td>${transfer.schedule_datetime}</td>
                            <td>${transfer.repeat_type}</td>
                            <td><span class="mlm-status-badge ${transfer.status}">${transfer.status}</span></td>
                            <td>
                                ${transfer.status === 'pending' ?
                                    `<button class="button button-small mlm-cancel-scheduled" data-id="${transfer.id}">❌ Cancel</button>` :
                                    '-'}
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                $('#mlm-scheduled-transfers-list').html(html);
            } else {
                $('#mlm-scheduled-transfers-list').html('<p style="text-align: center; color: #666;"><em><?php _e('No scheduled transfers found', 'thaiprompt-mlm'); ?></em></p>');
            }
        });
    }

    // Cancel Scheduled Transfer
    $(document).on('click', '.mlm-cancel-scheduled', function() {
        if (!confirm('<?php _e('Are you sure you want to cancel this scheduled transfer?', 'thaiprompt-mlm'); ?>')) {
            return;
        }

        const scheduleId = $(this).data('id');
        $.post(ajaxurl, {
            action: 'mlm_cancel_scheduled_transfer',
            nonce: thaipromptMLM.nonce,
            schedule_id: scheduleId
        }, function(response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                loadScheduledTransfers();
            } else {
                alert('❌ ' + response.data);
            }
        });
    });

    // Auto-load scheduled transfers on tab load
    if (window.location.href.indexOf('tab=scheduled') > -1) {
        loadScheduledTransfers();
    }
});
</script>
