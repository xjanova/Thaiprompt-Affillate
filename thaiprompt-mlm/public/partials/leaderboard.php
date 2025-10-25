<?php
/**
 * MLM Leaderboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$leaderboard = isset($leaderboard) ? $leaderboard : Thaiprompt_MLM_Rank::get_leaderboard(50);
$user_id = get_current_user_id();
?>

<div class="mlm-leaderboard">
    <div class="mlm-leaderboard-header">
        <h2><?php _e('Leaderboard', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Top performers in our MLM network', 'thaiprompt-mlm'); ?></p>
    </div>

    <!-- Top 3 Podium -->
    <?php if (count($leaderboard) >= 3): ?>
    <div class="mlm-podium" style="display: flex; align-items: flex-end; justify-content: center; gap: 20px; margin: 40px 0; padding: 40px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px;">
        <!-- 2nd Place -->
        <div style="text-align: center; flex: 1; max-width: 200px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #c0c0c0, #e8e8e8); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                <span style="font-size: 36px;">🥈</span>
            </div>
            <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; height: 150px; display: flex; flex-direction: column; justify-content: center;">
                <div style="font-size: 16px; font-weight: 600; color: white; margin-bottom: 5px;"><?php echo esc_html(get_userdata($leaderboard[1]->user_id)->display_name); ?></div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 10px;"><?php echo esc_html($leaderboard[1]->rank_name); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: white;"><?php echo wc_price($leaderboard[1]->group_sales); ?></div>
            </div>
        </div>

        <!-- 1st Place -->
        <div style="text-align: center; flex: 1; max-width: 200px; transform: translateY(-20px);">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #ffd700, #ffed4e); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; border: 4px solid rgba(255,255,255,0.4); box-shadow: 0 12px 32px rgba(0,0,0,0.3);">
                <span style="font-size: 48px;">🏆</span>
            </div>
            <div style="background: rgba(255,255,255,0.25); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; height: 170px; display: flex; flex-direction: column; justify-content: center;">
                <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px;"><?php echo esc_html(get_userdata($leaderboard[0]->user_id)->display_name); ?></div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.9); margin-bottom: 10px;"><?php echo esc_html($leaderboard[0]->rank_name); ?></div>
                <div style="font-size: 24px; font-weight: 700; color: white;"><?php echo wc_price($leaderboard[0]->group_sales); ?></div>
            </div>
        </div>

        <!-- 3rd Place -->
        <div style="text-align: center; flex: 1; max-width: 200px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #cd7f32, #e8a87c); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                <span style="font-size: 36px;">🥉</span>
            </div>
            <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; height: 150px; display: flex; flex-direction: column; justify-content: center;">
                <div style="font-size: 16px; font-weight: 600; color: white; margin-bottom: 5px;"><?php echo esc_html(get_userdata($leaderboard[2]->user_id)->display_name); ?></div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 10px;"><?php echo esc_html($leaderboard[2]->rank_name); ?></div>
                <div style="font-size: 20px; font-weight: 700; color: white;"><?php echo wc_price($leaderboard[2]->group_sales); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Full Leaderboard Table -->
    <div class="mlm-leaderboard-table" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 30px;">
        <h3><?php _e('Full Leaderboard', 'thaiprompt-mlm'); ?></h3>

        <?php if (!empty($leaderboard)): ?>
        <div style="margin-top: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 15px; text-align: center; font-weight: 600; width: 60px;"><?php _e('#', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;"><?php _e('Member', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;"><?php _e('Rank', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: right; font-weight: 600;"><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: right; font-weight: 600;"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: center; font-weight: 600;"><?php _e('Team Size', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $member):
                        $member_user = get_userdata($member->user_id);
                        $is_current_user = $member->user_id == $user_id;
                        $row_style = $is_current_user ? 'background: #e7f3ff; border-left: 4px solid #667eea;' : '';
                        $medal = '';
                        if ($index === 0) $medal = '🥇';
                        elseif ($index === 1) $medal = '🥈';
                        elseif ($index === 2) $medal = '🥉';
                    ?>
                    <tr style="border-bottom: 1px solid #dee2e6; <?php echo $row_style; ?>">
                        <td style="padding: 15px; text-align: center; font-weight: 600; font-size: 18px;">
                            <?php echo $medal ? $medal : ($index + 1); ?>
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px;">
                                    <?php echo strtoupper(substr($member_user->display_name, 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #2c3e50;">
                                        <?php echo esc_html($member_user->display_name); ?>
                                        <?php if ($is_current_user): ?>
                                        <span style="display: inline-block; padding: 2px 8px; background: #667eea; color: white; border-radius: 12px; font-size: 11px; margin-left: 8px;">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12px; color: #6c757d;"><?php echo esc_html($member_user->user_email); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <span style="display: inline-block; padding: 6px 12px; background: <?php echo esc_attr($member->rank_color); ?>; color: white; border-radius: 12px; font-size: 13px; font-weight: 500;">
                                <?php echo esc_html($member->rank_name); ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: 600; color: #2c3e50;">
                            <?php echo wc_price($member->personal_sales); ?>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: 700; color: #28a745; font-size: 16px;">
                            <?php echo wc_price($member->group_sales); ?>
                        </td>
                        <td style="padding: 15px; text-align: center; font-weight: 600; color: #6c757d;">
                            <?php echo number_format($member->total_downline); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: #6c757d;">
            <div style="font-size: 64px; margin-bottom: 20px;">🏆</div>
            <h3 style="margin-bottom: 10px; color: #2c3e50;"><?php _e('No Rankings Yet', 'thaiprompt-mlm'); ?></h3>
            <p><?php _e('Be the first to start earning and climb the leaderboard!', 'thaiprompt-mlm'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .mlm-podium {
        flex-direction: column;
        align-items: center !important;
    }
    .mlm-podium > div {
        transform: none !important;
        max-width: 280px !important;
        width: 100%;
    }
}
</style>
