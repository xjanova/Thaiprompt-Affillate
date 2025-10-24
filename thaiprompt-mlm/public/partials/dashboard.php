<?php
/**
 * MLM Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$position = isset($position) ? $position : Thaiprompt_MLM_Network::get_user_position($user_id);
$team_stats = isset($team_stats) ? $team_stats : Thaiprompt_MLM_Network::get_team_stats($user_id);
$wallet_stats = isset($wallet_stats) ? $wallet_stats : Thaiprompt_MLM_Wallet::get_wallet_stats($user_id);
$rank = isset($rank) ? $rank : Thaiprompt_MLM_Database::get_user_rank($user_id);
$rank_progress = isset($rank_progress) ? $rank_progress : Thaiprompt_MLM_Rank::get_rank_progress($user_id);
?>

<div class="mlm-dashboard">
    <div class="mlm-dashboard-header">
        <h2><?php _e('MLM Dashboard', 'thaiprompt-mlm'); ?></h2>
        <p><?php printf(__('Welcome back, %s!', 'thaiprompt-mlm'), wp_get_current_user()->display_name); ?></p>
    </div>

    <!-- Stats Grid -->
    <div class="mlm-stats-grid">
        <div class="mlm-stat-card">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Wallet Balance', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo wc_price($wallet_stats['balance']); ?></div>
                    <div class="mlm-stat-change">
                        <?php printf(__('Pending: %s', 'thaiprompt-mlm'), wc_price($wallet_stats['pending_balance'])); ?>
                    </div>
                </div>
                <div class="mlm-stat-icon">💰</div>
            </div>
        </div>

        <div class="mlm-stat-card success">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Total Earned', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo wc_price($wallet_stats['total_earned']); ?></div>
                    <div class="mlm-stat-change">
                        <?php printf(__('Withdrawn: %s', 'thaiprompt-mlm'), wc_price($wallet_stats['total_withdrawn'])); ?>
                    </div>
                </div>
                <div class="mlm-stat-icon">📈</div>
            </div>
        </div>

        <div class="mlm-stat-card warning">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Team Size', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo number_format($team_stats['total_team']); ?></div>
                    <div class="mlm-stat-change">
                        <?php printf(__('Active: %d', 'thaiprompt-mlm'), $team_stats['active_members']); ?>
                    </div>
                </div>
                <div class="mlm-stat-icon">👥</div>
            </div>
        </div>

        <div class="mlm-stat-card danger">
            <div class="mlm-stat-header">
                <div class="mlm-stat-content">
                    <div class="mlm-stat-label"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-stat-value"><?php echo wc_price($team_stats['total_sales']); ?></div>
                    <div class="mlm-stat-change">
                        <?php printf(__('Personal: %s', 'thaiprompt-mlm'), wc_price($position['personal_sales'] ?? 0)); ?>
                    </div>
                </div>
                <div class="mlm-stat-icon">💵</div>
            </div>
        </div>
    </div>

    <!-- Current Rank -->
    <?php if ($rank): ?>
    <div class="mlm-rank-progress">
        <h3><?php _e('Your Rank', 'thaiprompt-mlm'); ?></h3>
        <div class="mlm-rank-current">
            <span class="mlm-rank-badge-large" style="background-color: <?php echo esc_attr($rank->rank_color); ?>">
                <?php echo esc_html($rank->rank_name); ?>
            </span>
        </div>

        <?php if ($rank_progress['next_rank']): ?>
        <div class="mlm-rank-next">
            <div class="mlm-rank-arrow">⬇</div>
            <h4><?php printf(__('Next Rank: %s', 'thaiprompt-mlm'), $rank_progress['next_rank']['name']); ?></h4>
            <div class="mlm-progress-bar-container">
                <div class="mlm-progress-bar" style="width: <?php echo $rank_progress['progress']; ?>%">
                    <?php echo round($rank_progress['progress']); ?>%
                </div>
            </div>

            <div class="mlm-requirements">
                <div class="mlm-requirement-item <?php echo $rank_progress['requirements_met']['personal_sales']['met'] ? 'met' : ''; ?>">
                    <div class="mlm-requirement-label"><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-requirement-value">
                        <?php echo wc_price($rank_progress['requirements_met']['personal_sales']['current']); ?> /
                        <?php echo wc_price($rank_progress['requirements_met']['personal_sales']['required']); ?>
                    </div>
                </div>

                <div class="mlm-requirement-item <?php echo $rank_progress['requirements_met']['group_sales']['met'] ? 'met' : ''; ?>">
                    <div class="mlm-requirement-label"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-requirement-value">
                        <?php echo wc_price($rank_progress['requirements_met']['group_sales']['current']); ?> /
                        <?php echo wc_price($rank_progress['requirements_met']['group_sales']['required']); ?>
                    </div>
                </div>

                <div class="mlm-requirement-item <?php echo $rank_progress['requirements_met']['active_legs']['met'] ? 'met' : ''; ?>">
                    <div class="mlm-requirement-label"><?php _e('Active Legs', 'thaiprompt-mlm'); ?></div>
                    <div class="mlm-requirement-value">
                        <?php echo number_format($rank_progress['requirements_met']['active_legs']['current']); ?> /
                        <?php echo number_format($rank_progress['requirements_met']['active_legs']['required']); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="mlm-rank-max">
            <p><?php _e('🎉 Congratulations! You have reached the highest rank!', 'thaiprompt-mlm'); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Referral Link -->
    <div class="mlm-referral-box">
        <h3 class="mlm-referral-title"><?php _e('Your Referral Link', 'thaiprompt-mlm'); ?></h3>
        <div class="mlm-referral-link">
            <input type="text" class="mlm-referral-input" value="<?php echo esc_attr(Thaiprompt_MLM_Network::get_referral_link($user_id)); ?>" readonly />
            <button class="mlm-copy-button" data-clipboard-text="<?php echo esc_attr(Thaiprompt_MLM_Network::get_referral_link($user_id)); ?>">
                <?php _e('Copy', 'thaiprompt-mlm'); ?>
            </button>
        </div>
        <div class="mlm-share-buttons" style="margin-top: 15px;">
            <button class="mlm-share-btn" data-platform="facebook" style="background: #4267B2; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin-right: 5px; cursor: pointer;">Facebook</button>
            <button class="mlm-share-btn" data-platform="line" style="background: #00B900; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin-right: 5px; cursor: pointer;">LINE</button>
            <button class="mlm-share-btn" data-platform="twitter" style="background: #1DA1F2; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin-right: 5px; cursor: pointer;">Twitter</button>
            <button class="mlm-share-btn" data-platform="whatsapp" style="background: #25D366; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">WhatsApp</button>
        </div>
    </div>

    <!-- Team Overview -->
    <div class="mlm-team-overview" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <h3><?php _e('Team Overview', 'thaiprompt-mlm'); ?></h3>
        <div class="mlm-team-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;"><?php _e('Left Leg', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 24px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($team_stats['left_leg_sales']); ?></div>
                <div style="font-size: 12px; color: #6c757d; margin-top: 5px;"><?php printf(__('%d members', 'thaiprompt-mlm'), $team_stats['left_count']); ?></div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;"><?php _e('Right Leg', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 24px; font-weight: 700; color: #2c3e50;"><?php echo wc_price($team_stats['right_leg_sales']); ?></div>
                <div style="font-size: 12px; color: #6c757d; margin-top: 5px;"><?php printf(__('%d members', 'thaiprompt-mlm'), $team_stats['right_count']); ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mlm-quick-actions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <a href="<?php echo wc_get_account_endpoint_url('mlm-network'); ?>" class="mlm-action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; transition: transform 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px;">🌳</div>
            <div style="font-weight: 600;"><?php _e('View Network', 'thaiprompt-mlm'); ?></div>
        </a>
        <a href="<?php echo wc_get_account_endpoint_url('mlm-wallet'); ?>" class="mlm-action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; transition: transform 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px;">💳</div>
            <div style="font-weight: 600;"><?php _e('My Wallet', 'thaiprompt-mlm'); ?></div>
        </a>
        <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="mlm-action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; transition: transform 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px;">📦</div>
            <div style="font-weight: 600;"><?php _e('My Orders', 'thaiprompt-mlm'); ?></div>
        </a>
    </div>
</div>

<style>
.mlm-action-card:hover {
    transform: translateY(-5px);
}
</style>
