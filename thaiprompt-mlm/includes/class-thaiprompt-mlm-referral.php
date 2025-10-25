<?php
/**
 * Referral Code Management
 */

class Thaiprompt_MLM_Referral {

    /**
     * Generate unique referral code for user
     */
    public static function generate_referral_code($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Check if user already has a code
        $existing_code = get_user_meta($user_id, 'mlm_referral_code', true);
        if ($existing_code) {
            return $existing_code;
        }

        // Generate unique code
        $attempts = 0;
        do {
            // Create code from username + random string
            $username_part = strtoupper(substr(sanitize_title($user->user_login), 0, 4));
            $random_part = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $code = $username_part . $random_part;

            // Check if code exists
            $exists = self::code_exists($code);
            $attempts++;

            if ($attempts > 10) {
                // Fallback to pure random if can't find unique username-based code
                $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
                $exists = self::code_exists($code);
            }
        } while ($exists && $attempts < 20);

        if ($attempts >= 20) {
            return false; // Failed to generate unique code
        }

        // Save code
        update_user_meta($user_id, 'mlm_referral_code', $code);

        return $code;
    }

    /**
     * Check if referral code exists
     */
    public static function code_exists($code) {
        $users = get_users(array(
            'meta_key' => 'mlm_referral_code',
            'meta_value' => $code,
            'number' => 1
        ));

        return !empty($users);
    }

    /**
     * Get user ID from referral code
     */
    public static function get_user_from_code($code) {
        $code = strtoupper(sanitize_text_field($code));

        $users = get_users(array(
            'meta_key' => 'mlm_referral_code',
            'meta_value' => $code,
            'number' => 1
        ));

        return !empty($users) ? $users[0]->ID : false;
    }

    /**
     * Get referral link with code
     */
    public static function get_referral_link($user_id, $page_id = null) {
        $code = self::get_code($user_id);

        if (!$code) {
            $code = self::generate_referral_code($user_id);
        }

        if (!$page_id) {
            // Use landing page if available, otherwise home
            $page_id = get_option('thaiprompt_mlm_landing_page_id', home_url());
        }

        $url = is_numeric($page_id) ? get_permalink($page_id) : $page_id;

        return add_query_arg('ref', $code, $url);
    }

    /**
     * Get user's referral code
     */
    public static function get_code($user_id) {
        $code = get_user_meta($user_id, 'mlm_referral_code', true);

        if (!$code) {
            $code = self::generate_referral_code($user_id);
        }

        return $code;
    }

    /**
     * Get sponsor info from user
     */
    public static function get_sponsor_info($user_id) {
        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);

        if (!$network_data || !$network_data->sponsor_id) {
            return null;
        }

        $sponsor = get_userdata($network_data->sponsor_id);
        if (!$sponsor) {
            return null;
        }

        return array(
            'id' => $network_data->sponsor_id,
            'name' => $sponsor->display_name,
            'email' => $sponsor->user_email,
            'code' => self::get_code($network_data->sponsor_id)
        );
    }

    /**
     * Generate QR Code Data URL
     */
    public static function get_qr_code_url($user_id, $size = 300) {
        $referral_link = self::get_referral_link($user_id);

        // Use Google Charts API for QR Code generation
        $qr_url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chl=' . urlencode($referral_link) . '&choe=UTF-8';

        return $qr_url;
    }

    /**
     * Track referral visit
     */
    public static function track_referral() {
        if (!isset($_GET['ref'])) {
            return;
        }

        $code = sanitize_text_field($_GET['ref']);
        $sponsor_id = self::get_user_from_code($code);

        if ($sponsor_id) {
            // Store in session for registration
            if (!session_id()) {
                session_start();
            }
            $_SESSION['mlm_sponsor_code'] = $code;
            $_SESSION['mlm_sponsor_id'] = $sponsor_id;

            // Store in cookie as backup (30 days)
            setcookie('mlm_sponsor_code', $code, time() + (30 * 24 * 60 * 60), '/');
            setcookie('mlm_sponsor_id', $sponsor_id, time() + (30 * 24 * 60 * 60), '/');
        }
    }

    /**
     * Get stored sponsor from session/cookie
     */
    public static function get_stored_sponsor() {
        // Try session first
        if (!session_id()) {
            session_start();
        }

        if (isset($_SESSION['mlm_sponsor_id'])) {
            return $_SESSION['mlm_sponsor_id'];
        }

        // Fallback to cookie
        if (isset($_COOKIE['mlm_sponsor_id'])) {
            return intval($_COOKIE['mlm_sponsor_id']);
        }

        return null;
    }
}
