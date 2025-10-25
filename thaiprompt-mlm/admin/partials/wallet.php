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

    <!-- Status Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=thaiprompt-mlm-wallet&status=pending" class="nav-tab <?php echo $status_filter === 'pending' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Pending', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&status=completed" class="nav-tab <?php echo $status_filter === 'completed' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Completed', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&status=rejected" class="nav-tab <?php echo $status_filter === 'rejected' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Rejected', 'thaiprompt-mlm'); ?>
        </a>
        <a href="?page=thaiprompt-mlm-wallet&status=" class="nav-tab <?php echo $status_filter === '' ? 'nav-tab-active' : ''; ?>">
            <?php _e('All', 'thaiprompt-mlm'); ?>
        </a>
    </h2>

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
});
</script>
