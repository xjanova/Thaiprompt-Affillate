<?php
/**
 * Network Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$referrals = isset($referrals) ? $referrals : Thaiprompt_MLM_Network::get_direct_referrals($user_id);
$referral_link = isset($referral_link) ? $referral_link : Thaiprompt_MLM_Network::get_referral_link($user_id);
$position = Thaiprompt_MLM_Network::get_user_position($user_id);
?>

<div class="mlm-network-page" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h2><?php _e('My Network', 'thaiprompt-mlm'); ?></h2>

    <!-- Network Position -->
    <div class="mlm-position-card" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <h3><?php _e('Your Position', 'thaiprompt-mlm'); ?></h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php if ($position['sponsor']): ?>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 10px;"><?php _e('Your Sponsor', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?php echo esc_html($position['sponsor']['name']); ?></div>
            </div>
            <?php endif; ?>

            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 10px;"><?php _e('Your Level', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?php echo esc_html($position['level']); ?></div>
            </div>

            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 10px;"><?php _e('Position', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?php echo esc_html(ucfirst($position['position'] ?? 'Root')); ?></div>
            </div>

            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 14px; color: #6c757d; margin-bottom: 10px;"><?php _e('Total Downline', 'thaiprompt-mlm'); ?></div>
                <div style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?php echo number_format($position['total_downline']); ?></div>
            </div>
        </div>
    </div>

    <!-- Referral Link -->
    <div class="mlm-referral-box">
        <h3 class="mlm-referral-title"><?php _e('Share Your Referral Link', 'thaiprompt-mlm'); ?></h3>
        <p style="text-align: center; margin-bottom: 20px; opacity: 0.9;">
            <?php _e('Invite others to join your network using this link', 'thaiprompt-mlm'); ?>
        </p>
        <div class="mlm-referral-link">
            <input type="text" class="mlm-referral-input" value="<?php echo esc_attr($referral_link); ?>" readonly />
            <button class="mlm-copy-button" data-clipboard-text="<?php echo esc_attr($referral_link); ?>">
                <?php _e('Copy', 'thaiprompt-mlm'); ?>
            </button>
        </div>
        <div class="mlm-share-buttons" style="margin-top: 15px; text-align: center;">
            <button class="mlm-share-btn" data-platform="facebook" style="background: #4267B2; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;">📘 Facebook</button>
            <button class="mlm-share-btn" data-platform="line" style="background: #00B900; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;">💬 LINE</button>
            <button class="mlm-share-btn" data-platform="twitter" style="background: #1DA1F2; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;">🐦 Twitter</button>
            <button class="mlm-share-btn" data-platform="whatsapp" style="background: #25D366; color: white; padding: 8px 15px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;">📱 WhatsApp</button>
        </div>
    </div>

    <!-- Genealogy Tree -->
    <div style="margin: 30px 0;">
        <?php echo do_shortcode('[mlm_genealogy]'); ?>
    </div>

    <!-- Direct Referrals -->
    <div class="mlm-referrals" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 30px;">
        <h3><?php _e('Direct Referrals', 'thaiprompt-mlm'); ?></h3>
        <?php if ($referrals && count($referrals) > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #2c3e50; color: white;">
                        <th style="padding: 15px; text-align: left;"><?php _e('Name', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: left;"><?php _e('Email', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: left;"><?php _e('Joined Date', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: left;"><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: right;"><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></th>
                        <th style="padding: 15px; text-align: right;"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin-right: 10px;">
                                    <?php echo esc_html(substr($referral['name'], 0, 1)); ?>
                                </div>
                                <strong><?php echo esc_html($referral['name']); ?></strong>
                            </div>
                        </td>
                        <td style="padding: 15px;"><?php echo esc_html($referral['email']); ?></td>
                        <td style="padding: 15px;">
                            <?php echo date_i18n(get_option('date_format'), strtotime($referral['joined_date'])); ?>
                        </td>
                        <td style="padding: 15px;">
                            <span style="background: #3498db; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                Level <?php echo esc_html($referral['level']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: 600;">
                            <?php echo wc_price($referral['personal_sales']); ?>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: 600;">
                            <?php echo wc_price($referral['group_sales']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 64px; margin-bottom: 20px;">👥</div>
            <h4 style="color: #2c3e50; margin-bottom: 10px;"><?php _e('No Referrals Yet', 'thaiprompt-mlm'); ?></h4>
            <p style="color: #7f8c8d; margin-bottom: 20px;">
                <?php _e('Start building your network by sharing your referral link!', 'thaiprompt-mlm'); ?>
            </p>
            <button onclick="document.querySelector('.mlm-copy-button').click()" style="background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <?php _e('Copy Referral Link', 'thaiprompt-mlm'); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>
