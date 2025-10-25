<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
global $wpdb;
$network_table = $wpdb->prefix . 'thaiprompt_mlm_network';
$commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';
$wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

$total_members = $wpdb->get_var("SELECT COUNT(*) FROM $network_table");
$total_commissions = $wpdb->get_var("SELECT SUM(amount) FROM $commissions_table WHERE status = 'approved'");
$pending_commissions = $wpdb->get_var("SELECT SUM(amount) FROM $commissions_table WHERE status = 'pending'");
$total_sales = $wpdb->get_var("SELECT SUM(personal_sales) FROM $network_table");
$pending_withdrawals = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}thaiprompt_mlm_withdrawals WHERE status = 'pending'");

// Get recent activities
$recent_members = $wpdb->get_results("SELECT * FROM $network_table ORDER BY created_at DESC LIMIT 10");
$recent_commissions = $wpdb->get_results("SELECT * FROM $commissions_table ORDER BY created_at DESC LIMIT 10");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-networking" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php _e('Thaiprompt MLM Dashboard', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Stats Cards -->
    <div class="mlm-dashboard-cards">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Members', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">👥</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($total_members); ?></div>
            <div class="mlm-card-subtitle"><?php _e('Active network members', 'thaiprompt-mlm'); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Commissions', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">💰</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($total_commissions); ?></div>
            <div class="mlm-card-subtitle"><?php _e('Approved commissions paid', 'thaiprompt-mlm'); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Pending Commissions', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⏳</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($pending_commissions); ?></div>
            <div class="mlm-card-subtitle"><?php _e('Awaiting approval', 'thaiprompt-mlm'); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Sales', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📈</span>
            </div>
            <div class="mlm-card-value"><?php echo wc_price($total_sales); ?></div>
            <div class="mlm-card-subtitle"><?php _e('Network sales volume', 'thaiprompt-mlm'); ?></div>
        </div>
    </div>

    <?php if ($pending_withdrawals > 0): ?>
    <div class="mlm-notice warning">
        <p>
            <strong><?php _e('Action Required:', 'thaiprompt-mlm'); ?></strong>
            <?php printf(__('You have %d pending withdrawal requests.', 'thaiprompt-mlm'), $pending_withdrawals); ?>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-wallet'); ?>" class="button button-small">
                <?php _e('Review Now', 'thaiprompt-mlm'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
        <!-- Recent Members -->
        <div class="mlm-network-table">
            <h2><?php _e('Recent Members', 'thaiprompt-mlm'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Joined', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Sales', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_members): ?>
                        <?php foreach ($recent_members as $member): ?>
                            <?php $user = get_userdata($member->user_id); ?>
                            <?php if ($user): ?>
                            <tr>
                                <td>
                                    <?php echo get_avatar($user->ID, 32, '', '', array('class' => 'mlm-user-avatar')); ?>
                                    <strong><?php echo esc_html($user->display_name); ?></strong>
                                </td>
                                <td><?php echo esc_html($member->level); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($member->created_at)); ?></td>
                                <td><?php echo wc_price($member->personal_sales); ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                <?php _e('No members yet', 'thaiprompt-mlm'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Commissions -->
        <div class="mlm-network-table">
            <h2><?php _e('Recent Commissions', 'thaiprompt-mlm'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                        <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_commissions): ?>
                        <?php foreach ($recent_commissions as $commission): ?>
                            <?php $user = get_userdata($commission->user_id); ?>
                            <?php if ($user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($user->display_name); ?></strong>
                                </td>
                                <td><?php echo esc_html($commission->commission_type); ?></td>
                                <td><?php echo wc_price($commission->amount); ?></td>
                                <td>
                                    <span class="mlm-status-badge <?php echo esc_attr($commission->status); ?>">
                                        <?php echo esc_html(ucfirst($commission->status)); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                <?php _e('No commissions yet', 'thaiprompt-mlm'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="margin-top: 30px;">
        <h2><?php _e('Quick Actions', 'thaiprompt-mlm'); ?></h2>
        <p>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-commissions'); ?>" class="button button-primary">
                <?php _e('Approve Commissions', 'thaiprompt-mlm'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-wallet'); ?>" class="button button-primary">
                <?php _e('Process Withdrawals', 'thaiprompt-mlm'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-network'); ?>" class="button">
                <?php _e('View Network', 'thaiprompt-mlm'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=thaiprompt-mlm-settings'); ?>" class="button">
                <?php _e('Settings', 'thaiprompt-mlm'); ?>
            </a>
        </p>
    </div>

    <!-- Charts -->
    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="mlm-chart-container">
            <h3 class="mlm-chart-title"><?php _e('Sales Trend', 'thaiprompt-mlm'); ?></h3>
            <canvas id="mlm-sales-chart" width="400" height="200"></canvas>
        </div>
        <div class="mlm-chart-container">
            <h3 class="mlm-chart-title"><?php _e('Commission Distribution', 'thaiprompt-mlm'); ?></h3>
            <canvas id="mlm-commission-chart" width="400" height="200"></canvas>
        </div>
    </div>
</div>
