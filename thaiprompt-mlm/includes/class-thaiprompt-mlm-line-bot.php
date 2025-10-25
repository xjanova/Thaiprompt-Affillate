<?php
/**
 * LINE Bot Integration Class
 *
 * Complete LINE Messaging API integration for:
 * - Send push messages (individual messages, not broadcast)
 * - Reply to messages
 * - Get user profiles
 * - Rich menu management
 * - Flex message support
 * - AI integration ready
 */

class Thaiprompt_MLM_LINE_Bot {

    /**
     * LINE Messaging API endpoint
     */
    const API_ENDPOINT = 'https://api.line.me/v2/bot';

    /**
     * Get Channel Access Token from settings
     */
    private static function get_access_token() {
        return get_option('thaiprompt_mlm_line_channel_access_token', '');
    }

    /**
     * Get Channel Secret from settings
     */
    private static function get_channel_secret() {
        return get_option('thaiprompt_mlm_line_channel_secret', '');
    }

    /**
     * Validate webhook signature
     */
    public static function validate_signature($body, $signature) {
        $channel_secret = self::get_channel_secret();
        $hash = base64_encode(hash_hmac('sha256', $body, $channel_secret, true));

        return hash_equals($hash, $signature);
    }

    /**
     * Send push message to specific user (not broadcast)
     *
     * @param string $user_id LINE User ID
     * @param array $messages Array of message objects
     * @return array|WP_Error Response or error
     */
    public static function push_message($user_id, $messages) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            Thaiprompt_MLM_Logger::error('LINE push message failed: No access token configured');
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        // Ensure messages is an array
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        $data = array(
            'to' => $user_id,
            'messages' => $messages
        );

        $response = self::call_api('/message/push', $data);

        if (is_wp_error($response)) {
            Thaiprompt_MLM_Logger::error('LINE push message failed', array(
                'user_id' => $user_id,
                'error' => $response->get_error_message()
            ));
        } else {
            Thaiprompt_MLM_Logger::info('LINE push message sent', array(
                'user_id' => $user_id,
                'message_count' => count($messages)
            ));
        }

        return $response;
    }

    /**
     * Reply to user message
     *
     * @param string $reply_token Reply token from webhook event
     * @param array $messages Array of message objects
     * @return array|WP_Error Response or error
     */
    public static function reply_message($reply_token, $messages) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        // Ensure messages is an array
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        $data = array(
            'replyToken' => $reply_token,
            'messages' => $messages
        );

        $response = self::call_api('/message/reply', $data);

        if (is_wp_error($response)) {
            Thaiprompt_MLM_Logger::error('LINE reply message failed', array(
                'reply_token' => $reply_token,
                'error' => $response->get_error_message()
            ));
        }

        return $response;
    }

    /**
     * Get user profile
     *
     * @param string $user_id LINE User ID
     * @return array|WP_Error User profile or error
     */
    public static function get_profile($user_id) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        $response = self::call_api('/profile/' . $user_id, null, 'GET');

        return $response;
    }

    /**
     * Create Rich Menu
     *
     * @param array $rich_menu_data Rich menu configuration
     * @return string|WP_Error Rich menu ID or error
     */
    public static function create_rich_menu($rich_menu_data) {
        $response = self::call_api('/richmenu', $rich_menu_data);

        if (!is_wp_error($response) && isset($response['richMenuId'])) {
            return $response['richMenuId'];
        }

        return $response;
    }

    /**
     * Upload Rich Menu image
     *
     * @param string $rich_menu_id Rich menu ID
     * @param string $image_path Path to image file
     * @return array|WP_Error Response or error
     */
    public static function upload_rich_menu_image($rich_menu_id, $image_path) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        if (!file_exists($image_path)) {
            return new WP_Error('file_not_found', 'Image file not found');
        }

        $url = self::API_ENDPOINT . '/richmenu/' . $rich_menu_id . '/content';

        $image_data = file_get_contents($image_path);
        $content_type = mime_content_type($image_path);

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => $content_type
            ),
            'body' => $image_data,
            'timeout' => 30
        ));

        return self::parse_response($response);
    }

    /**
     * Set default Rich Menu
     *
     * @param string $rich_menu_id Rich menu ID
     * @return array|WP_Error Response or error
     */
    public static function set_default_rich_menu($rich_menu_id) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        $url = self::API_ENDPOINT . '/user/all/richmenu/' . $rich_menu_id;

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            ),
            'timeout' => 30
        ));

        return self::parse_response($response);
    }

    /**
     * Link Rich Menu to user
     *
     * @param string $user_id LINE User ID
     * @param string $rich_menu_id Rich menu ID
     * @return array|WP_Error Response or error
     */
    public static function link_rich_menu_to_user($user_id, $rich_menu_id) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        $url = self::API_ENDPOINT . '/user/' . $user_id . '/richmenu/' . $rich_menu_id;

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            ),
            'timeout' => 30
        ));

        return self::parse_response($response);
    }

    /**
     * Get Rich Menu list
     *
     * @return array|WP_Error List of rich menus or error
     */
    public static function get_rich_menu_list() {
        return self::call_api('/richmenu/list', null, 'GET');
    }

    /**
     * Delete Rich Menu
     *
     * @param string $rich_menu_id Rich menu ID
     * @return array|WP_Error Response or error
     */
    public static function delete_rich_menu($rich_menu_id) {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        $url = self::API_ENDPOINT . '/richmenu/' . $rich_menu_id;

        $response = wp_remote_request($url, array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            ),
            'timeout' => 30
        ));

        return self::parse_response($response);
    }

    /**
     * Make API call to LINE
     *
     * @param string $endpoint API endpoint (e.g., '/message/push')
     * @param array|null $data Request data
     * @param string $method HTTP method (GET, POST, DELETE)
     * @return array|WP_Error Response or error
     */
    private static function call_api($endpoint, $data = null, $method = 'POST') {
        $access_token = self::get_access_token();

        if (empty($access_token)) {
            return new WP_Error('no_token', 'LINE Channel Access Token not configured');
        }

        $url = self::API_ENDPOINT . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        if ($data && ($method === 'POST' || $method === 'PUT')) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        return self::parse_response($response);
    }

    /**
     * Parse API response
     *
     * @param array|WP_Error $response WordPress HTTP response
     * @return array|WP_Error Parsed response or error
     */
    private static function parse_response($response) {
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code >= 200 && $status_code < 300) {
            return $data ? $data : array('success' => true);
        }

        // Error response
        $error_message = isset($data['message']) ? $data['message'] : 'Unknown error';
        $error_details = isset($data['details']) ? $data['details'] : array();

        Thaiprompt_MLM_Logger::error('LINE API Error', array(
            'status_code' => $status_code,
            'message' => $error_message,
            'details' => $error_details
        ));

        return new WP_Error('line_api_error', $error_message, array(
            'status' => $status_code,
            'details' => $error_details
        ));
    }

    /**
     * Build text message
     *
     * @param string $text Message text
     * @return array Message object
     */
    public static function build_text_message($text) {
        return array(
            'type' => 'text',
            'text' => $text
        );
    }

    /**
     * Build image message
     *
     * @param string $original_url Original image URL
     * @param string $preview_url Preview image URL
     * @return array Message object
     */
    public static function build_image_message($original_url, $preview_url = null) {
        return array(
            'type' => 'image',
            'originalContentUrl' => $original_url,
            'previewImageUrl' => $preview_url ? $preview_url : $original_url
        );
    }

    /**
     * Build template button message
     *
     * @param string $alt_text Alt text for notifications
     * @param string $title Title text
     * @param string $text Body text
     * @param array $actions Array of action objects
     * @return array Message object
     */
    public static function build_button_template($alt_text, $title, $text, $actions) {
        return array(
            'type' => 'template',
            'altText' => $alt_text,
            'template' => array(
                'type' => 'buttons',
                'title' => $title,
                'text' => $text,
                'actions' => $actions
            )
        );
    }

    /**
     * Build Flex Message
     *
     * @param string $alt_text Alt text for notifications
     * @param array $contents Flex contents
     * @return array Message object
     */
    public static function build_flex_message($alt_text, $contents) {
        return array(
            'type' => 'flex',
            'altText' => $alt_text,
            'contents' => $contents
        );
    }
}
