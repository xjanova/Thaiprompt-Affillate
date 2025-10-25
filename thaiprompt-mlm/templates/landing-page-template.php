<?php
/**
 * Template Name: MLM Landing Page
 *
 * This template displays approved landing pages created by MLM members
 */

// Get landing page ID from query
$landing_id = get_query_var('landing_id', 0);

if (!$landing_id) {
    wp_redirect(home_url());
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';

// Get landing page
$landing_page = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table WHERE id = %d AND status = 'approved' AND is_active = 1",
    $landing_id
));

if (!$landing_page) {
    wp_redirect(home_url());
    exit;
}

// Track view (increment views counter)
$wpdb->query($wpdb->prepare(
    "UPDATE $table SET views = views + 1 WHERE id = %d",
    $landing_id
));

// Get creator info
$creator = get_userdata($landing_page->user_id);
$referral_code = get_user_meta($landing_page->user_id, 'mlm_referral_code', true);
$referral_link = home_url('/mlm-portal/?ref=' . $referral_code);

// Store referral in session for conversion tracking
if (!session_id()) {
    session_start();
}
$_SESSION['mlm_landing_referral'] = $landing_page->user_id;
$_SESSION['mlm_landing_id'] = $landing_id;

// Set cookie for 30 days
setcookie('mlm_landing_referral', $landing_page->user_id, time() + (30 * 24 * 60 * 60), '/');
setcookie('mlm_landing_id', $landing_id, time() + (30 * 24 * 60 * 60), '/');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo esc_html($landing_page->title); ?> - <?php bloginfo('name'); ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo esc_attr(wp_trim_words($landing_page->description, 30)); ?>">
    <meta property="og:title" content="<?php echo esc_attr($landing_page->title); ?>">
    <meta property="og:description" content="<?php echo esc_attr(wp_trim_words($landing_page->description, 30)); ?>">
    <?php if ($landing_page->image1_url): ?>
    <meta property="og:image" content="<?php echo esc_url($landing_page->image1_url); ?>">
    <?php endif; ?>

    <?php wp_head(); ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .landing-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .landing-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
        }

        .landing-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .landing-headline {
            font-size: 24px;
            font-weight: 600;
            opacity: 0.95;
            line-height: 1.4;
        }

        .landing-images {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 0;
        }

        .landing-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .landing-content {
            padding: 40px 30px;
        }

        .landing-description {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 40px;
            white-space: pre-wrap;
        }

        .landing-cta-section {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .landing-cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 18px 50px;
            font-size: 20px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .landing-cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        .landing-footer {
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            color: #666;
            font-size: 14px;
        }

        .creator-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .creator-name {
            font-size: 16px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .landing-title {
                font-size: 28px;
            }

            .landing-headline {
                font-size: 18px;
            }

            .landing-description {
                font-size: 16px;
            }

            .landing-cta-button {
                width: 100%;
                padding: 16px 30px;
                font-size: 18px;
            }

            .landing-image {
                height: 200px;
            }

            .landing-header,
            .landing-content,
            .landing-cta-section {
                padding: 30px 20px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .landing-container > * {
            animation: fadeInUp 0.6s ease-out;
        }

        .landing-images {
            animation-delay: 0.2s;
        }

        .landing-content {
            animation-delay: 0.4s;
        }

        .landing-cta-section {
            animation-delay: 0.6s;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Header -->
        <div class="landing-header">
            <h1 class="landing-title"><?php echo esc_html($landing_page->title); ?></h1>
            <p class="landing-headline"><?php echo esc_html($landing_page->headline); ?></p>

            <div class="creator-info">
                <p class="creator-name">
                    <?php _e('Presented by:', 'thaiprompt-mlm'); ?>
                    <strong><?php echo esc_html($creator->display_name); ?></strong>
                </p>
            </div>
        </div>

        <!-- Images -->
        <?php
        $images = array();
        for ($i = 1; $i <= 3; $i++) {
            $img_field = 'image' . $i . '_url';
            if ($landing_page->$img_field) {
                $images[] = $landing_page->$img_field;
            }
        }

        if (!empty($images)):
        ?>
        <div class="landing-images">
            <?php foreach ($images as $index => $image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>"
                     alt="<?php echo esc_attr($landing_page->title . ' - Image ' . ($index + 1)); ?>"
                     class="landing-image">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="landing-content">
            <div class="landing-description">
                <?php echo esc_html($landing_page->description); ?>
            </div>
        </div>

        <!-- CTA Section - LINE Add Friend -->
        <div class="landing-cta-section">
            <?php
            $line_oa_id = get_option('thaiprompt_mlm_line_oa_id', '');
            if ($line_oa_id) {
                // Create LINE Add Friend URL with referral tracking
                $line_add_url = 'https://line.me/R/ti/p/' . $line_oa_id;
                $state_data = base64_encode(json_encode(array(
                    'referrer_id' => $landing_page->user_id,
                    'landing_id' => $landing_id,
                    'ref_code' => $referral_code
                )));
                ?>
                <a href="<?php echo esc_url($line_add_url); ?>"
                   class="landing-cta-button"
                   style="background: #06C755; display: inline-flex; align-items: center; gap: 12px; justify-content: center;"
                   data-state="<?php echo esc_attr($state_data); ?>">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" fill="white"/>
                    </svg>
                    <?php echo esc_html($landing_page->cta_text); ?>
                </a>
                <p style="margin-top: 15px; color: rgba(255,255,255,0.8); font-size: 14px;">
                    <?php _e('เพิ่มเพื่อนเพื่อสมัครสมาชิกผ่าน LINE OA', 'thaiprompt-mlm'); ?>
                </p>
            <?php else: ?>
                <p style="color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 20px; border-radius: 12px;">
                    ⚠️ <?php _e('กรุณาติดต่อผู้ดูแลระบบ - ยังไม่ได้ตั้งค่า LINE OA', 'thaiprompt-mlm'); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="landing-footer">
            <p>
                <?php printf(
                    __('Powered by %s', 'thaiprompt-mlm'),
                    '<strong>' . get_bloginfo('name') . '</strong>'
                ); ?>
            </p>
        </div>
    </div>

    <?php wp_footer(); ?>

    <script>
        // Track CTA click
        document.querySelector('.landing-cta-button').addEventListener('click', function() {
            // Send analytics event (optional)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    'event_category': 'Landing Page',
                    'event_label': 'CTA Click - <?php echo esc_js($landing_page->title); ?>'
                });
            }
        });
    </script>
</body>
</html>
