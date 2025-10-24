<?php
/**
 * MLM Network management class
 */
class Thaiprompt_MLM_Network {

    /**
     * Register a new user in the MLM network
     */
    public static function register_user($user_id, $sponsor_id = null, $placement_id = null, $position = null) {
        // Check if user already exists in network
        $existing = Thaiprompt_MLM_Database::get_user_network($user_id);
        if ($existing) {
            return new WP_Error('user_exists', __('User already exists in MLM network', 'thaiprompt-mlm'));
        }

        // Get settings
        $settings = get_option('thaiprompt_mlm_settings', array());
        $placement_type = $settings['placement_type'] ?? 'auto';

        // If no sponsor specified, assign to admin or root
        if (!$sponsor_id) {
            $sponsor_id = get_option('thaiprompt_mlm_root_user', 1);
        }

        // Handle placement based on type
        if (!$placement_id) {
            if ($placement_type === 'auto') {
                $placement_data = self::find_auto_placement($sponsor_id);
                $placement_id = $placement_data['placement_id'];
                $position = $placement_data['position'];
            } else {
                $placement_id = $sponsor_id;
                $position = $placement_type; // 'left' or 'right'
            }
        }

        // Add to network
        $result = Thaiprompt_MLM_Database::add_to_network($user_id, $sponsor_id, $placement_id, $position);

        if ($result) {
            // Create wallet for new user
            self::create_user_wallet($user_id);

            // Trigger action
            do_action('thaiprompt_mlm_user_registered', $user_id, $sponsor_id, $placement_id, $position);

            return true;
        }

        return new WP_Error('registration_failed', __('Failed to register user in MLM network', 'thaiprompt-mlm'));
    }

    /**
     * Find auto placement (binary tree)
     */
    private static function find_auto_placement($sponsor_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        // Start with sponsor
        $queue = array($sponsor_id);
        $visited = array();

        while (!empty($queue)) {
            $current_id = array_shift($queue);

            // Skip if already visited
            if (in_array($current_id, $visited)) {
                continue;
            }
            $visited[] = $current_id;

            // Check if this user has available spots
            $children = Thaiprompt_MLM_Database::get_downline($current_id);
            $has_left = false;
            $has_right = false;

            foreach ($children as $child) {
                if ($child->position === 'left') {
                    $has_left = true;
                    $queue[] = $child->user_id;
                }
                if ($child->position === 'right') {
                    $has_right = true;
                    $queue[] = $child->user_id;
                }
            }

            // Found an empty spot
            if (!$has_left) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'left'
                );
            }
            if (!$has_right) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'right'
                );
            }
        }

        // Fallback to sponsor's left
        return array(
            'placement_id' => $sponsor_id,
            'position' => 'left'
        );
    }

    /**
     * Create wallet for new user
     */
    private static function create_user_wallet($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0
        ));
    }

    /**
     * Get user's position in network
     */
    public static function get_user_position($user_id) {
        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);

        if (!$network_data) {
            return null;
        }

        $sponsor = null;
        $placement = null;

        if ($network_data->sponsor_id) {
            $sponsor_user = get_userdata($network_data->sponsor_id);
            $sponsor = array(
                'id' => $network_data->sponsor_id,
                'name' => $sponsor_user ? $sponsor_user->display_name : 'Unknown'
            );
        }

        if ($network_data->placement_id) {
            $placement_user = get_userdata($network_data->placement_id);
            $placement = array(
                'id' => $network_data->placement_id,
                'name' => $placement_user ? $placement_user->display_name : 'Unknown'
            );
        }

        return array(
            'level' => $network_data->level,
            'position' => $network_data->position,
            'sponsor' => $sponsor,
            'placement' => $placement,
            'left_count' => $network_data->left_count,
            'right_count' => $network_data->right_count,
            'total_downline' => $network_data->total_downline,
            'personal_sales' => floatval($network_data->personal_sales),
            'group_sales' => floatval($network_data->group_sales)
        );
    }

    /**
     * Get user's direct referrals (sponsored by user)
     */
    public static function get_direct_referrals($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $referrals = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE sponsor_id = %d ORDER BY created_at DESC",
            $user_id
        ));

        $result = array();
        foreach ($referrals as $referral) {
            $user = get_userdata($referral->user_id);
            if ($user) {
                $result[] = array(
                    'user_id' => $referral->user_id,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'joined_date' => $referral->created_at,
                    'level' => $referral->level,
                    'personal_sales' => floatval($referral->personal_sales),
                    'group_sales' => floatval($referral->group_sales)
                );
            }
        }

        return $result;
    }

    /**
     * Get team statistics
     */
    public static function get_team_stats($user_id) {
        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);

        if (!$network_data) {
            return array(
                'total_team' => 0,
                'active_members' => 0,
                'total_sales' => 0,
                'left_leg_sales' => 0,
                'right_leg_sales' => 0
            );
        }

        // Get left and right leg sales
        $left_leg_sales = self::get_leg_sales($user_id, 'left');
        $right_leg_sales = self::get_leg_sales($user_id, 'right');

        // Count active members (made purchase in last 30 days)
        $active_count = self::count_active_members($user_id);

        return array(
            'total_team' => intval($network_data->total_downline),
            'active_members' => $active_count,
            'total_sales' => floatval($network_data->group_sales),
            'left_leg_sales' => $left_leg_sales,
            'right_leg_sales' => $right_leg_sales,
            'left_count' => intval($network_data->left_count),
            'right_count' => intval($network_data->right_count)
        );
    }

    /**
     * Get leg sales (left or right)
     */
    private static function get_leg_sales($user_id, $position) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        // Get direct child in this position
        $child = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id, group_sales FROM $table WHERE placement_id = %d AND position = %s",
            $user_id,
            $position
        ));

        if ($child) {
            return floatval($child->group_sales);
        }

        return 0;
    }

    /**
     * Count active members in downline
     */
    private static function count_active_members($user_id) {
        global $wpdb;
        $network_table = $wpdb->prefix . 'thaiprompt_mlm_network';
        $commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT n.user_id)
            FROM $network_table n
            INNER JOIN $commissions_table c ON n.user_id = c.user_id
            WHERE n.sponsor_id = %d AND c.created_at > %s",
            $user_id,
            $thirty_days_ago
        ));

        return intval($count);
    }

    /**
     * Get referral link for user
     */
    public static function get_referral_link($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }

        $site_url = home_url();
        $referral_param = apply_filters('thaiprompt_mlm_referral_param', 'ref');

        return add_query_arg($referral_param, $user->user_login, $site_url);
    }

    /**
     * Get sponsor from referral link
     */
    public static function get_sponsor_from_referral() {
        $referral_param = apply_filters('thaiprompt_mlm_referral_param', 'ref');

        if (isset($_GET[$referral_param])) {
            $username = sanitize_text_field($_GET[$referral_param]);
            $user = get_user_by('login', $username);

            if ($user) {
                // Store in session or cookie
                if (!session_id()) {
                    session_start();
                }
                $_SESSION['mlm_sponsor_id'] = $user->ID;

                return $user->ID;
            }
        }

        // Check session
        if (!session_id()) {
            session_start();
        }
        if (isset($_SESSION['mlm_sponsor_id'])) {
            return $_SESSION['mlm_sponsor_id'];
        }

        return null;
    }
}
