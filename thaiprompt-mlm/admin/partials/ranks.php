<?php
/**
 * Admin Ranks View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$ranks_table = $wpdb->prefix . 'thaiprompt_mlm_ranks';

// Get all ranks
$ranks = $wpdb->get_results("SELECT * FROM $ranks_table ORDER BY rank_order ASC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('MLM Ranks Management', 'thaiprompt-mlm'); ?>
    </h1>
    <a href="#" class="page-title-action" onclick="alert('<?php _e('Add Rank feature coming soon!', 'thaiprompt-mlm'); ?>'); return false;">
        <?php _e('Add New Rank', 'thaiprompt-mlm'); ?>
    </a>
    <hr class="wp-header-end">

    <p><?php _e('Manage rank requirements and bonuses for your MLM network.', 'thaiprompt-mlm'); ?></p>

    <!-- Ranks Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="5%"><?php _e('Order', 'thaiprompt-mlm'); ?></th>
                <th width="3%"></th>
                <th><?php _e('Rank Name', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Requirements', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Bonuses', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Members', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ranks): ?>
                <?php foreach ($ranks as $rank): ?>
                    <?php
                    // Count members with this rank
                    $user_ranks_table = $wpdb->prefix . 'thaiprompt_mlm_user_ranks';
                    $members_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $user_ranks_table WHERE rank_id = %d AND is_current = 1",
                        $rank->id
                    ));
                    ?>
                    <tr>
                        <td><strong><?php echo $rank->rank_order; ?></strong></td>
                        <td>
                            <div style="width: 30px; height: 30px; border-radius: 50%; background-color: <?php echo esc_attr($rank->rank_color); ?>; border: 2px solid #ddd;"></div>
                        </td>
                        <td>
                            <strong style="font-size: 16px;"><?php echo esc_html($rank->rank_name); ?></strong>
                        </td>
                        <td>
                            <div style="font-size: 13px;">
                                <div><strong><?php _e('Personal Sales:', 'thaiprompt-mlm'); ?></strong> <?php echo wc_price($rank->required_personal_sales); ?></div>
                                <div><strong><?php _e('Group Sales:', 'thaiprompt-mlm'); ?></strong> <?php echo wc_price($rank->required_group_sales); ?></div>
                                <div><strong><?php _e('Active Legs:', 'thaiprompt-mlm'); ?></strong> <?php echo $rank->required_active_legs; ?></div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13px;">
                                <?php if ($rank->bonus_percentage > 0): ?>
                                    <div><strong><?php _e('Bonus %:', 'thaiprompt-mlm'); ?></strong> <?php echo $rank->bonus_percentage; ?>%</div>
                                <?php endif; ?>
                                <?php if ($rank->bonus_amount > 0): ?>
                                    <div><strong><?php _e('Achievement Bonus:', 'thaiprompt-mlm'); ?></strong> <?php echo wc_price($rank->bonus_amount); ?></div>
                                <?php endif; ?>
                                <?php if ($rank->bonus_percentage == 0 && $rank->bonus_amount == 0): ?>
                                    <em><?php _e('No bonuses', 'thaiprompt-mlm'); ?></em>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size: 18px; color: #2271b1;"><?php echo number_format($members_count); ?></strong>
                            <div style="font-size: 11px; color: #666;"><?php _e('members', 'thaiprompt-mlm'); ?></div>
                        </td>
                        <td>
                            <?php if ($rank->is_active): ?>
                                <span class="mlm-status-badge approved"><?php _e('Active', 'thaiprompt-mlm'); ?></span>
                            <?php else: ?>
                                <span class="mlm-status-badge"><?php _e('Inactive', 'thaiprompt-mlm'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button button-small" onclick="alert('<?php _e('Edit feature coming soon!', 'thaiprompt-mlm'); ?>')">
                                <?php _e('Edit', 'thaiprompt-mlm'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <strong><?php _e('No ranks found', 'thaiprompt-mlm'); ?></strong>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Rank Distribution Chart -->
    <div class="mlm-chart-container" style="margin-top: 30px;">
        <h2 class="mlm-chart-title"><?php _e('Rank Distribution', 'thaiprompt-mlm'); ?></h2>
        <canvas id="mlm-rank-distribution-chart" width="400" height="300"></canvas>
    </div>

    <script>
    jQuery(document).ready(function($) {
        if (typeof Chart !== 'undefined') {
            var ctx = document.getElementById('mlm-rank-distribution-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [<?php
                            $labels = array();
                            foreach ($ranks as $rank) {
                                $labels[] = "'" . esc_js($rank->rank_name) . "'";
                            }
                            echo implode(', ', $labels);
                        ?>],
                        datasets: [{
                            label: '<?php _e('Number of Members', 'thaiprompt-mlm'); ?>',
                            data: [<?php
                                $data = array();
                                foreach ($ranks as $rank) {
                                    $count = $wpdb->get_var($wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$wpdb->prefix}thaiprompt_mlm_user_ranks WHERE rank_id = %d AND is_current = 1",
                                        $rank->id
                                    ));
                                    $data[] = $count;
                                }
                                echo implode(', ', $data);
                            ?>],
                            backgroundColor: [<?php
                                $colors = array();
                                foreach ($ranks as $rank) {
                                    $colors[] = "'" . $rank->rank_color . "'";
                                }
                                echo implode(', ', $colors);
                            ?>],
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    });
    </script>
</div>
