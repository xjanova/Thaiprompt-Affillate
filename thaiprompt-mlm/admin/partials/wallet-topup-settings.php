<?php
/**
 * Admin Wallet Top-up Settings Page
 *
 * Configure wallet top-up amounts and manage products
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['save_topup_settings']) && check_admin_referer('thaiprompt_mlm_topup_settings')) {
    $amounts = array();

    // Collect amounts from form
    if (isset($_POST['topup_amounts']) && is_array($_POST['topup_amounts'])) {
        foreach ($_POST['topup_amounts'] as $amount) {
            $amount = floatval($amount);
            if ($amount > 0) {
                $amounts[] = $amount;
            }
        }
    }

    // Save amounts
    $saved = Thaiprompt_MLM_Wallet_Topup::save_topup_amounts($amounts);

    echo '<div class="notice notice-success"><p>✅ ' . sprintf(__('Saved %d top-up amounts successfully!', 'thaiprompt-mlm'), count($saved)) . '</p></div>';

    Thaiprompt_MLM_Logger::info('Wallet top-up settings updated', array('amounts' => $saved));
}

// Handle create products action
if (isset($_POST['create_products']) && check_admin_referer('thaiprompt_mlm_topup_create')) {
    $created = Thaiprompt_MLM_Wallet_Topup::create_all_wallet_products();
    echo '<div class="notice notice-success"><p>✅ ' . sprintf(__('Created %d wallet products successfully!', 'thaiprompt-mlm'), count($created)) . '</p></div>';
}

// Get current settings
$amounts = Thaiprompt_MLM_Wallet_Topup::get_topup_amounts();
?>

<div class="wrap">
    <h1 style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 32px;">💳</span>
        <?php _e('Wallet Top-up Settings', 'thaiprompt-mlm'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Info Card -->
    <div class="mlm-dashboard-cards" style="margin: 20px 0;">
        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('WooCommerce Status', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon"><?php echo class_exists('WooCommerce') ? '✅' : '❌'; ?></span>
            </div>
            <div class="mlm-card-value" style="font-size: 16px;">
                <?php echo class_exists('WooCommerce') ? __('Active', 'thaiprompt-mlm') : __('Not Active', 'thaiprompt-mlm'); ?>
            </div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Top-up Amounts', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">💰</span>
            </div>
            <div class="mlm-card-value"><?php echo count($amounts); ?></div>
        </div>

        <div class="mlm-card">
            <div class="mlm-card-header">
                <span class="mlm-card-title"><?php _e('Total Products', 'thaiprompt-mlm'); ?></span>
                <span class="mlm-card-icon">📦</span>
            </div>
            <div class="mlm-card-value">
                <?php
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'private',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array('key' => '_mlm_wallet_topup', 'value' => 'yes')
                    )
                );
                $products = get_posts($args);
                echo count($products);
                ?>
            </div>
        </div>
    </div>

    <?php if (!class_exists('WooCommerce')): ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e('WooCommerce Required', 'thaiprompt-mlm'); ?></strong><br>
            <?php _e('Wallet top-up feature requires WooCommerce to be installed and activated.', 'thaiprompt-mlm'); ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Settings Form -->
    <form method="post" action="">
        <?php wp_nonce_field('thaiprompt_mlm_topup_settings'); ?>

        <div class="postbox">
            <div class="postbox-header">
                <h2>💰 <?php _e('Top-up Amounts Configuration', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">

                <p class="description" style="margin-bottom: 20px;">
                    <?php _e('Configure the predefined amounts that users can choose to top-up their wallet. Virtual products will be created automatically for each amount.', 'thaiprompt-mlm'); ?>
                </p>

                <div id="topup-amounts-list" style="max-width: 600px;">
                    <?php foreach ($amounts as $index => $amount): ?>
                    <div class="topup-amount-row" style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
                        <span style="color: #666; font-weight: 600; min-width: 30px;">#<?php echo $index + 1; ?></span>
                        <span style="color: #666; font-size: 18px;">฿</span>
                        <input type="number" name="topup_amounts[]" value="<?php echo esc_attr($amount); ?>" step="0.01" min="1" class="regular-text" placeholder="<?php _e('Amount', 'thaiprompt-mlm'); ?>" style="max-width: 200px;" required>
                        <button type="button" class="button remove-amount-btn" style="color: #dc3545;">
                            ❌ <?php _e('Remove', 'thaiprompt-mlm'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 20px;">
                    <button type="button" id="add-amount-btn" class="button button-secondary">
                        ➕ <?php _e('Add Amount', 'thaiprompt-mlm'); ?>
                    </button>
                </div>

                <hr style="margin: 30px 0;">

                <p class="submit">
                    <button type="submit" name="save_topup_settings" class="button button-primary button-large">
                        💾 <?php _e('Save Top-up Amounts', 'thaiprompt-mlm'); ?>
                    </button>
                </p>

                <div style="background: #f0f6fc; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-top: 20px;">
                    <strong>💡 <?php _e('How it works:', 'thaiprompt-mlm'); ?></strong>
                    <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                        <li><?php _e('Virtual products are created automatically for each amount', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Products are hidden from shop and search', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('When user completes payment, wallet is credited automatically', 'thaiprompt-mlm'); ?></li>
                        <li><?php _e('Top-up orders are excluded from commission calculations', 'thaiprompt-mlm'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

    <!-- Product Management -->
    <?php if (class_exists('WooCommerce')): ?>
    <form method="post" action="" style="margin-top: 20px;">
        <?php wp_nonce_field('thaiprompt_mlm_topup_create'); ?>

        <div class="postbox">
            <div class="postbox-header">
                <h2>📦 <?php _e('Product Management', 'thaiprompt-mlm'); ?></h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <p><?php _e('Manually create or recreate wallet products for all configured amounts.', 'thaiprompt-mlm'); ?></p>

                <button type="submit" name="create_products" class="button button-secondary">
                    🔄 <?php _e('Create/Update All Products', 'thaiprompt-mlm'); ?>
                </button>

                <hr style="margin: 30px 0;">

                <h3><?php _e('Existing Wallet Products', 'thaiprompt-mlm'); ?></h3>

                <?php if (!empty($products)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Product Name', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                            <th><?php _e('Actions', 'thaiprompt-mlm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product_post):
                            $product = wc_get_product($product_post->ID);
                            $amount = $product->get_meta('_mlm_topup_amount');
                        ?>
                        <tr>
                            <td><?php echo $product_post->ID; ?></td>
                            <td><?php echo esc_html($product->get_name()); ?></td>
                            <td><strong>฿<?php echo number_format($amount, 2); ?></strong></td>
                            <td><?php echo ucfirst($product_post->post_status); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($product_post->ID); ?>" class="button button-small">
                                    <?php _e('Edit', 'thaiprompt-mlm'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #666;"><?php _e('No wallet products found. Click "Create/Update All Products" to create them.', 'thaiprompt-mlm'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Add new amount field
    $('#add-amount-btn').on('click', function() {
        var index = $('#topup-amounts-list .topup-amount-row').length + 1;
        var row = '<div class="topup-amount-row" style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">' +
            '<span style="color: #666; font-weight: 600; min-width: 30px;">#' + index + '</span>' +
            '<span style="color: #666; font-size: 18px;">฿</span>' +
            '<input type="number" name="topup_amounts[]" value="" step="0.01" min="1" class="regular-text" placeholder="<?php _e('Amount', 'thaiprompt-mlm'); ?>" style="max-width: 200px;" required>' +
            '<button type="button" class="button remove-amount-btn" style="color: #dc3545;">❌ <?php _e('Remove', 'thaiprompt-mlm'); ?></button>' +
            '</div>';

        $('#topup-amounts-list').append(row);
        updateRowNumbers();
    });

    // Remove amount field
    $(document).on('click', '.remove-amount-btn', function() {
        if ($('#topup-amounts-list .topup-amount-row').length > 1) {
            $(this).closest('.topup-amount-row').remove();
            updateRowNumbers();
        } else {
            alert('<?php _e('You must have at least one top-up amount', 'thaiprompt-mlm'); ?>');
        }
    });

    // Update row numbers
    function updateRowNumbers() {
        $('#topup-amounts-list .topup-amount-row').each(function(index) {
            $(this).find('span').first().text('#' + (index + 1));
        });
    }
});
</script>

<style>
.mlm-dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.mlm-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.mlm-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.mlm-card-title {
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.mlm-card-icon {
    font-size: 24px;
}

.mlm-card-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.topup-amount-row:hover {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 4px;
    margin-left: -10px;
    margin-right: -10px;
}
</style>
