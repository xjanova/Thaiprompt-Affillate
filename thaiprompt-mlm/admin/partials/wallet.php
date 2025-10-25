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
                                <button class="button button-small mlm-action-btn approve mlm-approve-withdrawal" data-withdrawal-id="<?php echo $withdrawal->id; ?>">
                                    <?php _e('Approve', 'thaiprompt-mlm'); ?>
                                </button>
                                <button class="button button-small mlm-action-btn reject mlm-reject-withdrawal" data-withdrawal-id="<?php echo $withdrawal->id; ?>">
                                    <?php _e('Reject', 'thaiprompt-mlm'); ?>
                                </button>
                            <?php elseif ($withdrawal->notes): ?>
                                <button class="button button-small" onclick="alert('<?php echo esc_js($withdrawal->notes); ?>')">
                                    <?php _e('View Notes', 'thaiprompt-mlm'); ?>
                                </button>
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
