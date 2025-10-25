<?php
/**
 * Admin Reports View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Date filter
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-t');

// Get statistics
$network_table = $wpdb->prefix . 'thaiprompt_mlm_network';
$commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

$stats = $wpdb->get_row($wpdb->prepare("
    SELECT
        COUNT(DISTINCT n.user_id) as total_members,
        SUM(n.personal_sales) as total_sales,
        SUM(n.group_sales) as total_group_sales,
        AVG(n.personal_sales) as avg_sales_per_member
    FROM $network_table n
    WHERE n.created_at BETWEEN %s AND %s
", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));

$commission_stats = $wpdb->get_row($wpdb->prepare("
    SELECT
        COUNT(*) as total_commissions,
        SUM(amount) as total_amount,
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount
    FROM $commissions_table
    WHERE created_at BETWEEN %s AND %s
", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));

// Top performers
$top_performers = $wpdb->get_results("
    SELECT user_id, personal_sales, group_sales
    FROM $network_table
    ORDER BY group_sales DESC
    LIMIT 10
");

// Commission by type
$commission_by_type = $wpdb->get_results($wpdb->prepare("
    SELECT
        commission_type,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM $commissions_table
    WHERE created_at BETWEEN %s AND %s
    GROUP BY commission_type
    ORDER BY total_amount DESC
", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
?>

<div class="wrap">
    <h1><?php _e('MLM Reports & Analytics', 'thaiprompt-mlm'); ?></h1>

    <!-- Date Filter -->
    <div class="tablenav top">
        <form method="get" style="display: flex; gap: 10px; align-items: center; margin: 20px 0;">
            <input type="hidden" name="page" value="thaiprompt-mlm-reports">
            <label><?php _e('From:', 'thaiprompt-mlm'); ?></label>
            <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>">
            <label><?php _e('To:', 'thaiprompt-mlm'); ?></label>
            <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>">
            <button type="submit" class="button button-primary"><?php _e('Apply Filter', 'thaiprompt-mlm'); ?></button>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="mlm-dashboard-cards">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Members', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">👥</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->total_members ?? 0); ?></div>
            <div class="mlm-card-subtitle"><?php printf(__('Period: %s to %s', 'thaiprompt-mlm'), $date_from, $date_to); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Sales', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">💵</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->total_sales ?? 0); ?></div>
            <div class="mlm-card-subtitle"><?php printf(__('Avg: %s per member', 'thaiprompt-mlm'), wc_price($stats->avg_sales_per_member ?? 0)); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📈</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($stats->total_group_sales ?? 0); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Commissions Paid', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">💰</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($commission_stats->approved_amount ?? 0); ?></div>
            <div class="mlm-card-subtitle"><?php printf(__('%s pending', 'thaiprompt-mlm'), wc_price($commission_stats->pending_amount ?? 0)); ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
        <!-- Top Performers -->
        <div class="mlm-network-table">
            <h2><?php _e('Top 10 Performers (By Group Sales)', 'thaiprompt-mlm'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('Rank', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Group Sales', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($top_performers): ?>
                        <?php $rank = 1; ?>
                        <?php foreach ($top_performers as $performer): ?>
                            <?php $user = get_userdata($performer->user_id); ?>
                            <?php if ($user): ?>
                            <tr>
                                <td>
                                    <strong style="font-size: 18px; color: <?php echo $rank <= 3 ? '#f39c12' : '#666'; ?>">
                                        #<?php echo $rank; ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo get_avatar($user->ID, 32, '', '', array('class' => 'mlm-user-avatar')); ?>
                                    <strong><?php echo esc_html($user->display_name); ?></strong>
                                </td>
                                <td><?php echo wc_price($performer->personal_sales); ?></td>
                                <td><strong><?php echo wc_price($performer->group_sales); ?></strong></td>
                            </tr>
                            <?php $rank++; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                <?php _e('No data available', 'thaiprompt-mlm'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Commission by Type -->
        <div class="mlm-network-table">
            <h2><?php _e('Commission by Type', 'thaiprompt-mlm'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Count', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Total Amount', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($commission_by_type): ?>
                        <?php foreach ($commission_by_type as $type): ?>
                        <tr>
                            <td><code><?php echo esc_html($type->commission_type); ?></code></td>
                            <td><?php echo number_format($type->count); ?></td>
                            <td><strong><?php echo wc_price($type->total_amount); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">
                                <?php _e('No data available', 'thaiprompt-mlm'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Options -->
    <div style="margin-top: 30px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2><?php _e('Export Reports', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Export data for further analysis:', 'thaiprompt-mlm'); ?></p>
        <button class="button mlm-export-csv" data-export-type="members">
            <?php _e('Export Members (CSV)', 'thaiprompt-mlm'); ?>
        </button>
        <button class="button mlm-export-csv" data-export-type="commissions">
            <?php _e('Export Commissions (CSV)', 'thaiprompt-mlm'); ?>
        </button>
        <button class="button mlm-export-csv" data-export-type="transactions">
            <?php _e('Export Transactions (CSV)', 'thaiprompt-mlm'); ?>
        </button>
    </div>
</div>
