<?php
/**
 * LINE Webhook Handler
 *
 * Handles incoming messages and events from LINE
 */

class Thaiprompt_MLM_LINE_Webhook {

    /**
     * Initialize webhook routes
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        register_rest_route('thaiprompt-mlm/v1', '/line-webhook', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_webhook'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Handle incoming webhook
     */
    public static function handle_webhook($request) {
        // Check if webhook is enabled
        if (!get_option('thaiprompt_mlm_line_webhook_enabled', 0)) {
            Thaiprompt_MLM_Logger::warning('LINE webhook received but disabled');
            return new WP_REST_Response(array('status' => 'disabled'), 200);
        }

        // Get request body and signature
        $body = $request->get_body();
        $signature = $request->get_header('x-line-signature');

        // Validate signature
        if (!Thaiprompt_MLM_LINE_Bot::validate_signature($body, $signature)) {
            Thaiprompt_MLM_Logger::error('LINE webhook signature validation failed');
            return new WP_REST_Response(array('error' => 'Invalid signature'), 403);
        }

        // Parse events
        $data = json_decode($body, true);
        $events = isset($data['events']) ? $data['events'] : array();

        Thaiprompt_MLM_Logger::info('LINE webhook received', array(
            'event_count' => count($events)
        ));

        // Process each event
        foreach ($events as $event) {
            self::process_event($event);
        }

        return new WP_REST_Response(array('status' => 'ok'), 200);
    }

    /**
     * Process individual event
     */
    private static function process_event($event) {
        $type = isset($event['type']) ? $event['type'] : '';

        switch ($type) {
            case 'message':
                self::handle_message_event($event);
                break;

            case 'follow':
                self::handle_follow_event($event);
                break;

            case 'unfollow':
                self::handle_unfollow_event($event);
                break;

            case 'join':
                self::handle_join_event($event);
                break;

            case 'postback':
                self::handle_postback_event($event);
                break;

            default:
                Thaiprompt_MLM_Logger::debug('Unknown LINE event type', array('type' => $type));
        }
    }

    /**
     * Handle message event
     */
    private static function handle_message_event($event) {
        $user_id = $event['source']['userId'];
        $message = $event['message'];
        $reply_token = $event['replyToken'];
        $message_type = $message['type'];

        Thaiprompt_MLM_Logger::info('LINE message received', array(
            'user_id' => $user_id,
            'type' => $message_type
        ));

        // Get or create WP user
        $wp_user = self::get_or_create_user($user_id);

        if (!$wp_user) {
            Thaiprompt_MLM_Logger::error('Failed to get/create user', array('line_user_id' => $user_id));
            return;
        }

        // Handle text messages
        if ($message_type === 'text') {
            $text = $message['text'];

            // Check for commands
            if (self::is_command($text)) {
                self::handle_command($text, $reply_token, $user_id, $wp_user);
            } else {
                // Regular conversation - pass to AI or default response
                self::handle_conversation($text, $reply_token, $user_id, $wp_user);
            }
        }
    }

    /**
     * Handle follow event (Add Friend)
     */
    private static function handle_follow_event($event) {
        $user_id = $event['source']['userId'];

        Thaiprompt_MLM_Logger::info('LINE user followed', array('user_id' => $user_id));

        // Auto registration if enabled
        if (get_option('thaiprompt_mlm_line_auto_register', 1)) {
            $wp_user = self::register_user_from_line($user_id);

            if ($wp_user) {
                // Send welcome message
                $welcome_msg = get_option('thaiprompt_mlm_line_welcome_message', 'สวัสดีครับ! ยินดีต้อนรับสู่ระบบ MLM 🎉');

                $messages = array(
                    Thaiprompt_MLM_LINE_Bot::build_text_message($welcome_msg),
                    Thaiprompt_MLM_LINE_Bot::build_text_message('คุณได้ลงทะเบียนเรียบร้อยแล้ว! ✅')
                );

                Thaiprompt_MLM_LINE_Bot::push_message($user_id, $messages);
            }
        }
    }

    /**
     * Handle unfollow event
     */
    private static function handle_unfollow_event($event) {
        $user_id = $event['source']['userId'];

        Thaiprompt_MLM_Logger::info('LINE user unfollowed', array('user_id' => $user_id));

        // Update user meta
        $wp_user_id = self::get_wp_user_by_line_id($user_id);
        if ($wp_user_id) {
            update_user_meta($wp_user_id, 'line_following', 0);
        }
    }

    /**
     * Handle join event (group/room)
     */
    private static function handle_join_event($event) {
        $reply_token = $event['replyToken'];

        Thaiprompt_MLM_Logger::info('LINE bot joined group/room');

        // Send greeting
        $message = Thaiprompt_MLM_LINE_Bot::build_text_message('สวัสดีครับทุกคน! 👋');
        Thaiprompt_MLM_LINE_Bot::reply_message($reply_token, $message);
    }

    /**
     * Handle postback event
     */
    private static function handle_postback_event($event) {
        $user_id = $event['source']['userId'];
        $data = $event['postback']['data'];
        $reply_token = $event['replyToken'];

        Thaiprompt_MLM_Logger::info('LINE postback received', array(
            'user_id' => $user_id,
            'data' => $data
        ));

        // Parse postback data
        parse_str($data, $params);

        // Handle different actions
        $action = isset($params['action']) ? $params['action'] : '';

        switch ($action) {
            case 'register':
                self::handle_registration_confirm($reply_token, $user_id, $params);
                break;

            case 'profile':
                self::send_user_profile($reply_token, $user_id);
                break;

            default:
                Thaiprompt_MLM_Logger::debug('Unknown postback action', array('action' => $action));
        }
    }

    /**
     * Get or create WordPress user from LINE ID
     */
    private static function get_or_create_user($line_user_id) {
        // Try to find existing user
        $wp_user_id = self::get_wp_user_by_line_id($line_user_id);

        if ($wp_user_id) {
            return get_userdata($wp_user_id);
        }

        // Auto-create if enabled
        if (get_option('thaiprompt_mlm_line_auto_register', 1)) {
            return self::register_user_from_line($line_user_id);
        }

        return false;
    }

    /**
     * Register new user from LINE profile
     */
    private static function register_user_from_line($line_user_id) {
        // Get LINE profile
        $profile = Thaiprompt_MLM_LINE_Bot::get_profile($line_user_id);

        if (is_wp_error($profile)) {
            Thaiprompt_MLM_Logger::error('Failed to get LINE profile', array(
                'line_user_id' => $line_user_id,
                'error' => $profile->get_error_message()
            ));
            return false;
        }

        $display_name = $profile['displayName'];
        $picture_url = isset($profile['pictureUrl']) ? $profile['pictureUrl'] : '';
        $status_message = isset($profile['statusMessage']) ? $profile['statusMessage'] : '';

        // Generate username from LINE display name
        $username = sanitize_user(strtolower(str_replace(' ', '_', $display_name)) . '_' . substr($line_user_id, 0, 8));

        // Ensure unique username
        $base_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        // Generate random password
        $password = wp_generate_password(16, true, true);

        // Create user
        $user_id = wp_create_user($username, $password, '');

        if (is_wp_error($user_id)) {
            Thaiprompt_MLM_Logger::error('Failed to create user', array(
                'error' => $user_id->get_error_message()
            ));
            return false;
        }

        // Update user data
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $display_name,
            'first_name' => $display_name
        ));

        // Save LINE profile data
        update_user_meta($user_id, 'line_user_id', $line_user_id);
        update_user_meta($user_id, 'line_display_name', $display_name);
        update_user_meta($user_id, 'line_picture_url', $picture_url);
        update_user_meta($user_id, 'line_status_message', $status_message);
        update_user_meta($user_id, 'line_following', 1);

        // Download and set profile picture
        if ($picture_url) {
            self::set_user_avatar_from_url($user_id, $picture_url);
        }

        // Generate referral code
        Thaiprompt_MLM_Referral::generate_referral_code($user_id);

        Thaiprompt_MLM_Logger::info('User registered from LINE', array(
            'user_id' => $user_id,
            'line_user_id' => $line_user_id,
            'display_name' => $display_name
        ));

        return get_userdata($user_id);
    }

    /**
     * Get WordPress user ID from LINE user ID
     */
    private static function get_wp_user_by_line_id($line_user_id) {
        $users = get_users(array(
            'meta_key' => 'line_user_id',
            'meta_value' => $line_user_id,
            'number' => 1
        ));

        return !empty($users) ? $users[0]->ID : false;
    }

    /**
     * Download and set user avatar from URL
     */
    private static function set_user_avatar_from_url($user_id, $image_url) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $tmp = download_url($image_url);

        if (is_wp_error($tmp)) {
            return false;
        }

        $file_array = array(
            'name' => 'line-avatar-' . $user_id . '.jpg',
            'tmp_name' => $tmp
        );

        $id = media_handle_sideload($file_array, 0);

        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return false;
        }

        update_user_meta($user_id, 'line_avatar_attachment_id', $id);

        return $id;
    }

    /**
     * Check if message is a command
     */
    private static function is_command($text) {
        return strpos($text, '/') === 0 || in_array(strtolower($text), array('help', 'profile', 'register', 'สมัคร', 'โปรไฟล์'));
    }

    /**
     * Handle command messages
     */
    private static function handle_command($text, $reply_token, $line_user_id, $wp_user) {
        $command = strtolower(trim($text, '/'));

        switch ($command) {
            case 'help':
            case 'ช่วยเหลือ':
                $message = "📚 คำสั่งที่ใช้ได้:\n\n" .
                          "/profile - ดูโปรไฟล์ของคุณ\n" .
                          "/referral - ดูลิงก์แนะนำ\n" .
                          "/help - ดูคำสั่งทั้งหมด";
                break;

            case 'profile':
            case 'โปรไฟล์':
                $message = "👤 ข้อมูลของคุณ:\n\n" .
                          "ชื่อ: " . $wp_user->display_name . "\n" .
                          "Username: " . $wp_user->user_login . "\n" .
                          "รหัสแนะนำ: " . Thaiprompt_MLM_Referral::get_code($wp_user->ID);
                break;

            case 'referral':
            case 'แนะนำ':
                $ref_code = Thaiprompt_MLM_Referral::get_code($wp_user->ID);
                $ref_link = Thaiprompt_MLM_Referral::get_referral_link($wp_user->ID);
                $message = "🔗 ลิงก์แนะนำของคุณ:\n\n" .
                          $ref_link . "\n\n" .
                          "รหัส: " . $ref_code;
                break;

            default:
                $message = "ขอโทษครับ ไม่เข้าใจคำสั่ง '" . $text . "'\n\nพิมพ์ /help เพื่อดูคำสั่งทั้งหมด";
        }

        $reply_message = Thaiprompt_MLM_LINE_Bot::build_text_message($message);
        Thaiprompt_MLM_LINE_Bot::reply_message($reply_token, $reply_message);
    }

    /**
     * Handle regular conversation (AI or default)
     */
    private static function handle_conversation($text, $reply_token, $line_user_id, $wp_user) {
        // TODO: Integrate with AI (ChatGPT, Gemini, DeepSeek)
        // For now, just echo back

        $message = "ได้รับข้อความ: " . $text . "\n\n(AI ยังไม่ได้เปิดใช้งาน)";

        $reply_message = Thaiprompt_MLM_LINE_Bot::build_text_message($message);
        Thaiprompt_MLM_LINE_Bot::reply_message($reply_token, $reply_message);
    }

    /**
     * Handle registration confirmation
     */
    private static function handle_registration_confirm($reply_token, $line_user_id, $params) {
        $user = self::register_user_from_line($line_user_id);

        if ($user) {
            $message = "✅ ลงทะเบียนเรียบร้อยแล้ว!\n\n" .
                      "ชื่อผู้ใช้: " . $user->user_login . "\n" .
                      "รหัสแนะนำ: " . Thaiprompt_MLM_Referral::get_code($user->ID);
        } else {
            $message = "❌ เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่อีกครั้ง";
        }

        $reply_message = Thaiprompt_MLM_LINE_Bot::build_text_message($message);
        Thaiprompt_MLM_LINE_Bot::reply_message($reply_token, $reply_message);
    }

    /**
     * Send user profile
     */
    private static function send_user_profile($reply_token, $line_user_id) {
        $wp_user_id = self::get_wp_user_by_line_id($line_user_id);

        if (!$wp_user_id) {
            $message = "❌ ไม่พบข้อมูลผู้ใช้";
        } else {
            $user = get_userdata($wp_user_id);
            $ref_code = Thaiprompt_MLM_Referral::get_code($wp_user_id);

            $message = "👤 ข้อมูลของคุณ\n\n" .
                      "ชื่อ: " . $user->display_name . "\n" .
                      "Username: " . $user->user_login . "\n" .
                      "รหัสแนะนำ: " . $ref_code;
        }

        $reply_message = Thaiprompt_MLM_LINE_Bot::build_text_message($message);
        Thaiprompt_MLM_LINE_Bot::reply_message($reply_token, $reply_message);
    }
}

// Initialize webhook
Thaiprompt_MLM_LINE_Webhook::init();
