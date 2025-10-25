<?php
/**
 * Template Name: MLM Portal
 * Description: Full-width MLM Portal template with modern purple theme
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Get Portal Settings
$settings = get_option('thaiprompt_mlm_settings', array());
$portal_logo = get_option('thaiprompt_mlm_portal_logo', '');
$slideshow_images = get_option('thaiprompt_mlm_portal_slideshow', array());
$header_text = isset($settings['portal_header_text']) ? $settings['portal_header_text'] : 'MLM Portal';
$subtitle_text = isset($settings['portal_subtitle_text']) ? $settings['portal_subtitle_text'] : 'Welcome back, {name}!';
$slideshow_enabled = isset($settings['portal_slideshow_enabled']) && $settings['portal_slideshow_enabled'];
$slideshow_speed = isset($settings['portal_slideshow_speed']) ? intval($settings['portal_slideshow_speed']) : 5;

// Replace {name} with user's display name
$subtitle_text = str_replace('{name}', $user->display_name, $subtitle_text);

// Get MLM data
$position = Thaiprompt_MLM_Network::get_user_position($user_id);
$team_stats = Thaiprompt_MLM_Network::get_team_stats($user_id);
$wallet_stats = Thaiprompt_MLM_Wallet::get_wallet_stats($user_id);
$rank = Thaiprompt_MLM_Database::get_user_rank($user_id);
$rank_progress = Thaiprompt_MLM_Rank::get_rank_progress($user_id);
$referrals = Thaiprompt_MLM_Network::get_direct_referrals($user_id);
$referral_link = Thaiprompt_MLM_Network::get_referral_link($user_id);
$commissions = Thaiprompt_MLM_Database::get_user_commissions($user_id, array('limit' => 20));
$commission_stats = Thaiprompt_MLM_Commission::get_commission_summary($user_id);
$wallet = Thaiprompt_MLM_Wallet::get_balance($user_id);
$transactions = Thaiprompt_MLM_Wallet::get_transactions($user_id, array('limit' => 10));

// Enqueue portal assets
wp_enqueue_style('thaiprompt-mlm-portal', THAIPROMPT_MLM_PLUGIN_URL . 'public/css/thaiprompt-mlm-portal.css', array(), THAIPROMPT_MLM_VERSION);
wp_enqueue_script('thaiprompt-mlm-portal', THAIPROMPT_MLM_PLUGIN_URL . 'public/js/thaiprompt-mlm-portal.js', array('jquery'), THAIPROMPT_MLM_VERSION, true);

// Localize script for AJAX
wp_localize_script('thaiprompt-mlm-portal', 'thaipromptMLM', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('thaiprompt_mlm_public_nonce'),
    'user_id' => $user_id
));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('MLM Portal', 'thaiprompt-mlm'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('mlm-portal-page'); ?>>

<div class="mlm-portal-wrapper">
    <div class="mlm-portal-container">

        <!-- Portal Header -->
        <header class="mlm-portal-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <?php if ($portal_logo): ?>
                        <img src="<?php echo esc_url($portal_logo); ?>" alt="<?php echo esc_attr($header_text); ?>" style="max-height: 60px; width: auto;">
                    <?php endif; ?>
                    <div>
                        <h1 class="mlm-portal-title"><?php echo $portal_logo ? '' : '✨ '; ?><?php echo esc_html($header_text); ?></h1>
                        <p class="mlm-portal-subtitle">
                            <?php echo esc_html($subtitle_text); ?>
                        </p>
                    </div>
                </div>
                <div>
                    <a href="<?php echo home_url(); ?>" class="mlm-portal-btn" style="background: rgba(255,255,255,0.2);">
                        ← <?php _e('Back to Site', 'thaiprompt-mlm'); ?>
                    </a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="mlm-portal-btn" style="background: rgba(255,255,255,0.2); margin-left: 10px;">
                        <?php _e('Logout', 'thaiprompt-mlm'); ?>
                    </a>
                </div>
            </div>
        </header>

        <!-- Portal Layout -->
        <div class="mlm-portal-layout">

            <!-- Sidebar Navigation -->
            <aside class="mlm-portal-sidebar">
                <ul class="mlm-portal-nav">
                    <li class="mlm-portal-nav-item">
                        <a href="#dashboard" class="mlm-portal-nav-link active" data-tab="dashboard">
                            <span class="mlm-portal-nav-icon">📊</span>
                            <span><?php _e('Dashboard', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                    <li class="mlm-portal-nav-item">
                        <a href="#genealogy" class="mlm-portal-nav-link" data-tab="genealogy">
                            <span class="mlm-portal-nav-icon">🌳</span>
                            <span><?php _e('Genealogy', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                    <li class="mlm-portal-nav-item">
                        <a href="#network" class="mlm-portal-nav-link" data-tab="network">
                            <span class="mlm-portal-nav-icon">👥</span>
                            <span><?php _e('My Network', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                    <li class="mlm-portal-nav-item">
                        <a href="#wallet" class="mlm-portal-nav-link" data-tab="wallet">
                            <span class="mlm-portal-nav-icon">💰</span>
                            <span><?php _e('Wallet', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                    <li class="mlm-portal-nav-item">
                        <a href="#commissions" class="mlm-portal-nav-link" data-tab="commissions">
                            <span class="mlm-portal-nav-icon">💵</span>
                            <span><?php _e('Commissions', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                    <li class="mlm-portal-nav-item">
                        <a href="#rank" class="mlm-portal-nav-link" data-tab="rank">
                            <span class="mlm-portal-nav-icon">🏆</span>
                            <span><?php _e('Rank Progress', 'thaiprompt-mlm'); ?></span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <main class="mlm-portal-main">

                <!-- Dashboard Tab -->
                <div class="mlm-portal-tab-content active" data-tab-content="dashboard">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        📊 <?php _e('Dashboard Overview', 'thaiprompt-mlm'); ?>
                    </h2>

                    <!-- Portal Slideshow -->
                    <?php if ($slideshow_enabled && !empty($slideshow_images)): ?>
                    <div class="mlm-portal-slideshow" style="margin-bottom: 30px;">
                        <div class="mlm-slideshow-container">
                            <?php foreach ($slideshow_images as $index => $image): ?>
                                <div class="mlm-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo esc_url($image); ?>" alt="Slide <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($slideshow_images) > 1): ?>
                                <button class="mlm-slide-prev">❮</button>
                                <button class="mlm-slide-next">❯</button>

                                <div class="mlm-slide-dots">
                                    <?php foreach ($slideshow_images as $index => $image): ?>
                                        <span class="mlm-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <script>
                    // Slideshow functionality
                    (function() {
                        let currentSlide = 0;
                        const slides = document.querySelectorAll('.mlm-slide');
                        const dots = document.querySelectorAll('.mlm-dot');
                        const slideSpeed = <?php echo $slideshow_speed * 1000; ?>;

                        function showSlide(n) {
                            slides.forEach(s => s.classList.remove('active'));
                            dots.forEach(d => d.classList.remove('active'));

                            currentSlide = (n + slides.length) % slides.length;
                            slides[currentSlide].classList.add('active');
                            if (dots[currentSlide]) dots[currentSlide].classList.add('active');
                        }

                        function nextSlide() {
                            showSlide(currentSlide + 1);
                        }

                        function prevSlide() {
                            showSlide(currentSlide - 1);
                        }

                        // Auto advance
                        setInterval(nextSlide, slideSpeed);

                        // Navigation
                        const prevBtn = document.querySelector('.mlm-slide-prev');
                        const nextBtn = document.querySelector('.mlm-slide-next');
                        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
                        if (nextBtn) nextBtn.addEventListener('click', nextSlide);

                        // Dots
                        dots.forEach((dot, index) => {
                            dot.addEventListener('click', () => showSlide(index));
                        });
                    })();
                    </script>
                    <?php endif; ?>

                    <!-- Stats Grid -->
                    <div class="mlm-portal-stats">
                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">💰</div>
                            <div class="mlm-stat-value"><?php echo number_format($wallet_stats['balance'], 2); ?></div>
                            <div class="mlm-stat-label"><?php _e('Available Balance', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">💵</div>
                            <div class="mlm-stat-value"><?php echo number_format($wallet_stats['total_earned'], 2); ?></div>
                            <div class="mlm-stat-label"><?php _e('Total Earned', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">👥</div>
                            <div class="mlm-stat-value"><?php echo number_format($team_stats['total_team']); ?></div>
                            <div class="mlm-stat-label"><?php _e('Team Members', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">📈</div>
                            <div class="mlm-stat-value"><?php echo number_format($team_stats['total_sales'], 2); ?></div>
                            <div class="mlm-stat-label"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></div>
                        </div>
                    </div>

                    <!-- Current Rank -->
                    <?php if ($rank): ?>
                    <div class="mlm-glass-card" style="margin-bottom: 30px;">
                        <h3 style="color: #fff; margin-bottom: 20px;">🏆 <?php _e('Your Current Rank', 'thaiprompt-mlm'); ?></h3>
                        <div style="text-align: center; padding: 20px;">
                            <div style="display: inline-block; padding: 15px 40px; border-radius: 50px; background: <?php echo esc_attr($rank->rank_color); ?>; color: #fff; font-size: 24px; font-weight: 800; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                <?php echo esc_html($rank->rank_name); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="mlm-glass-card">
                        <h3 style="color: #fff; margin-bottom: 20px;">⚡ <?php _e('Quick Actions', 'thaiprompt-mlm'); ?></h3>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <a href="#wallet" class="mlm-portal-btn" onclick="jQuery('.mlm-portal-nav-link[data-tab=wallet]').click(); return false;">
                                <?php _e('Withdraw Funds', 'thaiprompt-mlm'); ?>
                            </a>
                            <a href="#network" class="mlm-portal-btn" onclick="jQuery('.mlm-portal-nav-link[data-tab=network]').click(); return false;" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <?php _e('Share Referral Link', 'thaiprompt-mlm'); ?>
                            </a>
                            <a href="#genealogy" class="mlm-portal-btn" onclick="jQuery('.mlm-portal-nav-link[data-tab=genealogy]').click(); return false;" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                <?php _e('View Team Tree', 'thaiprompt-mlm'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Genealogy Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="genealogy">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        🌳 <?php _e('Genealogy Tree', 'thaiprompt-mlm'); ?>
                    </h2>

                    <div class="mlm-glass-card" style="margin-bottom: 30px; padding: 20px;">
                        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                            <label style="color: #fff;">
                                <?php _e('View Tree:', 'thaiprompt-mlm'); ?>
                            </label>
                            <select id="mlm-genealogy-user" class="mlm-portal-input" style="flex: 1; max-width: 300px;">
                                <option value="<?php echo $user_id; ?>"><?php echo esc_html($user->display_name); ?> (<?php _e('Me', 'thaiprompt-mlm'); ?>)</option>
                                <?php if ($position && $position['sponsor_id']): ?>
                                    <?php $sponsor = get_userdata($position['sponsor_id']); ?>
                                    <option value="<?php echo $position['sponsor_id']; ?>">
                                        <?php echo $sponsor ? esc_html($sponsor->display_name) : 'Sponsor'; ?> (<?php _e('My Sponsor', 'thaiprompt-mlm'); ?>)
                                    </option>
                                <?php endif; ?>
                            </select>
                            <select id="mlm-genealogy-depth" class="mlm-portal-input" style="max-width: 150px;">
                                <option value="3"><?php _e('3 Levels', 'thaiprompt-mlm'); ?></option>
                                <option value="5" selected><?php _e('5 Levels', 'thaiprompt-mlm'); ?></option>
                                <option value="7"><?php _e('7 Levels', 'thaiprompt-mlm'); ?></option>
                                <option value="10"><?php _e('10 Levels', 'thaiprompt-mlm'); ?></option>
                            </select>
                            <button id="mlm-genealogy-refresh" class="mlm-portal-btn" style="background: linear-gradient(135deg, #10b981, #059669);">
                                🔄 <?php _e('Refresh', 'thaiprompt-mlm'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="mlm-glass-card">
                        <div id="mlm-genealogy-loading" style="text-align: center; padding: 60px; display: none;">
                            <div style="font-size: 48px; margin-bottom: 20px;">⏳</div>
                            <p style="color: rgba(255,255,255,0.7);"><?php _e('Loading genealogy tree...', 'thaiprompt-mlm'); ?></p>
                        </div>
                        <div id="mlm-genealogy-container" style="overflow-x: auto; padding: 40px 20px; min-height: 400px;">
                            <!-- Genealogy tree will be rendered here -->
                        </div>
                    </div>
                </div>

                <!-- Network Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="network">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        👥 <?php _e('My Network', 'thaiprompt-mlm'); ?>
                    </h2>

                    <!-- Referral Link -->
                    <div class="mlm-glass-card mlm-referral-box" style="text-align: center; margin-bottom: 30px;">
                        <h3 style="color: #fff; margin-bottom: 15px;">🔗 <?php _e('Your Referral Link', 'thaiprompt-mlm'); ?></h3>
                        <div style="display: flex; gap: 10px; max-width: 600px; margin: 20px auto;">
                            <input type="text" class="mlm-referral-input" value="<?php echo esc_attr($referral_link); ?>" readonly style="flex: 1; padding: 15px; border-radius: 50px; border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 16px;">
                            <button class="mlm-portal-btn mlm-copy-referral" data-link="<?php echo esc_attr($referral_link); ?>">
                                📋 <?php _e('Copy', 'thaiprompt-mlm'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Team Stats -->
                    <div class="mlm-portal-stats">
                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">👈</div>
                            <div class="mlm-stat-value"><?php echo wc_price($team_stats['left_leg_sales']); ?></div>
                            <div class="mlm-stat-label"><?php _e('Left Leg Sales', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">👉</div>
                            <div class="mlm-stat-value"><?php echo wc_price($team_stats['right_leg_sales']); ?></div>
                            <div class="mlm-stat-label"><?php _e('Right Leg Sales', 'thaiprompt-mlm'); ?></div>
                        </div>
                    </div>

                    <!-- Direct Referrals -->
                    <div class="mlm-glass-card" style="margin-top: 30px;">
                        <h3 style="color: #fff; margin-bottom: 20px;">👤 <?php _e('Direct Referrals', 'thaiprompt-mlm'); ?></h3>
                        <?php if ($referrals && count($referrals) > 0): ?>
                            <table class="mlm-portal-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Name', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Joined', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Group Sales', 'thaiprompt-mlm'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($referrals as $referral): ?>
                                    <tr>
                                        <td><?php echo esc_html($referral['name']); ?></td>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($referral['joined_date'])); ?></td>
                                        <td><?php echo wc_price($referral['personal_sales']); ?></td>
                                        <td><?php echo wc_price($referral['group_sales']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: rgba(255,255,255,0.7); text-align: center; padding: 40px;">
                                <?php _e('No referrals yet. Share your link to get started!', 'thaiprompt-mlm'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Wallet Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="wallet">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        💰 <?php _e('My Wallet', 'thaiprompt-mlm'); ?>
                    </h2>

                    <!-- Wallet Balance -->
                    <div class="mlm-glass-card" style="text-align: center; margin-bottom: 30px; padding: 50px;">
                        <div class="mlm-stat-label" style="margin-bottom: 15px;"><?php _e('Available Balance', 'thaiprompt-mlm'); ?></div>
                        <div class="mlm-stat-value mlm-wallet-balance" style="font-size: 56px; margin-bottom: 20px;">
                            <?php echo wc_price($wallet->balance ?? 0); ?>
                        </div>
                        <div style="color: rgba(255,255,255,0.7); margin-bottom: 30px;">
                            <?php printf(__('Pending: %s', 'thaiprompt-mlm'), wc_price($wallet->pending_balance ?? 0)); ?>
                        </div>
                        <button class="mlm-portal-btn mlm-withdraw-btn">
                            <?php _e('Withdraw Funds', 'thaiprompt-mlm'); ?>
                        </button>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="mlm-glass-card">
                        <h3 style="color: #fff; margin-bottom: 20px;">📜 <?php _e('Recent Transactions', 'thaiprompt-mlm'); ?></h3>
                        <?php if ($transactions && count($transactions) > 0): ?>
                            <table class="mlm-portal-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Date', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo esc_html($transaction->transaction_type); ?></td>
                                        <td style="color: <?php echo $transaction->amount >= 0 ? '#10b981' : '#ef4444'; ?>;">
                                            <?php echo ($transaction->amount >= 0 ? '+' : '') . wc_price(abs($transaction->amount)); ?>
                                        </td>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($transaction->created_at)); ?></td>
                                        <td>
                                            <span class="mlm-badge mlm-badge-<?php echo $transaction->status === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo esc_html(ucfirst($transaction->status)); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: rgba(255,255,255,0.7); text-align: center; padding: 40px;">
                                <?php _e('No transactions yet', 'thaiprompt-mlm'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Commissions Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="commissions">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        💵 <?php _e('Commission History', 'thaiprompt-mlm'); ?>
                    </h2>

                    <!-- Commission Stats -->
                    <div class="mlm-portal-stats">
                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">💰</div>
                            <div class="mlm-stat-value"><?php echo number_format($commission_stats['total_earned'], 2); ?></div>
                            <div class="mlm-stat-label"><?php _e('Total Earned', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">⏳</div>
                            <div class="mlm-stat-value"><?php echo number_format($commission_stats['pending'], 2); ?></div>
                            <div class="mlm-stat-label"><?php _e('Pending', 'thaiprompt-mlm'); ?></div>
                        </div>

                        <div class="mlm-stat-card">
                            <div class="mlm-stat-icon">📊</div>
                            <div class="mlm-stat-value"><?php echo number_format($commission_stats['total_transactions']); ?></div>
                            <div class="mlm-stat-label"><?php _e('Total Transactions', 'thaiprompt-mlm'); ?></div>
                        </div>
                    </div>

                    <!-- Commission List -->
                    <div class="mlm-glass-card" style="margin-top: 30px;">
                        <h3 style="color: #fff; margin-bottom: 20px;">📜 <?php _e('Recent Commissions', 'thaiprompt-mlm'); ?></h3>
                        <?php if ($commissions && count($commissions) > 0): ?>
                            <table class="mlm-portal-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Type', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Level', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Date', 'thaiprompt-mlm'); ?></th>
                                        <th><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commissions as $commission): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($commission->commission_type); ?></code></td>
                                        <td><strong><?php echo wc_price($commission->amount); ?></strong></td>
                                        <td><?php echo $commission->level ? 'L' . $commission->level : '-'; ?></td>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($commission->created_at)); ?></td>
                                        <td>
                                            <span class="mlm-badge mlm-badge-<?php echo $commission->status === 'approved' ? 'success' : ($commission->status === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo esc_html(ucfirst($commission->status)); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: rgba(255,255,255,0.7); text-align: center; padding: 40px;">
                                <?php _e('No commissions yet', 'thaiprompt-mlm'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rank Progress Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="rank">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        🏆 <?php _e('Rank Progress', 'thaiprompt-mlm'); ?>
                    </h2>

                    <?php if ($rank_progress['next_rank']): ?>
                    <div class="mlm-glass-card">
                        <div style="text-align: center; margin-bottom: 40px;">
                            <h3 style="color: #fff; margin-bottom: 20px;"><?php _e('Current Rank', 'thaiprompt-mlm'); ?></h3>
                            <div style="display: inline-block; padding: 15px 40px; border-radius: 50px; background: <?php echo esc_attr($rank->rank_color); ?>; color: #fff; font-size: 28px; font-weight: 800;">
                                <?php echo esc_html($rank->rank_name); ?>
                            </div>
                        </div>

                        <div style="text-align: center; margin: 40px 0;">
                            <div style="font-size: 48px; margin-bottom: 10px;">⬇️</div>
                            <h4 style="color: #fff;"><?php printf(__('Next: %s', 'thaiprompt-mlm'), $rank_progress['next_rank']['name']); ?></h4>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <div class="mlm-progress-bar">
                                <div class="mlm-progress-fill" data-progress="<?php echo $rank_progress['progress']; ?>" style="display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;">
                                    <?php echo round($rank_progress['progress']); ?>%
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div class="mlm-glass-card" style="text-align: center;">
                                <div style="color: rgba(255,255,255,0.7); margin-bottom: 10px;"><?php _e('Personal Sales', 'thaiprompt-mlm'); ?></div>
                                <div style="color: #fff; font-size: 20px; font-weight: 700;">
                                    <?php echo wc_price($rank_progress['requirements_met']['personal_sales']['current']); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 5px;">
                                    / <?php echo wc_price($rank_progress['requirements_met']['personal_sales']['required']); ?>
                                </div>
                            </div>

                            <div class="mlm-glass-card" style="text-align: center;">
                                <div style="color: rgba(255,255,255,0.7); margin-bottom: 10px;"><?php _e('Group Sales', 'thaiprompt-mlm'); ?></div>
                                <div style="color: #fff; font-size: 20px; font-weight: 700;">
                                    <?php echo wc_price($rank_progress['requirements_met']['group_sales']['current']); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 5px;">
                                    / <?php echo wc_price($rank_progress['requirements_met']['group_sales']['required']); ?>
                                </div>
                            </div>

                            <div class="mlm-glass-card" style="text-align: center;">
                                <div style="color: rgba(255,255,255,0.7); margin-bottom: 10px;"><?php _e('Active Legs', 'thaiprompt-mlm'); ?></div>
                                <div style="color: #fff; font-size: 20px; font-weight: 700;">
                                    <?php echo number_format($rank_progress['requirements_met']['active_legs']['current']); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 5px;">
                                    / <?php echo number_format($rank_progress['requirements_met']['active_legs']['required']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mlm-glass-card" style="text-align: center; padding: 60px;">
                        <div style="font-size: 72px; margin-bottom: 20px;">🎉</div>
                        <h3 style="color: #fff; font-size: 32px; margin-bottom: 15px;"><?php _e('Congratulations!', 'thaiprompt-mlm'); ?></h3>
                        <p style="color: rgba(255,255,255,0.8); font-size: 18px;">
                            <?php _e('You have reached the highest rank!', 'thaiprompt-mlm'); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>
</div>

<script>
// Vanilla JavaScript fallback for tab navigation
(function() {
    'use strict';

    console.log('MLM Portal: Vanilla JS tab navigation loaded');

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPortal);
    } else {
        initPortal();
    }

    function initPortal() {
        console.log('MLM Portal: Initializing vanilla JS tabs');

        const navLinks = document.querySelectorAll('.mlm-portal-nav-link');
        const tabContents = document.querySelectorAll('.mlm-portal-tab-content');

        console.log('Found', navLinks.length, 'nav links and', tabContents.length, 'tab contents');

        navLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const tab = this.getAttribute('data-tab');
                console.log('Tab clicked:', tab);

                // Remove active from all links
                navLinks.forEach(function(l) {
                    l.classList.remove('active');
                });

                // Add active to clicked link
                this.classList.add('active');

                // Hide all tab contents
                tabContents.forEach(function(content) {
                    content.style.display = 'none';
                    content.classList.remove('active');
                });

                // Show selected tab content
                const selectedTab = document.querySelector('[data-tab-content="' + tab + '"]');
                if (selectedTab) {
                    selectedTab.style.display = 'block';
                    selectedTab.classList.add('active');

                    // Scroll to top
                    const mainContent = document.querySelector('.mlm-portal-main');
                    if (mainContent) {
                        mainContent.scrollTop = 0;
                    }
                }
            });
        });
    }
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
