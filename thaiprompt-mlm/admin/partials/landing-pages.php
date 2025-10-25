<?php
/**
 * Admin Landing Pages View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$landing_pages_table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

// Filters
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Build query
$where = array('1=1');
if ($status_filter) {
    $where[] = $wpdb->prepare("status = %s", $status_filter);
}
$where_clause = implode(' AND ', $where);

$total = $wpdb->get_var("SELECT COUNT(*) FROM $landing_pages_table WHERE $where_clause");
$landing_pages = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $landing_pages_table WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_pages = ceil($total / $per_page);

// Statistics
$stats = $wpdb->get_row("
    SELECT
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(views) as total_views,
        SUM(conversions) as total_conversions,
        COUNT(*) as total_count
    FROM $landing_pages_table
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Landing Pages Management', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Stats -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Pending Review', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">⏳</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->pending ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Approved', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">✅</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->approved ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Rejected', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">❌</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->rejected ?? 0); ?></div>
        </div>
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Views', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">👁️</span>
            </div>
            <div class="mlm-card-value"><?php echo number_format($stats->total_views ?? 0); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="page" value="thaiprompt-mlm-landing-pages">

            <select name="status" onchange="this.form.submit()">
                <option value=""><?php _e('All Statuses', 'thaiprompt-mlm'); ?></option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'thaiprompt-mlm'); ?></option>
                <option value="approved" <?php selected($status_filter, 'approved'); ?>><?php _e('Approved', 'thaiprompt-mlm'); ?></option>
                <option value="rejected" <?php selected($status_filter, 'rejected'); ?>><?php _e('Rejected', 'thaiprompt-mlm'); ?></option>
            </select>
        </form>
    </div>

    <!-- Landing Pages List -->
    <?php if (empty($landing_pages)): ?>
        <div class="notice notice-info">
            <p><?php _e('No landing pages found.', 'thaiprompt-mlm'); ?></p>
        </div>
    <?php else: ?>
        <div class="mlm-landing-pages-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(500px, 1fr)); gap: 20px; margin: 20px 0;">
            <?php foreach ($landing_pages as $page):
                $user = get_userdata($page->user_id);
                $status_badge = '';
                $status_color = '';

                switch ($page->status) {
                    case 'pending':
                        $status_badge = '⏳ Pending';
                        $status_color = '#f39c12';
                        break;
                    case 'approved':
                        $status_badge = '✅ Approved';
                        $status_color = '#27ae60';
                        break;
                    case 'rejected':
                        $status_badge = '❌ Rejected';
                        $status_color = '#e74c3c';
                        break;
                }
            ?>
            <div class="mlm-landing-page-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <!-- Header -->
                <div style="padding: 15px; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 16px;"><?php echo esc_html($page->title); ?></h3>
                        <span style="background: <?php echo $status_color; ?>; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                            <?php echo $status_badge; ?>
                        </span>
                    </div>
                    <div style="font-size: 13px; color: #666;">
                        <?php _e('By:', 'thaiprompt-mlm'); ?> <strong><?php echo esc_html($user->display_name); ?></strong>
                        <span style="margin-left: 15px;">📅 <?php echo date('Y-m-d H:i', strtotime($page->created_at)); ?></span>
                    </div>
                </div>

                <!-- Preview Images -->
                <?php if ($page->image1_url || $page->image2_url || $page->image3_url): ?>
                <div style="display: flex; gap: 5px; background: #f9f9f9; padding: 10px;">
                    <?php for ($i = 1; $i <= 3; $i++):
                        $img_field = 'image' . $i . '_url';
                        if ($page->$img_field): ?>
                        <img src="<?php echo esc_url($page->$img_field); ?>" alt="Image <?php echo $i; ?>" style="width: calc(33.33% - 4px); height: 100px; object-fit: cover; border-radius: 4px;">
                    <?php endif; endfor; ?>
                </div>
                <?php endif; ?>

                <!-- Content Preview -->
                <div style="padding: 15px; border-bottom: 1px solid #eee;">
                    <p style="margin: 0 0 10px; font-size: 14px; font-weight: 600;">
                        <?php echo esc_html($page->headline); ?>
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #666; line-height: 1.5;">
                        <?php echo esc_html(wp_trim_words($page->description, 30)); ?>
                    </p>
                    <p style="margin: 10px 0 0; font-size: 12px; color: #3498db;">
                        <strong><?php _e('CTA:', 'thaiprompt-mlm'); ?></strong> <?php echo esc_html($page->cta_text); ?>
                    </p>
                </div>

                <!-- Stats -->
                <div style="padding: 10px 15px; background: #f9f9f9; border-bottom: 1px solid #eee; display: flex; gap: 20px;">
                    <span style="font-size: 12px;">👁️ <?php echo number_format($page->views); ?> <?php _e('views', 'thaiprompt-mlm'); ?></span>
                    <span style="font-size: 12px;">✨ <?php echo number_format($page->conversions); ?> <?php _e('conversions', 'thaiprompt-mlm'); ?></span>
                </div>

                <!-- Admin Notes -->
                <?php if ($page->admin_notes): ?>
                <div style="padding: 10px 15px; background: #fffbcc; border-bottom: 1px solid #eee;">
                    <strong style="font-size: 12px;"><?php _e('Admin Notes:', 'thaiprompt-mlm'); ?></strong>
                    <p style="margin: 5px 0 0; font-size: 12px; color: #666;">
                        <?php echo esc_html($page->admin_notes); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Version History -->
                <?php
                $version_count = Thaiprompt_MLM_Landing_Page_Version::get_version_count($page->id);
                if ($version_count > 0):
                ?>
                <div style="padding: 10px 15px; background: #e7f3ff; border-bottom: 1px solid #eee;">
                    <strong style="font-size: 12px;">📝 <?php _e('Version History:', 'thaiprompt-mlm'); ?></strong>
                    <p style="margin: 5px 0 0; font-size: 12px; color: #666;">
                        <?php printf(__('%d versions tracked', 'thaiprompt-mlm'), $version_count); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div style="padding: 15px; display: flex; gap: 10px; justify-content: space-between;">
                    <?php if ($page->status === 'pending'): ?>
                        <button type="button" class="button button-primary mlm-approve-landing" data-id="<?php echo $page->id; ?>" style="flex: 1;">
                            ✅ <?php _e('Approve', 'thaiprompt-mlm'); ?>
                        </button>
                        <button type="button" class="button mlm-reject-landing" data-id="<?php echo $page->id; ?>" style="flex: 1; background: #e74c3c; border-color: #c0392b; color: #fff;">
                            ❌ <?php _e('Reject', 'thaiprompt-mlm'); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="button mlm-view-full-landing" data-id="<?php echo $page->id; ?>" style="flex: 1;">
                            👁️ <?php _e('View Full Details', 'thaiprompt-mlm'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Approve Modal -->
<div id="mlm-approve-modal" class="mlm-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 100000; align-items: center; justify-content: center;">
    <div class="mlm-modal-content" style="background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;"><?php _e('Approve Landing Page', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Add optional notes for the user:', 'thaiprompt-mlm'); ?></p>
        <textarea id="mlm-approve-notes" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="button" id="mlm-approve-cancel"><?php _e('Cancel', 'thaiprompt-mlm'); ?></button>
            <button type="button" class="button button-primary" id="mlm-approve-confirm">✅ <?php _e('Approve & Activate', 'thaiprompt-mlm'); ?></button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="mlm-reject-modal" class="mlm-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 100000; align-items: center; justify-content: center;">
    <div class="mlm-modal-content" style="background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;"><?php _e('Reject Landing Page', 'thaiprompt-mlm'); ?></h2>
        <p><?php _e('Please provide a reason for rejection:', 'thaiprompt-mlm'); ?></p>
        <textarea id="mlm-reject-notes" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>
        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="button" id="mlm-reject-cancel"><?php _e('Cancel', 'thaiprompt-mlm'); ?></button>
            <button type="button" class="button button-primary" id="mlm-reject-confirm" style="background: #e74c3c; border-color: #c0392b;">❌ <?php _e('Reject', 'thaiprompt-mlm'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentLandingId = 0;

    // Approve button
    $('.mlm-approve-landing').on('click', function() {
        currentLandingId = $(this).data('id');
        $('#mlm-approve-modal').css('display', 'flex');
        $('#mlm-approve-notes').val('');
    });

    // Reject button
    $('.mlm-reject-landing').on('click', function() {
        currentLandingId = $(this).data('id');
        $('#mlm-reject-modal').css('display', 'flex');
        $('#mlm-reject-notes').val('');
    });

    // Cancel buttons
    $('#mlm-approve-cancel, #mlm-reject-cancel').on('click', function() {
        $('.mlm-modal').hide();
    });

    // Confirm approve
    $('#mlm-approve-confirm').on('click', function() {
        const notes = $('#mlm-approve-notes').val();
        const $btn = $(this);
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_approve_landing_page',
                nonce: thaipromptMLM.nonce,
                landing_id: currentLandingId,
                admin_notes: notes
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Confirm reject
    $('#mlm-reject-confirm').on('click', function() {
        const notes = $('#mlm-reject-notes').val().trim();

        if (!notes) {
            alert('Please provide a reason for rejection.');
            return;
        }

        const $btn = $(this);
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_reject_landing_page',
                nonce: thaipromptMLM.nonce,
                landing_id: currentLandingId,
                admin_notes: notes
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
