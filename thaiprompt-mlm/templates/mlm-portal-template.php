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
$referral_link = Thaiprompt_MLM_Referral::get_referral_link($user_id);
$referral_code = Thaiprompt_MLM_Referral::get_code($user_id);
$qr_code_url = Thaiprompt_MLM_Referral::get_qr_code_url($user_id);
$sponsor_info = Thaiprompt_MLM_Referral::get_sponsor_info($user_id);
$commissions = Thaiprompt_MLM_Database::get_user_commissions($user_id, array('limit' => 20));
$commission_stats = Thaiprompt_MLM_Commission::get_commission_summary($user_id);
$wallet = Thaiprompt_MLM_Wallet::get_balance($user_id);
$transactions = Thaiprompt_MLM_Wallet::get_transactions($user_id, array('limit' => 10));

// Get user's landing page
global $wpdb;
$landing_pages_table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';
$landing_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $landing_pages_table WHERE user_id = %d ORDER BY id DESC LIMIT 1", $user_id));

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
            <div class="mlm-header-container">
                <!-- Hamburger Menu Button (Mobile) -->
                <button class="mlm-hamburger" id="mlm-hamburger" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="mlm-header-left">
                    <?php if ($portal_logo): ?>
                        <img src="<?php echo esc_url($portal_logo); ?>" alt="<?php echo esc_attr($header_text); ?>" class="mlm-portal-logo">
                    <?php endif; ?>
                    <div class="mlm-header-text">
                        <h1 class="mlm-portal-title"><?php echo $portal_logo ? '' : '✨ '; ?><?php echo esc_html($header_text); ?></h1>
                        <p class="mlm-portal-subtitle">
                            <?php echo esc_html($subtitle_text); ?>
                        </p>
                    </div>
                </div>
                <div class="mlm-header-actions">
                    <a href="<?php echo home_url(); ?>" class="mlm-portal-btn mlm-btn-secondary">
                        <span class="mlm-btn-icon">←</span>
                        <span class="mlm-btn-text"><?php _e('Back', 'thaiprompt-mlm'); ?></span>
                    </a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="mlm-portal-btn mlm-btn-secondary">
                        <span class="mlm-btn-icon">🚪</span>
                        <span class="mlm-btn-text"><?php _e('Logout', 'thaiprompt-mlm'); ?></span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Portal Layout -->
        <div class="mlm-portal-layout">

            <!-- Mobile Menu Overlay -->
            <div class="mlm-menu-overlay" id="mlm-menu-overlay"></div>

            <!-- Sidebar Navigation -->
            <aside class="mlm-portal-sidebar" id="mlm-portal-sidebar">
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
                    <li class="mlm-portal-nav-item">
                        <a href="#landing" class="mlm-portal-nav-link" data-tab="landing">
                            <span class="mlm-portal-nav-icon">🎨</span>
                            <span><?php _e('My Landing Page', 'thaiprompt-mlm'); ?></span>
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

                    <!-- Sponsor Info -->
                    <?php if ($sponsor_info): ?>
                    <div class="mlm-glass-card" style="margin-bottom: 30px; padding: 25px;">
                        <h3 style="color: #fff; margin-bottom: 20px;">👤 <?php _e('My Sponsor', 'thaiprompt-mlm'); ?></h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 5px;"><?php _e('Name', 'thaiprompt-mlm'); ?></div>
                                <div style="color: #fff; font-size: 18px; font-weight: 600;"><?php echo esc_html($sponsor_info['name']); ?></div>
                            </div>
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 5px;"><?php _e('Referral Code', 'thaiprompt-mlm'); ?></div>
                                <div style="color: #fff; font-size: 18px; font-weight: 600; font-family: monospace;"><?php echo esc_html($sponsor_info['code']); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Referral Section -->
                    <div class="mlm-glass-card mlm-referral-box" style="margin-bottom: 30px; padding: 30px;">
                        <h3 style="color: #fff; margin-bottom: 25px; text-align: center;">🔗 <?php _e('Share Your Referral', 'thaiprompt-mlm'); ?></h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                            <!-- Referral Code -->
                            <div style="text-align: center;">
                                <div style="color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 10px;"><?php _e('Your Referral Code', 'thaiprompt-mlm'); ?></div>
                                <div style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3); border-radius: 15px; padding: 20px; margin-bottom: 15px;">
                                    <div style="color: #fff; font-size: 32px; font-weight: 800; font-family: monospace; letter-spacing: 3px;">
                                        <?php echo esc_html($referral_code); ?>
                                    </div>
                                </div>
                                <button class="mlm-portal-btn mlm-copy-code" data-code="<?php echo esc_attr($referral_code); ?>" style="width: 100%;">
                                    📋 <?php _e('Copy Code', 'thaiprompt-mlm'); ?>
                                </button>
                            </div>

                            <!-- QR Code -->
                            <div style="text-align: center;">
                                <div style="color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 10px;"><?php _e('QR Code', 'thaiprompt-mlm'); ?></div>
                                <div style="background: #fff; border-radius: 15px; padding: 15px; margin-bottom: 15px; display: inline-block;">
                                    <img src="<?php echo esc_url($qr_code_url); ?>" alt="QR Code" style="max-width: 200px; height: auto; display: block;">
                                </div>
                                <button class="mlm-portal-btn mlm-download-qr" data-qr="<?php echo esc_attr($qr_code_url); ?>" style="width: 100%; background: linear-gradient(135deg, #10b981, #059669);">
                                    ⬇️ <?php _e('Download QR', 'thaiprompt-mlm'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Referral Link -->
                        <div style="text-align: center;">
                            <div style="color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 10px;"><?php _e('Your Referral Link', 'thaiprompt-mlm'); ?></div>
                            <div style="display: flex; gap: 10px; max-width: 700px; margin: 0 auto;">
                                <input type="text" class="mlm-referral-input" value="<?php echo esc_attr($referral_link); ?>" readonly style="flex: 1; padding: 15px; border-radius: 50px; border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 14px;">
                                <button class="mlm-portal-btn mlm-copy-referral" data-link="<?php echo esc_attr($referral_link); ?>">
                                    📋 <?php _e('Copy Link', 'thaiprompt-mlm'); ?>
                                </button>
                            </div>
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
                        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <button class="mlm-portal-btn mlm-withdraw-btn">
                                <?php _e('Withdraw Funds', 'thaiprompt-mlm'); ?>
                            </button>
                            <?php if (class_exists('WooCommerce')): ?>
                            <button class="mlm-portal-btn" style="background: linear-gradient(135deg, #10b981, #059669);" onclick="document.getElementById('wallet-topup-section').scrollIntoView({behavior: 'smooth'});">
                                💳 <?php _e('Top-up Wallet', 'thaiprompt-mlm'); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Wallet Top-up (WooCommerce) -->
                    <?php if (class_exists('WooCommerce')): ?>
                    <div id="wallet-topup-section" class="mlm-glass-card" style="margin-bottom: 30px;">
                        <h3 style="color: #fff; margin-bottom: 20px;">💳 <?php _e('Top-up Wallet', 'thaiprompt-mlm'); ?></h3>
                        <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;">
                            <?php _e('เลือกจำนวนเงินที่ต้องการเติมเข้ากระเป๋าเงิน', 'thaiprompt-mlm'); ?>
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                            <?php
                            $topup_amounts = Thaiprompt_MLM_Wallet_Topup::get_topup_amounts();
                            foreach ($topup_amounts as $amount):
                                $topup_url = Thaiprompt_MLM_Wallet_Topup::get_topup_url($user_id, $amount);
                            ?>
                            <a href="<?php echo esc_url($topup_url); ?>"
                               class="mlm-topup-card"
                               style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
                                      border: 2px solid rgba(16, 185, 129, 0.3);
                                      border-radius: 12px;
                                      padding: 25px;
                                      text-align: center;
                                      text-decoration: none;
                                      transition: all 0.3s ease;
                                      display: block;">
                                <div style="font-size: 28px; margin-bottom: 10px;">💵</div>
                                <div style="color: #fff; font-size: 24px; font-weight: bold; margin-bottom: 5px;">
                                    <?php echo wc_price($amount); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.6); font-size: 13px;">
                                    <?php _e('Top-up', 'thaiprompt-mlm'); ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; border-radius: 8px;">
                            <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">
                                💡 <?php _e('เงินที่เติมจะเข้ากระเป๋าทันทีหลังชำระเงินสำเร็จ และไม่มีค่าธรรมเนียม', 'thaiprompt-mlm'); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

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

                <!-- Landing Page Builder Tab -->
                <div class="mlm-portal-tab-content" data-tab-content="landing">
                    <h2 style="color: #fff; margin-bottom: 30px; font-size: 32px;">
                        🎨 <?php _e('My Landing Page', 'thaiprompt-mlm'); ?>
                    </h2>

                    <?php if ($landing_page): ?>
                        <?php
                        $status_badges = array(
                            'pending' => array('color' => '#f59e0b', 'text' => 'Pending Approval'),
                            'approved' => array('color' => '#10b981', 'text' => 'Approved'),
                            'rejected' => array('color' => '#ef4444', 'text' => 'Rejected')
                        );
                        $current_status = $status_badges[$landing_page->status] ?? $status_badges['pending'];
                        $landing_url = add_query_arg('ref', $referral_code, home_url('landing/' . $landing_page->id));
                        ?>

                        <!-- Status Card -->
                        <div class="mlm-glass-card" style="margin-bottom: 30px; padding: 25px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <h3 style="color: #fff; margin-bottom: 10px;"><?php echo esc_html($landing_page->title); ?></h3>
                                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                        <span style="padding: 6px 15px; border-radius: 20px; background: <?php echo esc_attr($current_status['color']); ?>; color: #fff; font-size: 13px; font-weight: 600;">
                                            <?php echo esc_html($current_status['text']); ?>
                                        </span>
                                        <?php if ($landing_page->status === 'approved' && $landing_page->is_active): ?>
                                            <span style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                                👁️ <?php echo number_format($landing_page->views); ?> views | 🎯 <?php echo number_format($landing_page->conversions); ?> conversions
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <?php
                                    // Preview URL - accessible for any status
                                    $preview_url = add_query_arg(array(
                                        'ref' => $referral_code,
                                        'preview' => 'true'
                                    ), home_url('landing/' . $landing_page->id));
                                    ?>
                                    <a href="<?php echo esc_url($preview_url); ?>" target="_blank" class="mlm-portal-btn" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                        👁️ <?php _e('Preview', 'thaiprompt-mlm'); ?>
                                    </a>
                                    <?php if ($landing_page->status === 'approved' && $landing_page->is_active): ?>
                                        <a href="<?php echo esc_url($landing_url); ?>" target="_blank" class="mlm-portal-btn" style="background: linear-gradient(135deg, #10b981, #059669);">
                                            🌐 <?php _e('Live Page', 'thaiprompt-mlm'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <button class="mlm-portal-btn mlm-edit-landing-btn">
                                        ✏️ <?php _e('Edit', 'thaiprompt-mlm'); ?>
                                    </button>
                                </div>
                            </div>

                            <?php if ($landing_page->status === 'rejected' && $landing_page->admin_notes): ?>
                                <div style="margin-top: 20px; padding: 15px; background: rgba(239, 68, 68, 0.2); border-left: 4px solid #ef4444; border-radius: 8px;">
                                    <div style="color: #fff; font-weight: 600; margin-bottom: 5px;">❌ <?php _e('Rejection Reason:', 'thaiprompt-mlm'); ?></div>
                                    <div style="color: rgba(255,255,255,0.9);"><?php echo nl2br(esc_html($landing_page->admin_notes)); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Share Section (only for approved pages) -->
                        <?php if ($landing_page->status === 'approved' && $landing_page->is_active): ?>
                        <div class="mlm-glass-card" style="margin-bottom: 30px; padding: 25px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1));">
                            <h3 style="color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                🔗 <?php _e('Share Your Landing Page', 'thaiprompt-mlm'); ?>
                            </h3>

                            <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                                <label style="color: rgba(255,255,255,0.8); display: block; margin-bottom: 10px; font-size: 14px;">
                                    <?php _e('Your Landing Page URL:', 'thaiprompt-mlm'); ?>
                                </label>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <input type="text" readonly value="<?php echo esc_attr($landing_url); ?>" id="mlm-landing-url-input"
                                        style="flex: 1; min-width: 250px; padding: 12px 16px; background: rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.2); border-radius: 12px; color: #fff; font-size: 14px; font-family: monospace;">
                                    <button type="button" class="mlm-portal-btn mlm-copy-landing-url" data-url="<?php echo esc_attr($landing_url); ?>"
                                        style="background: linear-gradient(135deg, #10b981, #059669); white-space: nowrap;">
                                        📋 <?php _e('Copy URL', 'thaiprompt-mlm'); ?>
                                    </button>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
                                <button type="button" class="mlm-share-landing" data-platform="facebook" data-url="<?php echo esc_attr($landing_url); ?>"
                                    style="padding: 12px 16px; background: #1877f2; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    📘 Facebook
                                </button>
                                <button type="button" class="mlm-share-landing" data-platform="twitter" data-url="<?php echo esc_attr($landing_url); ?>"
                                    style="padding: 12px 16px; background: #1da1f2; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    🐦 Twitter
                                </button>
                                <button type="button" class="mlm-share-landing" data-platform="line" data-url="<?php echo esc_attr($landing_url); ?>"
                                    style="padding: 12px 16px; background: #00B900; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    💬 LINE
                                </button>
                                <button type="button" class="mlm-share-landing" data-platform="whatsapp" data-url="<?php echo esc_attr($landing_url); ?>"
                                    style="padding: 12px 16px; background: #25D366; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    📱 WhatsApp
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Preview Card -->
                        <div class="mlm-glass-card" style="margin-bottom: 30px;">
                            <h3 style="color: #fff; margin-bottom: 20px;">📱 <?php _e('Preview', 'thaiprompt-mlm'); ?></h3>
                            <div style="background: rgba(255,255,255,0.05); border-radius: 15px; padding: 30px;">
                                <h2 style="color: #fff; font-size: 28px; margin-bottom: 15px;"><?php echo esc_html($landing_page->headline); ?></h2>
                                <p style="color: rgba(255,255,255,0.8); font-size: 16px; line-height: 1.6; margin-bottom: 25px;"><?php echo nl2br(esc_html($landing_page->description)); ?></p>

                                <?php if ($landing_page->image1_url || $landing_page->image2_url || $landing_page->image3_url): ?>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                                        <?php if ($landing_page->image1_url): ?>
                                            <img src="<?php echo esc_url($landing_page->image1_url); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px;">
                                        <?php endif; ?>
                                        <?php if ($landing_page->image2_url): ?>
                                            <img src="<?php echo esc_url($landing_page->image2_url); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px;">
                                        <?php endif; ?>
                                        <?php if ($landing_page->image3_url): ?>
                                            <img src="<?php echo esc_url($landing_page->image3_url); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px;">
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <button style="padding: 15px 40px; background: linear-gradient(135deg, #8B5CF6, #7C3AED); color: #fff; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: not-allowed;">
                                    <?php echo esc_html($landing_page->cta_text); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Landing Page Form -->
                    <div class="mlm-glass-card mlm-landing-page-form" <?php echo $landing_page ? 'style="display: none;"' : ''; ?>>
                        <h3 style="color: #fff; margin-bottom: 25px;">
                            <?php echo $landing_page ? '✏️ ' . __('Edit Landing Page', 'thaiprompt-mlm') : '✨ ' . __('Create Landing Page', 'thaiprompt-mlm'); ?>
                        </h3>

                        <form id="mlm-landing-page-form" enctype="multipart/form-data">
                            <?php wp_nonce_field('mlm_save_landing_page', 'mlm_landing_nonce'); ?>
                            <input type="hidden" name="action" value="mlm_save_landing_page">
                            <input type="hidden" name="landing_id" value="<?php echo $landing_page ? $landing_page->id : ''; ?>">

                            <!-- Title -->
                            <div style="margin-bottom: 25px;">
                                <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 600;">
                                    <?php _e('Page Title', 'thaiprompt-mlm'); ?> <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" name="title" class="mlm-portal-input" value="<?php echo $landing_page ? esc_attr($landing_page->title) : ''; ?>" required
                                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; color: #fff; font-size: 15px;"
                                    placeholder="<?php _e('e.g., Join Our Team Today!', 'thaiprompt-mlm'); ?>">
                            </div>

                            <!-- Headline -->
                            <div style="margin-bottom: 25px;">
                                <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 600;">
                                    <?php _e('Headline', 'thaiprompt-mlm'); ?> <span style="color: #ef4444;">*</span>
                                </label>
                                <textarea name="headline" rows="2" class="mlm-portal-input" required
                                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; color: #fff; font-size: 15px; resize: vertical;"
                                    placeholder="<?php _e('Catchy headline here...', 'thaiprompt-mlm'); ?>"><?php echo $landing_page ? esc_textarea($landing_page->headline) : ''; ?></textarea>
                            </div>

                            <!-- Description -->
                            <div style="margin-bottom: 25px;">
                                <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 600;">
                                    <?php _e('Description', 'thaiprompt-mlm'); ?> <span style="color: #ef4444;">*</span>
                                </label>
                                <textarea name="description" rows="6" class="mlm-portal-input" required
                                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; color: #fff; font-size: 15px; resize: vertical;"
                                    placeholder="<?php _e('Tell your story and why someone should join...', 'thaiprompt-mlm'); ?>"><?php echo $landing_page ? esc_textarea($landing_page->description) : ''; ?></textarea>
                            </div>

                            <!-- Images -->
                            <div style="margin-bottom: 25px;">
                                <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 600;">
                                    <?php _e('Images (Max 3)', 'thaiprompt-mlm'); ?>
                                </label>
                                <p style="color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 12px;">
                                    <?php _e('Upload up to 3 images. Recommended size: 1200x600px', 'thaiprompt-mlm'); ?>
                                </p>

                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                        <div>
                                            <input type="file" name="image<?php echo $i; ?>" accept="image/*" class="mlm-image-upload"
                                                style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; color: #fff; font-size: 13px;">
                                            <?php if ($landing_page && $landing_page->{"image{$i}_url"}): ?>
                                                <div style="margin-top: 10px;">
                                                    <img src="<?php echo esc_url($landing_page->{"image{$i}_url"}); ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                                                    <label style="display: block; margin-top: 5px; color: rgba(255,255,255,0.7); font-size: 12px;">
                                                        <input type="checkbox" name="remove_image<?php echo $i; ?>" value="1">
                                                        <?php _e('Remove this image', 'thaiprompt-mlm'); ?>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- CTA Text -->
                            <div style="margin-bottom: 25px;">
                                <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 600;">
                                    <?php _e('Call-to-Action Button Text', 'thaiprompt-mlm'); ?>
                                </label>
                                <input type="text" name="cta_text" class="mlm-portal-input" value="<?php echo $landing_page ? esc_attr($landing_page->cta_text) : 'Join Now'; ?>"
                                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; color: #fff; font-size: 15px;"
                                    placeholder="<?php _e('e.g., Join Now, Get Started, Sign Up', 'thaiprompt-mlm'); ?>">
                            </div>

                            <!-- Notice -->
                            <div style="background: rgba(245, 158, 11, 0.2); border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                                <div style="color: #fff; font-size: 14px;">
                                    ⚠️ <?php _e('Your landing page will be submitted for admin approval before going live.', 'thaiprompt-mlm'); ?>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <button type="submit" class="mlm-portal-btn" style="flex: 1; min-width: 200px;">
                                    💾 <?php _e('Save & Submit for Approval', 'thaiprompt-mlm'); ?>
                                </button>
                                <?php if ($landing_page): ?>
                                    <button type="button" class="mlm-portal-btn mlm-cancel-edit-btn" style="background: rgba(255,255,255,0.2);">
                                        ✖️ <?php _e('Cancel', 'thaiprompt-mlm'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
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
