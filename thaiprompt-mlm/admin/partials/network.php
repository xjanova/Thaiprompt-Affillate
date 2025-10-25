<?php
/**
 * Admin Network View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$network_table = $wpdb->prefix . 'thaiprompt_mlm_network';

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Search
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Query
$where = '';
if ($search) {
    $where = $wpdb->prepare(" AND user_id IN (SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s OR user_email LIKE %s)", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
}

$total = $wpdb->get_var("SELECT COUNT(*) FROM $network_table WHERE 1=1 $where");
$members = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $network_table WHERE 1=1 $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_pages = ceil($total / $per_page);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('MLM Network', 'thaiprompt-mlm'); ?>
    </h1>
    <a href="#" class="page-title-action mlm-add-user-network">
        <?php _e('Add User to Network', 'thaiprompt-mlm'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Search -->
    <form method="get" style="margin: 20px 0;">
        <input type="hidden" name="page" value="thaiprompt-mlm-network">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search users...', 'thaiprompt-mlm'); ?>">
            <input type="submit" class="button" value="<?php _e('Search', 'thaiprompt-mlm'); ?>">
        </p>
    </form>

    <!-- Members Table -->
    <div class="mlm-network-table">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Sponsor', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Placement', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Position', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Downline', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Group Sales', 'thaiprompt-mlm'); ?></th>
                    <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members): ?>
                    <?php foreach ($members as $member): ?>
                        <?php
                        $user = get_userdata($member->user_id);
                        $sponsor = $member->sponsor_id ? get_userdata($member->sponsor_id) : null;
                        $placement = $member->placement_id ? get_userdata($member->placement_id) : null;
                        ?>
                        <?php if ($user): ?>
                        <tr>
                            <td>
                                <?php echo get_avatar($user->ID, 32, '', '', array('class' => 'mlm-user-avatar')); ?>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            </td>
                            <td>
                                <?php if ($sponsor): ?>
                                    <?php echo esc_html($sponsor->display_name); ?>
                                <?php else: ?>
                                    <em><?php _e('Root', 'thaiprompt-mlm'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($placement): ?>
                                    <?php echo esc_html($placement->display_name); ?>
                                <?php else: ?>
                                    <em><?php _e('Root', 'thaiprompt-mlm'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($member->level); ?></td>
                            <td>
                                <?php if ($member->position): ?>
                                    <span class="mlm-status-badge" style="background: #3498db; color: white;">
                                        <?php echo esc_html(ucfirst($member->position)); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo number_format($member->total_downline); ?></strong>
                                <small>(L: <?php echo $member->left_count; ?>, R: <?php echo $member->right_count; ?>)</small>
                            </td>
                            <td><?php echo wc_price($member->personal_sales); ?></td>
                            <td><?php echo wc_price($member->group_sales); ?></td>
                            <td>
                                <button class="button button-small mlm-view-genealogy" data-user-id="<?php echo $member->user_id; ?>">
                                    <?php _e('Genealogy', 'thaiprompt-mlm'); ?>
                                </button>
                                <button class="button button-small mlm-update-rank" data-user-id="<?php echo $member->user_id; ?>">
                                    <?php _e('Update Rank', 'thaiprompt-mlm'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <strong><?php _e('No members found', 'thaiprompt-mlm'); ?></strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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

<!-- Add User Modal -->
<div id="mlm-add-user-modal" class="mlm-modal" style="display: none;">
    <div class="mlm-modal-overlay"></div>
    <div class="mlm-modal-content">
        <div class="mlm-modal-header">
            <h3 class="mlm-modal-title"><?php _e('Add User to Network', 'thaiprompt-mlm'); ?></h3>
            <button class="mlm-modal-close">&times;</button>
        </div>
        <form id="mlm-add-user-form">
            <table class="form-table">
                <tr>
                    <th><?php _e('User', 'thaiprompt-mlm'); ?></th>
                    <td>
                        <select name="user_id" required class="regular-text">
                            <option value=""><?php _e('Select User', 'thaiprompt-mlm'); ?></option>
                            <?php
                            $users = get_users(array('fields' => array('ID', 'display_name')));
                            foreach ($users as $u) {
                                echo '<option value="' . $u->ID . '">' . esc_html($u->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Sponsor', 'thaiprompt-mlm'); ?></th>
                    <td>
                        <select name="sponsor_id" required class="regular-text">
                            <option value=""><?php _e('Select Sponsor', 'thaiprompt-mlm'); ?></option>
                            <?php
                            foreach ($users as $u) {
                                echo '<option value="' . $u->ID . '">' . esc_html($u->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="submit" class="button button-primary">
                            <?php _e('Add User', 'thaiprompt-mlm'); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<!-- Genealogy Modal -->
<div id="mlm-genealogy-modal" class="mlm-modal" style="display: none;">
    <div class="mlm-modal-overlay"></div>
    <div class="mlm-modal-content" style="max-width: 90%; width: 1200px; max-height: 90vh; overflow: auto;">
        <div class="mlm-modal-header">
            <h3 class="mlm-modal-title"><?php _e('Genealogy Tree', 'thaiprompt-mlm'); ?></h3>
            <button class="mlm-modal-close">&times;</button>
        </div>
        <div id="mlm-genealogy-content"></div>
    </div>
</div>
