<?php
/**
 * AI Handler
 *
 * Handles AI integrations for chatbot responses (ChatGPT, Gemini, DeepSeek)
 *
 * @since 1.8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Thaiprompt_MLM_AI_Handler {

    /**
     * Get AI response
     *
     * @param string $user_message User's message
     * @param string $line_user_id LINE User ID for context
     * @return string|WP_Error AI response or error
     */
    public static function get_response($user_message, $line_user_id = '') {
        // Get AI provider setting
        $ai_provider = get_option('thaiprompt_mlm_ai_provider', 'none');

        if ($ai_provider === 'none') {
            return new WP_Error('no_ai', __('AI is not enabled', 'thaiprompt-mlm'));
        }

        // Get conversation context
        $context = self::get_conversation_context($line_user_id);

        // Route to appropriate provider
        switch ($ai_provider) {
            case 'chatgpt':
                $response = self::chatgpt_response($user_message, $context);
                break;

            case 'gemini':
                $response = self::gemini_response($user_message, $context);
                break;

            case 'deepseek':
                $response = self::deepseek_response($user_message, $context);
                break;

            default:
                return new WP_Error('invalid_provider', __('Invalid AI provider', 'thaiprompt-mlm'));
        }

        // Save conversation context if successful
        if (!is_wp_error($response)) {
            self::save_conversation_context($line_user_id, $user_message, $response);
        }

        return $response;
    }

    /**
     * ChatGPT API integration (OpenAI)
     */
    private static function chatgpt_response($message, $context = array()) {
        $api_key = get_option('thaiprompt_mlm_chatgpt_api_key', '');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('ChatGPT API key not configured', 'thaiprompt-mlm'));
        }

        $model = get_option('thaiprompt_mlm_chatgpt_model', 'gpt-4o-mini');
        $system_prompt = get_option('thaiprompt_mlm_ai_system_prompt', self::get_default_system_prompt());

        // Build messages array
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            )
        );

        // Add context history (last 5 exchanges)
        if (!empty($context)) {
            $messages = array_merge($messages, array_slice($context, -10)); // Last 10 messages (5 exchanges)
        }

        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $message
        );

        // Call OpenAI API
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7
            ))
        ));

        if (is_wp_error($response)) {
            Thaiprompt_MLM_Logger::error('ChatGPT API error', array('error' => $response->get_error_message()));
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            Thaiprompt_MLM_Logger::error('ChatGPT API error', $body['error']);
            return new WP_Error('api_error', $body['error']['message'] ?? 'Unknown error');
        }

        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid response from ChatGPT', 'thaiprompt-mlm'));
        }

        return $body['choices'][0]['message']['content'];
    }

    /**
     * Google Gemini API integration
     */
    private static function gemini_response($message, $context = array()) {
        $api_key = get_option('thaiprompt_mlm_gemini_api_key', '');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Gemini API key not configured', 'thaiprompt-mlm'));
        }

        $model = get_option('thaiprompt_mlm_gemini_model', 'gemini-2.0-flash-exp');
        $system_prompt = get_option('thaiprompt_mlm_ai_system_prompt', self::get_default_system_prompt());

        // Build content array
        $contents = array();

        // Add context history
        if (!empty($context)) {
            foreach (array_slice($context, -10) as $msg) {
                $contents[] = array(
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => array(
                        array('text' => $msg['content'])
                    )
                );
            }
        }

        // Add current message
        $contents[] = array(
            'role' => 'user',
            'parts' => array(
                array('text' => $message)
            )
        );

        // Call Gemini API
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'contents' => $contents,
                'systemInstruction' => array(
                    'parts' => array(
                        array('text' => $system_prompt)
                    )
                ),
                'generationConfig' => array(
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500
                )
            ))
        ));

        if (is_wp_error($response)) {
            Thaiprompt_MLM_Logger::error('Gemini API error', array('error' => $response->get_error_message()));
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            Thaiprompt_MLM_Logger::error('Gemini API error', $body['error']);
            return new WP_Error('api_error', $body['error']['message'] ?? 'Unknown error');
        }

        if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('invalid_response', __('Invalid response from Gemini', 'thaiprompt-mlm'));
        }

        return $body['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * DeepSeek API integration
     */
    private static function deepseek_response($message, $context = array()) {
        $api_key = get_option('thaiprompt_mlm_deepseek_api_key', '');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('DeepSeek API key not configured', 'thaiprompt-mlm'));
        }

        $model = get_option('thaiprompt_mlm_deepseek_model', 'deepseek-chat');
        $system_prompt = get_option('thaiprompt_mlm_ai_system_prompt', self::get_default_system_prompt());

        // Build messages array (same format as OpenAI)
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            )
        );

        // Add context history
        if (!empty($context)) {
            $messages = array_merge($messages, array_slice($context, -10));
        }

        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $message
        );

        // Call DeepSeek API
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7
            ))
        ));

        if (is_wp_error($response)) {
            Thaiprompt_MLM_Logger::error('DeepSeek API error', array('error' => $response->get_error_message()));
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            Thaiprompt_MLM_Logger::error('DeepSeek API error', $body['error']);
            return new WP_Error('api_error', $body['error']['message'] ?? 'Unknown error');
        }

        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid response from DeepSeek', 'thaiprompt-mlm'));
        }

        return $body['choices'][0]['message']['content'];
    }

    /**
     * Get conversation context from database
     */
    private static function get_conversation_context($line_user_id) {
        if (empty($line_user_id)) {
            return array();
        }

        $context = get_transient('mlm_ai_context_' . $line_user_id);
        return $context ? $context : array();
    }

    /**
     * Save conversation context
     */
    private static function save_conversation_context($line_user_id, $user_message, $ai_response) {
        if (empty($line_user_id)) {
            return;
        }

        $context = self::get_conversation_context($line_user_id);

        // Add new exchange
        $context[] = array(
            'role' => 'user',
            'content' => $user_message
        );
        $context[] = array(
            'role' => 'assistant',
            'content' => $ai_response
        );

        // Keep only last 20 messages (10 exchanges)
        $context = array_slice($context, -20);

        // Save for 1 hour
        set_transient('mlm_ai_context_' . $line_user_id, $context, HOUR_IN_SECONDS);
    }

    /**
     * Clear conversation context
     */
    public static function clear_context($line_user_id) {
        delete_transient('mlm_ai_context_' . $line_user_id);
    }

    /**
     * Get default system prompt
     */
    private static function get_default_system_prompt() {
        $site_name = get_bloginfo('name');
        $site_description = get_bloginfo('description');

        // Base prompt
        $prompt = "You are a helpful MLM (Multi-Level Marketing) assistant for {$site_name}. " .
                 "You help members with:\n" .
                 "- Understanding the MLM program and compensation plan\n" .
                 "- Getting their referral links and codes\n" .
                 "- Checking their network statistics and earnings\n" .
                 "- Learning about ranks and how to advance\n" .
                 "- Creating and managing landing pages\n\n";

        // Get knowledge sources configuration
        $knowledge_sources = get_option('thaiprompt_mlm_ai_knowledge_sources', array('general'));
        $response_mode = get_option('thaiprompt_mlm_ai_response_mode', 'flexible');

        // Add knowledge sources information
        $knowledge_context = self::build_knowledge_context($knowledge_sources);
        if (!empty($knowledge_context)) {
            $prompt .= "\n=== KNOWLEDGE BASE ===\n" . $knowledge_context . "\n";
        }

        // Add response mode instructions
        $prompt .= self::get_response_mode_instructions($response_mode, $knowledge_sources);

        // General instructions
        $prompt .= "\n\nBe friendly, professional, and encouraging. " .
                  "Use Thai language if the user speaks Thai, otherwise use English. " .
                  "Keep responses concise (under 300 characters when possible) as this is a LINE chat. " .
                  "If users ask about technical issues or account problems, suggest they contact support.";

        return $prompt;
    }

    /**
     * Build knowledge context from configured sources
     */
    private static function build_knowledge_context($knowledge_sources) {
        $context = '';

        // Website Information
        if (in_array('website', $knowledge_sources)) {
            $site_name = get_bloginfo('name');
            $site_description = get_bloginfo('description');
            $site_url = home_url();

            $context .= "Website Information:\n";
            $context .= "- Site Name: {$site_name}\n";
            $context .= "- Description: {$site_description}\n";
            $context .= "- URL: {$site_url}\n\n";
        }

        // Selected Posts/Articles
        if (in_array('posts', $knowledge_sources)) {
            $selected_posts = get_option('thaiprompt_mlm_ai_knowledge_posts', array());

            if (!empty($selected_posts)) {
                $context .= "Available Articles:\n";

                foreach ($selected_posts as $post_id) {
                    $post = get_post($post_id);
                    if ($post) {
                        $content = wp_strip_all_tags($post->post_content);
                        $content = substr($content, 0, 500); // Limit to 500 chars per post

                        $context .= "- Title: {$post->post_title}\n";
                        $context .= "  Summary: {$content}...\n";
                        $context .= "  URL: " . get_permalink($post_id) . "\n\n";
                    }
                }
            }
        }

        // External Links
        if (in_array('links', $knowledge_sources)) {
            $links = get_option('thaiprompt_mlm_ai_knowledge_links', '');

            if (!empty($links)) {
                $links_array = array_filter(explode("\n", $links));

                if (!empty($links_array)) {
                    $context .= "Reference Links:\n";
                    foreach ($links_array as $link) {
                        $link = trim($link);
                        if (!empty($link)) {
                            $context .= "- " . esc_url($link) . "\n";
                        }
                    }
                    $context .= "\n";
                }
            }
        }

        // Custom Knowledge Base
        if (in_array('custom', $knowledge_sources)) {
            $custom_knowledge = get_option('thaiprompt_mlm_ai_knowledge_custom', '');

            if (!empty($custom_knowledge)) {
                $context .= "Custom Knowledge Base:\n";
                $context .= $custom_knowledge . "\n\n";
            }
        }

        return $context;
    }

    /**
     * Get response mode instructions
     */
    private static function get_response_mode_instructions($mode, $knowledge_sources) {
        $has_specific_knowledge = count(array_diff($knowledge_sources, array('general'))) > 0;

        if (!$has_specific_knowledge) {
            return '';
        }

        switch ($mode) {
            case 'strict':
                return "\n⚠️ STRICT MODE: ONLY answer questions based on the knowledge base provided above. " .
                       "If the answer is not in the knowledge base, politely say you don't have that information " .
                       "and suggest contacting support or checking the website.";

            case 'moderate':
                return "\n📋 MODERATE MODE: Prioritize answering from the knowledge base provided above. " .
                       "You may supplement with general knowledge if needed, but clearly indicate when you're " .
                       "using general knowledge vs. specific information from the knowledge base.";

            case 'flexible':
            default:
                return "\n✨ FLEXIBLE MODE: Use the knowledge base provided above as primary reference, " .
                       "but feel free to use your general knowledge to provide helpful answers.";
        }
    }

    /**
     * Get available AI providers
     */
    public static function get_providers() {
        return array(
            'none' => __('Disabled', 'thaiprompt-mlm'),
            'chatgpt' => 'ChatGPT (OpenAI)',
            'gemini' => 'Google Gemini',
            'deepseek' => 'DeepSeek'
        );
    }

    /**
     * Test AI connection
     */
    public static function test_connection($provider = null) {
        if (!$provider) {
            $provider = get_option('thaiprompt_mlm_ai_provider', 'none');
        }

        if ($provider === 'none') {
            return new WP_Error('no_provider', __('No AI provider selected', 'thaiprompt-mlm'));
        }

        // Save current provider temporarily
        $current_provider = get_option('thaiprompt_mlm_ai_provider');
        update_option('thaiprompt_mlm_ai_provider', $provider);

        // Test with simple message
        $response = self::get_response('Hello, this is a test message.', '');

        // Restore original provider
        update_option('thaiprompt_mlm_ai_provider', $current_provider);

        return $response;
    }
}
