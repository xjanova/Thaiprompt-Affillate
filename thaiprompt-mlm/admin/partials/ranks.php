<?php
/**
 * Admin Ranks View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$ranks_table = $wpdb->prefix . 'thaiprompt_mlm_ranks';

// Handle Add/Edit Rank
if (isset($_POST['save_rank'])) {
    check_admin_referer('thaiprompt_mlm_rank');

    $rank_id = isset($_POST['rank_id']) ? intval($_POST['rank_id']) : 0;
    $rank_data = array(
        'rank_name' => sanitize_text_field($_POST['rank_name']),
        'rank_order' => intval($_POST['rank_order']),
        'rank_color' => sanitize_hex_color($_POST['rank_color']),
        'required_personal_sales' => floatval($_POST['required_personal_sales']),
        'required_group_sales' => floatval($_POST['required_group_sales']),
        'required_active_legs' => intval($_POST['required_active_legs']),
        'bonus_percentage' => floatval($_POST['bonus_percentage']),
        'bonus_amount' => floatval($_POST['bonus_amount']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );

    if ($rank_id > 0) {
        // Update existing rank
        $wpdb->update($ranks_table, $rank_data, array('id' => $rank_id));
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Rank updated successfully!', 'thaiprompt-mlm') . '</p></div>';
    } else {
        // Add new rank
        $wpdb->insert($ranks_table, $rank_data);
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Rank added successfully!', 'thaiprompt-mlm') . '</p></div>';
    }
}

// Handle Delete Rank
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['rank_id'])) {
    check_admin_referer('delete_rank_' . $_GET['rank_id']);
    $wpdb->delete($ranks_table, array('id' => intval($_GET['rank_id'])));
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Rank deleted successfully!', 'thaiprompt-mlm') . '</p></div>';
}

// Get rank for editing
$edit_rank = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['rank_id'])) {
    $edit_rank = $wpdb->get_row($wpdb->prepare("SELECT * FROM $ranks_table WHERE id = %d", intval($_GET['rank_id'])));
}

// Show form or list
$show_form = isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit');

// Get all ranks
$ranks = $wpdb->get_results("SELECT * FROM $ranks_table ORDER BY rank_order ASC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('MLM Ranks Management', 'thaiprompt-mlm'); ?>
    </h1>
    <?php if (!$show_form): ?>
        <a href="?page=thaiprompt-mlm-ranks&action=add" class="page-title-action">
            <?php _e('Add New Rank', 'thaiprompt-mlm'); ?>
        </a>
    <?php else: ?>
        <a href="?page=thaiprompt-mlm-ranks" class="page-title-action">
            ← <?php _e('Back to Ranks List', 'thaiprompt-mlm'); ?>
        </a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <?php if ($show_form): ?>
        <!-- Add/Edit Rank Form -->
        <div class="mlm-rank-form" style="max-width: 800px;">
            <h2><?php echo $edit_rank ? __('Edit Rank', 'thaiprompt-mlm') : __('Add New Rank', 'thaiprompt-mlm'); ?></h2>

            <form method="post" action="">
                <?php wp_nonce_field('thaiprompt_mlm_rank'); ?>
                <input type="hidden" name="rank_id" value="<?php echo $edit_rank ? $edit_rank->id : 0; ?>">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="rank_name"><?php _e('Rank Name', 'thaiprompt-mlm'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="rank_name" id="rank_name" value="<?php echo $edit_rank ? esc_attr($edit_rank->rank_name) : ''; ?>" class="regular-text" required>
                            <p class="description"><?php _e('Name of the rank (e.g., Bronze, Silver, Gold)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="rank_order"><?php _e('Rank Order', 'thaiprompt-mlm'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="number" name="rank_order" id="rank_order" value="<?php echo $edit_rank ? $edit_rank->rank_order : ($wpdb->get_var("SELECT MAX(rank_order) FROM $ranks_table") + 1); ?>" min="1" required style="width: 100px;">
                            <p class="description"><?php _e('Order of the rank (1 = lowest, higher numbers = higher ranks)', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="rank_color"><?php _e('Rank Color', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="color" name="rank_color" id="rank_color" value="<?php echo $edit_rank ? esc_attr($edit_rank->rank_color) : '#8B5CF6'; ?>">
                            <p class="description"><?php _e('Color used to represent this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3 style="margin: 20px 0 10px 0;"><?php _e('Requirements', 'thaiprompt-mlm'); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="required_personal_sales"><?php _e('Required Personal Sales', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="required_personal_sales" id="required_personal_sales" value="<?php echo $edit_rank ? $edit_rank->required_personal_sales : 0; ?>" step="0.01" min="0" style="width: 150px;">
                            <p class="description"><?php _e('Minimum personal sales required to achieve this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="required_group_sales"><?php _e('Required Group Sales', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="required_group_sales" id="required_group_sales" value="<?php echo $edit_rank ? $edit_rank->required_group_sales : 0; ?>" step="0.01" min="0" style="width: 150px;">
                            <p class="description"><?php _e('Minimum group sales required to achieve this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="required_active_legs"><?php _e('Required Active Legs', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="required_active_legs" id="required_active_legs" value="<?php echo $edit_rank ? $edit_rank->required_active_legs : 0; ?>" min="0" style="width: 100px;">
                            <p class="description"><?php _e('Number of active legs required for this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3 style="margin: 20px 0 10px 0;"><?php _e('Bonuses', 'thaiprompt-mlm'); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="bonus_percentage"><?php _e('Bonus Percentage (%)', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="bonus_percentage" id="bonus_percentage" value="<?php echo $edit_rank ? $edit_rank->bonus_percentage : 0; ?>" step="0.01" min="0" max="100" style="width: 100px;">
                            <p class="description"><?php _e('Additional commission percentage for this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="bonus_amount"><?php _e('Achievement Bonus', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="bonus_amount" id="bonus_amount" value="<?php echo $edit_rank ? $edit_rank->bonus_amount : 0; ?>" step="0.01" min="0" style="width: 150px;">
                            <p class="description"><?php _e('One-time bonus awarded when achieving this rank', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="is_active"><?php _e('Status', 'thaiprompt-mlm'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo ($edit_rank && $edit_rank->is_active) || !$edit_rank ? 'checked' : ''; ?>>
                                <?php _e('Active', 'thaiprompt-mlm'); ?>
                            </label>
                            <p class="description"><?php _e('Only active ranks are available for achievement', 'thaiprompt-mlm'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="save_rank" class="button button-primary button-large">
                        <?php echo $edit_rank ? __('Update Rank', 'thaiprompt-mlm') : __('Add Rank', 'thaiprompt-mlm'); ?>
                    </button>
                    <a href="?page=thaiprompt-mlm-ranks" class="button button-large">
                        <?php _e('Cancel', 'thaiprompt-mlm'); ?>
                    </a>
                </p>
            </form>
        </div>

    <?php else: ?>
        <!-- Ranks List -->
        <p><?php _e('Manage rank requirements and bonuses for your MLM network.', 'thaiprompt-mlm'); ?></p>

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
                                <a href="?page=thaiprompt-mlm-ranks&action=edit&rank_id=<?php echo $rank->id; ?>" class="button button-small">
                                    <?php _e('Edit', 'thaiprompt-mlm'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url('?page=thaiprompt-mlm-ranks&action=delete&rank_id=' . $rank->id, 'delete_rank_' . $rank->id); ?>" class="button button-small" onclick="return confirm('<?php _e('Are you sure you want to delete this rank?', 'thaiprompt-mlm'); ?>')">
                                    <?php _e('Delete', 'thaiprompt-mlm'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <strong><?php _e('No ranks found', 'thaiprompt-mlm'); ?></strong>
                            <p>
                                <a href="?page=thaiprompt-mlm-ranks&action=add" class="button button-primary">
                                    <?php _e('Add Your First Rank', 'thaiprompt-mlm'); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Rank Distribution Chart -->
        <?php if ($ranks): ?>
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
        <?php endif; ?>
    <?php endif; ?>
</div>
