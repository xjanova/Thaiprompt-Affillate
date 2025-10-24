<?php
/**
 * Database operations class
 */
class Thaiprompt_MLM_Database {

    /**
     * Get user's MLM network data
     */
    public static function get_user_network($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d",
            $user_id
        ));
    }

    /**
     * Add user to MLM network
     */
    public static function add_to_network($user_id, $sponsor_id, $placement_id = null, $position = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        // If no placement specified, use sponsor
        if (!$placement_id) {
            $placement_id = $sponsor_id;
        }

        // Calculate level
        $level = 1;
        if ($placement_id) {
            $parent = self::get_user_network($placement_id);
            if ($parent) {
                $level = $parent->level + 1;
            }
        }

        $data = array(
            'user_id' => $user_id,
            'sponsor_id' => $sponsor_id,
            'placement_id' => $placement_id,
            'position' => $position,
            'level' => $level
        );

        $result = $wpdb->insert($table, $data);

        if ($result) {
            // Update parent's counts
            self::update_downline_counts($placement_id);
        }

        return $result;
    }

    /**
     * Update downline counts for a user
     */
    public static function update_downline_counts($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        // Get direct downlines
        $left_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE placement_id = %d AND position = 'left'",
            $user_id
        ));

        $right_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE placement_id = %d AND position = 'right'",
            $user_id
        ));

        $total_downline = self::get_total_downline_count($user_id);

        $wpdb->update(
            $table,
            array(
                'left_count' => $left_count,
                'right_count' => $right_count,
                'total_downline' => $total_downline
            ),
            array('user_id' => $user_id)
        );
    }

    /**
     * Get total downline count recursively
     */
    public static function get_total_downline_count($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE sponsor_id = %d",
            $user_id
        ));

        return $count ? intval($count) : 0;
    }

    /**
     * Get user's downline
     */
    public static function get_downline($user_id, $level = null, $position = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $query = "SELECT * FROM $table WHERE placement_id = %d";
        $params = array($user_id);

        if ($position) {
            $query .= " AND position = %s";
            $params[] = $position;
        }

        if ($level) {
            $query .= " AND level = %d";
            $params[] = $level;
        }

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get user's genealogy tree
     */
    public static function get_genealogy_tree($user_id, $max_depth = 5, $current_depth = 0) {
        if ($current_depth >= $max_depth) {
            return null;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $user_data = self::get_user_network($user_id);
        if (!$user_data) {
            return null;
        }

        $user_info = get_userdata($user_id);

        $tree = array(
            'user_id' => $user_id,
            'name' => $user_info ? $user_info->display_name : 'Unknown',
            'email' => $user_info ? $user_info->user_email : '',
            'level' => $user_data->level,
            'personal_sales' => floatval($user_data->personal_sales),
            'group_sales' => floatval($user_data->group_sales),
            'left_count' => intval($user_data->left_count),
            'right_count' => intval($user_data->right_count),
            'children' => array()
        );

        // Get direct children
        $children = self::get_downline($user_id);

        foreach ($children as $child) {
            $child_tree = self::get_genealogy_tree($child->user_id, $max_depth, $current_depth + 1);
            if ($child_tree) {
                $tree['children'][] = $child_tree;
            }
        }

        return $tree;
    }

    /**
     * Update user sales
     */
    public static function update_user_sales($user_id, $amount, $type = 'personal') {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $field = $type === 'personal' ? 'personal_sales' : 'group_sales';

        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET $field = $field + %f WHERE user_id = %d",
            $amount,
            $user_id
        ));

        // Update upline group sales
        if ($type === 'personal') {
            self::update_upline_group_sales($user_id, $amount);
        }
    }

    /**
     * Update upline group sales recursively
     */
    private static function update_upline_group_sales($user_id, $amount) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $user_data = self::get_user_network($user_id);
        if (!$user_data || !$user_data->sponsor_id) {
            return;
        }

        // Update sponsor's group sales
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET group_sales = group_sales + %f WHERE user_id = %d",
            $amount,
            $user_data->sponsor_id
        ));

        // Continue up the chain
        self::update_upline_group_sales($user_data->sponsor_id, $amount);
    }

    /**
     * Get wallet balance
     */
    public static function get_wallet_balance($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d",
            $user_id
        ));

        if (!$wallet) {
            // Create wallet if doesn't exist
            $wpdb->insert($table, array('user_id' => $user_id));
            $wallet = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d",
                $user_id
            ));
        }

        return $wallet;
    }

    /**
     * Add commission
     */
    public static function add_commission($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $defaults = array(
            'user_id' => $user_id,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );

        $data = wp_parse_args($data, $defaults);

        return $wpdb->insert($table, $data);
    }

    /**
     * Get user commissions
     */
    public static function get_user_commissions($user_id, $args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $defaults = array(
            'limit' => 50,
            'offset' => 0,
            'status' => null,
            'commission_type' => null
        );

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM $table WHERE user_id = %d";
        $params = array($user_id);

        if ($args['status']) {
            $query .= " AND status = %s";
            $params[] = $args['status'];
        }

        if ($args['commission_type']) {
            $query .= " AND commission_type = %s";
            $params[] = $args['commission_type'];
        }

        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get commission statistics
     */
    public static function get_commission_stats($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_earned,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                COUNT(*) as total_commissions
            FROM $table WHERE user_id = %d",
            $user_id
        ));

        return $stats;
    }

    /**
     * Get product MLM settings
     */
    public static function get_product_settings($product_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_product_settings';

        $settings = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE product_id = %d",
            $product_id
        ));

        return $settings;
    }

    /**
     * Save product MLM settings
     */
    public static function save_product_settings($product_id, $settings) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_product_settings';

        $existing = self::get_product_settings($product_id);

        $data = array(
            'product_id' => $product_id,
            'mlm_enabled' => isset($settings['mlm_enabled']) ? 1 : 0,
            'commission_type' => $settings['commission_type'] ?? 'percentage',
            'commission_value' => $settings['commission_value'] ?? 0,
            'fast_start_enabled' => isset($settings['fast_start_enabled']) ? 1 : 0,
            'fast_start_value' => $settings['fast_start_value'] ?? 0,
            'max_level' => $settings['max_level'] ?? 10,
            'settings_override' => isset($settings['settings_override']) ? json_encode($settings['settings_override']) : null
        );

        if ($existing) {
            return $wpdb->update($table, $data, array('product_id' => $product_id));
        } else {
            return $wpdb->insert($table, $data);
        }
    }

    /**
     * Get all ranks
     */
    public static function get_ranks() {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_ranks';

        return $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY rank_order ASC"
        );
    }

    /**
     * Get user's current rank
     */
    public static function get_user_rank($user_id) {
        global $wpdb;
        $user_ranks_table = $wpdb->prefix . 'thaiprompt_mlm_user_ranks';
        $ranks_table = $wpdb->prefix . 'thaiprompt_mlm_ranks';

        $rank = $wpdb->get_row($wpdb->prepare(
            "SELECT r.* FROM $ranks_table r
            INNER JOIN $user_ranks_table ur ON r.id = ur.rank_id
            WHERE ur.user_id = %d AND ur.is_current = 1
            ORDER BY r.rank_order DESC LIMIT 1",
            $user_id
        ));

        if (!$rank) {
            // Return default rank (Member)
            $rank = $wpdb->get_row(
                "SELECT * FROM $ranks_table WHERE rank_order = 1 LIMIT 1"
            );
        }

        return $rank;
    }

    /**
     * Update user rank
     */
    public static function update_user_rank($user_id, $rank_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_user_ranks';

        // Set all previous ranks to not current
        $wpdb->update(
            $table,
            array('is_current' => 0),
            array('user_id' => $user_id)
        );

        // Add new rank
        return $wpdb->insert($table, array(
            'user_id' => $user_id,
            'rank_id' => $rank_id,
            'is_current' => 1,
            'achieved_at' => current_time('mysql')
        ));
    }
}
