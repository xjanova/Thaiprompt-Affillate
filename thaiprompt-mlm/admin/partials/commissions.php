<?php
/**
 * Admin Commissions View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

// Filters
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

// Pagination
$per_page = 50;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Build query
$where = array('1=1');
if ($status_filter) {
    $where[] = $wpdb->prepare("status = %s", $status_filter);
}
if ($type_filter) {
    $where[] = $wpdb->prepare("commission_type = %s", $type_filter);
}
$where_clause = implode(' AND ', $where);

$total = $wpdb->get_var("SELECT COUNT(*) FROM $commissions_table WHERE $where_clause");
$commissions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $commissions_table WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_pages = ceil($total / $per_page);

// Statistics
$stats = $wpdb->get_row("
    SELECT
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved,
        COUNT(*) as total_count
    FROM $commissions_table
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Commissions Management', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Stats -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Pending', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⏳</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->pending ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Approved', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">✅</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->approved ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Count', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📊</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->total_count ?? 0); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="page" value="thaiprompt-mlm-commissions">

            <select name="status" onchange="this.form.submit()">
                <option value=""><?php _e('All Statuses', 'thaiprompt-mlm'); ?></option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'thaiprompt-mlm'); ?></option>
                <option value="approved" <?php selected($status_filter, 'approved'); ?>><?php _e('Approved', 'thaiprompt-mlm'); ?></option>
            </select>

            <select name="type" onchange="this.form.submit()">
                <option value=""><?php _e('All Types', 'thaiprompt-mlm'); ?></option>
                <option value="level_1" <?php selected($type_filter, 'level_1'); ?>><?php _e('Level Commission', 'thaiprompt-mlm'); ?></option>
                <option value="fast_start" <?php selected($type_filter, 'fast_start'); ?>><?php _e('Fast Start', 'thaiprompt-mlm'); ?></option>
                <option value="binary" <?php selected($type_filter, 'binary'); ?>><?php _e('Binary', 'thaiprompt-mlm'); ?></option>
                <option value="rank_bonus" <?php selected($type_filter, 'rank_bonus'); ?>><?php _e('Rank Bonus', 'thaiprompt-mlm'); ?></option>
            </select>

            <?php if ($status_filter || $type_filter): ?>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-commissions'); ?>" class="button">
                <?php _e('Clear Filters', 'thaiprompt-mlm'); ?>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Commissions Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="5%"><?php _e('ID', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('From', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Order', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Date', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($commissions): ?>
                <?php foreach ($commissions as $commission): ?>
                    <?php
                    $user = get_userdata($commission->user_id);
                    $from_user = $commission->from_user_id ? get_userdata($commission->from_user_id) : null;
                    ?>
                    <tr>
                        <td><?php echo $commission->id; ?></td>
                        <td>
                            <?php if ($user): ?>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($from_user): ?>
                                <?php echo esc_html($from_user->display_name); ?>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo esc_html($commission->commission_type); ?></code>
                        </td>
                        <td>
                            <?php if ($commission->order_id): ?>
                                <a href="<?php echo admin_url('post.php?post=' . $commission->order_id . '&action=edit'); ?>" target="_blank">
                                    #<?php echo $commission->order_id; ?>
                                </a>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo wc_price($commission->amount); ?></strong></td>
                        <td><?php echo $commission->level ? 'L' . $commission->level : '-'; ?></td>
                        <td>
                            <span class="mlm-status-badge <?php echo esc_attr($commission->status); ?>">
                                <?php echo esc_html(ucfirst($commission->status)); ?>
                            </span>
                        </td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($commission->created_at)); ?></td>
                        <td>
                            <?php if ($commission->status === 'pending'): ?>
                                <button class="button button-small mlm-action-btn approve mlm-approve-commission" data-commission-id="<?php echo $commission->id; ?>">
                                    <?php _e('Approve', 'thaiprompt-mlm'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px;">
                        <strong><?php _e('No commissions found', 'thaiprompt-mlm'); ?></strong>
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
