<?php
/**
 * MLM Rank Progress Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$rank_progress = isset($rank_progress) ? $rank_progress : Thaiprompt_MLM_Rank::get_rank_progress($user_id);
$all_ranks = Thaiprompt_MLM_Database::get_all_ranks();
?>

<div class="mlm-rank-progress-page">
    <div class="mlm-rank-header">
        <h2><?php _e('My Rank Progress', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Track your progress through the MLM ranks', 'thaiprompt-mlm'); ?></p>
    </div>

    <!-- Current Rank Card -->
    <div class="mlm-current-rank-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 16px; margin-bottom: 30px; text-align: center; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;"><?php _e('YOUR CURRENT RANK', 'thaiprompt-mlm'); ?></div>
        <div style="font-size: 48px; font-weight: 700; margin-bottom: 10px;">
            <?php echo esc_html($rank_progress['current_rank']['name']); ?>
        </div>
        <div style="font-size: 16px; opacity: 0.9;">
            <?php
            $achieved_date = isset($rank_progress['current_rank']['achieved_at']) && $rank_progress['current_rank']['achieved_at']
                ? date('F d, Y', strtotime($rank_progress['current_rank']['achieved_at']))
                : date('F d, Y');
            printf(__('Achieved on %s', 'thaiprompt-mlm'), $achieved_date);
            ?>
        </div>
    </div>

    <?php if ($rank_progress['next_rank']): ?>
    <!-- Next Rank Progress -->
    <div class="mlm-next-rank-section" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <h3><?php _e('Progress to Next Rank', 'thaiprompt-mlm'); ?></h3>
        <div style="text-align: center; margin: 30px 0;">
            <div style="font-size: 18px; color: #6c757d; margin-bottom: 10px;">
                <?php printf(__('Next: %s', 'thaiprompt-mlm'), $rank_progress['next_rank']['name']); ?>
            </div>
            <div class="mlm-circular-progress" style="position: relative; width: 200px; height: 200px; margin: 0 auto;">
                <svg width="200" height="200" style="transform: rotate(-90deg);">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#e9ecef" stroke-width="12"></circle>
                    <circle cx="100" cy="100" r="90" fill="none" stroke="url(#gradient)" stroke-width="12"
                            stroke-dasharray="<?php echo 2 * 3.14159 * 90; ?>"
                            stroke-dashoffset="<?php echo 2 * 3.14159 * 90 * (1 - $rank_progress['progress'] / 100); ?>"
                            stroke-linecap="round"></circle>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <div style="font-size: 36px; font-weight: 700; color: #2c3e50;"><?php echo round($rank_progress['progress']); ?>%</div>
                    <div style="font-size: 12px; color: #6c757d;"><?php _e('Complete', 'thaiprompt-mlm'); ?></div>
                </div>
            </div>
        </div>

        <!-- Requirements -->
        <div class="mlm-requirements-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
            <!-- Personal Sales -->
            <div style="padding: 20px; background: #f8f9fa; border-radius: 12px; border-left: 4px solid <?php echo $rank_progress['requirements_met']['personal_sales']['met'] ? '#28a745' : '#ffc107'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #2c3e50;"><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></span>
                    <span style="font-size: 20px;"><?php echo $rank_progress['requirements_met']['personal_sales']['met'] ? '✅' : '⏳'; ?></span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">
                    <?php echo wc_price($rank_progress['requirements_met']['personal_sales']['current']); ?>
                </div>
                <div style="font-size: 14px; color: #6c757d;">
                    <?php printf(__('Goal: %s', 'thaiprompt-mlm'), wc_price($rank_progress['requirements_met']['personal_sales']['required'])); ?>
                </div>
                <div class="mlm-progress-bar-container" style="margin-top: 10px; background: #dee2e6; height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="width: <?php echo min(100, $rank_progress['requirements_met']['personal_sales']['percentage']); ?>%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s;"></div>
                </div>
            </div>

            <!-- Group Sales -->
            <div style="padding: 20px; background: #f8f9fa; border-radius: 12px; border-left: 4px solid <?php echo $rank_progress['requirements_met']['group_sales']['met'] ? '#28a745' : '#ffc107'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #2c3e50;"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></span>
                    <span style="font-size: 20px;"><?php echo $rank_progress['requirements_met']['group_sales']['met'] ? '✅' : '⏳'; ?></span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">
                    <?php echo wc_price($rank_progress['requirements_met']['group_sales']['current']); ?>
                </div>
                <div style="font-size: 14px; color: #6c757d;">
                    <?php printf(__('Goal: %s', 'thaiprompt-mlm'), wc_price($rank_progress['requirements_met']['group_sales']['required'])); ?>
                </div>
                <div class="mlm-progress-bar-container" style="margin-top: 10px; background: #dee2e6; height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="width: <?php echo min(100, $rank_progress['requirements_met']['group_sales']['percentage']); ?>%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s;"></div>
                </div>
            </div>

            <!-- Active Legs -->
            <div style="padding: 20px; background: #f8f9fa; border-radius: 12px; border-left: 4px solid <?php echo $rank_progress['requirements_met']['active_legs']['met'] ? '#28a745' : '#ffc107'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #2c3e50;"><?php _e('Active Legs', 'thaiprompt-mlm'); ?></span>
                    <span style="font-size: 20px;"><?php echo $rank_progress['requirements_met']['active_legs']['met'] ? '✅' : '⏳'; ?></span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">
                    <?php echo number_format($rank_progress['requirements_met']['active_legs']['current']); ?>
                </div>
                <div style="font-size: 14px; color: #6c757d;">
                    <?php printf(__('Goal: %d', 'thaiprompt-mlm'), $rank_progress['requirements_met']['active_legs']['required']); ?>
                </div>
                <div class="mlm-progress-bar-container" style="margin-top: 10px; background: #dee2e6; height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="width: <?php echo min(100, $rank_progress['requirements_met']['active_legs']['percentage']); ?>%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Max Rank Achieved -->
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 60px; border-radius: 16px; text-align: center; box-shadow: 0 10px 40px rgba(240, 147, 251, 0.3);">
        <div style="font-size: 72px; margin-bottom: 20px;">🏆</div>
        <h2 style="font-size: 32px; margin-bottom: 10px;"><?php _e('Congratulations!', 'thaiprompt-mlm'); ?></h2>
        <p style="font-size: 18px; opacity: 0.9;"><?php _e('You have reached the highest rank in our MLM system!', 'thaiprompt-mlm'); ?></p>
    </div>
    <?php endif; ?>

    <!-- All Ranks -->
    <div class="mlm-all-ranks" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 30px;">
        <h3><?php _e('All Ranks', 'thaiprompt-mlm'); ?></h3>
        <div class="mlm-ranks-timeline" style="margin-top: 30px;">
            <?php foreach ($all_ranks as $index => $rank):
                $current_rank_id = isset($rank_progress['current_rank']['id']) ? $rank_progress['current_rank']['id'] : 0;
                $current_rank_order = isset($rank_progress['current_rank']['rank_order']) ? $rank_progress['current_rank']['rank_order'] : 1;
                $is_current = $rank->id == $current_rank_id;
                $is_achieved = $rank->rank_order <= $current_rank_order;
            ?>
            <div style="display: flex; align-items: center; margin-bottom: 30px; position: relative; <?php echo !$is_achieved ? 'opacity: 0.5;' : ''; ?>">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: <?php echo $is_achieved ? $rank->rank_color : '#e9ecef'; ?>; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; font-weight: 700; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1;">
                    <?php echo $is_achieved ? '✓' : ($index + 1); ?>
                </div>
                <?php if ($index < count($all_ranks) - 1): ?>
                <div style="position: absolute; left: 30px; top: 60px; width: 2px; height: 30px; background: <?php echo $is_achieved ? '#28a745' : '#dee2e6'; ?>;"></div>
                <?php endif; ?>
                <div style="flex: 1; margin-left: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 20px; font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                <?php echo esc_html($rank->rank_name); ?>
                                <?php if ($is_current): ?>
                                <span style="display: inline-block; padding: 2px 8px; background: #667eea; color: white; border-radius: 12px; font-size: 12px; margin-left: 10px;">Current</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 14px; color: #6c757d;">
                                <?php printf(__('Sales: %s • Group: %s • Legs: %d', 'thaiprompt-mlm'),
                                    wc_price($rank->required_personal_sales),
                                    wc_price($rank->required_group_sales),
                                    $rank->required_active_legs
                                ); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;"><?php _e('Bonus', 'thaiprompt-mlm'); ?></div>
                            <div style="font-size: 18px; font-weight: 600; color: #28a745;">
                                <?php if ($rank->bonus_amount > 0): ?>
                                    <?php echo wc_price($rank->bonus_amount); ?>
                                <?php elseif ($rank->bonus_percentage > 0): ?>
                                    <?php echo $rank->bonus_percentage; ?>%
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
