<?php
/**
 * MLM Commissions Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$commissions = isset($commissions) ? $commissions : Thaiprompt_MLM_Database::get_user_commissions($user_id, array('limit' => 50));
$commission_stats = isset($commission_stats) ? $commission_stats : Thaiprompt_MLM_Commission::get_commission_summary($user_id);
?>

<div class="mlm-commissions">
    <div class="mlm-commissions-header">
        <h2><?php _e('My Commissions', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Track your earnings and commission history', 'thaiprompt-mlm'); ?></p>
    </div>

    <!-- Commission Summary -->
    <div class="mlm-stats-grid" style="margin-bottom: 30px;">
        <div class="mlm-stat-card success">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Total Earned', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo wc_price($commission_stats['total_earned']); ?></div>
                    <div class="mlm-stat-change"><?php _e('Approved', 'thaiprompt-mlm'); ?></div>
                </div>
                <div class="mlm-stat-icon">✅</div>
            </div>
        </div>

        <div class="mlm-stat-card warning">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Pending', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo wc_price($commission_stats['pending']); ?></div>
                    <div class="mlm-stat-change"><?php _e('Awaiting Approval', 'thaiprompt-mlm'); ?></div>
                </div>
                <div class="mlm-stat-icon">⏳</div>
            </div>
        </div>

        <div class="mlm-stat-card">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Total Transactions', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo number_format($commission_stats['total_transactions']); ?></div>
                    <div class="mlm-stat-change"><?php _e('All Time', 'thaiprompt-mlm'); ?></div>
                </div>
                <div class="mlm-stat-icon">📊</div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown -->
    <div class="mlm-commission-breakdown" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <h3><?php _e('Commission Breakdown', 'thaiprompt-mlm'); ?></h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;"><?php _e('Level Commissions', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($commission_stats['level_commissions']); ?></div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #f093fb;">
                <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;"><?php _e('Fast Start Bonus', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($commission_stats['fast_start']); ?></div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #4facfe;">
                <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;"><?php _e('Binary Commission', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($commission_stats['binary']); ?></div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #feca57;">
                <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;"><?php _e('Rank Bonus', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($commission_stats['rank_bonus']); ?></div>
            </div>
        </div>
    </div>

    <!-- Commission History -->
    <div class="mlm-commission-history" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <h3><?php _e('Recent Commissions', 'thaiprompt-mlm'); ?></h3>

        <?php if (!empty($commissions)): ?>
        <div class="mlm-table-responsive" style="margin-top: 20px; overflow-x: auto;">
            <table class="mlm-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left; font-weight: 600;"><?php _e('Date', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;"><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;"><?php _e('From', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 12px; text-align: right; font-weight: 600;"><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;"><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commissions as $commission):
                        $from_user = get_userdata($commission->from_user_id);
                        $status_color = $commission->status === 'approved' ? '#28a745' : '#ffc107';
                    ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($commission->created_at)); ?></td>
                        <td style="padding: 12px;">
                            <span style="display: inline-block; padding: 4px 10px; background: #e9ecef; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $commission->commission_type))); ?>
                            </span>
                        </td>
                        <td style="padding: 12px;"><?php echo $from_user ? esc_html($from_user->display_name) : '-'; ?></td>
                        <td style="padding: 12px; text-align: right; font-weight: 600; color: #28a745;"><?php echo wc_price($commission->amount); ?></td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="display: inline-block; padding: 4px 12px; background: <?php echo $status_color; ?>; color: white; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                <?php echo esc_html(ucfirst($commission->status)); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <div style="font-size: 48px; margin-bottom: 15px;">📊</div>
            <p><?php _e('No commissions yet. Start referring to earn!', 'thaiprompt-mlm'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
