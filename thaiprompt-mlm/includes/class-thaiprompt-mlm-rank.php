<?php
/**
 * MLM Rank management class
 */
class Thaiprompt_MLM_Rank {

    /**
     * Check and update user rank
     */
    public static function check_user_rank($user_id) {
        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);
        if (!$network_data) {
            return false;
        }

        $current_rank = Thaiprompt_MLM_Database::get_user_rank($user_id);
        $all_ranks = Thaiprompt_MLM_Database::get_ranks();

        $qualified_rank = null;

        // Check qualification for each rank (starting from highest)
        foreach (array_reverse($all_ranks) as $rank) {
            if (self::check_rank_qualification($user_id, $rank)) {
                $qualified_rank = $rank;
                break;
            }
        }

        if (!$qualified_rank) {
            // Default to first rank (Member)
            $qualified_rank = $all_ranks[0];
        }

        // Check if rank changed
        if (!$current_rank || $qualified_rank->id !== $current_rank->id) {
            // Update user rank
            Thaiprompt_MLM_Database::update_user_rank($user_id, $qualified_rank->id);

            // Award rank achievement bonus if higher rank
            if (!$current_rank || $qualified_rank->rank_order > $current_rank->rank_order) {
                self::award_rank_bonus($user_id, $qualified_rank);
            }

            do_action('thaiprompt_mlm_rank_changed', $user_id, $current_rank, $qualified_rank);

            return $qualified_rank;
        }

        return $current_rank;
    }

    /**
     * Check if user qualifies for a rank
     */
    public static function check_rank_qualification($user_id, $rank) {
        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);
        if (!$network_data) {
            return false;
        }

        // Check personal sales requirement
        if ($network_data->personal_sales < $rank->required_personal_sales) {
            return false;
        }

        // Check group sales requirement
        if ($network_data->group_sales < $rank->required_group_sales) {
            return false;
        }

        // Check active legs requirement
        if ($rank->required_active_legs > 0) {
            $active_legs = self::count_active_legs($user_id, $rank->required_personal_sales);
            if ($active_legs < $rank->required_active_legs) {
                return false;
            }
        }

        return true;
    }

    /**
     * Count active legs (direct referrals who meet minimum sales)
     */
    private static function count_active_legs($user_id, $minimum_sales) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table
            WHERE sponsor_id = %d AND personal_sales >= %f",
            $user_id,
            $minimum_sales
        ));

        return intval($count);
    }

    /**
     * Award rank achievement bonus
     */
    private static function award_rank_bonus($user_id, $rank) {
        if ($rank->bonus_amount <= 0) {
            return;
        }

        // Add rank bonus commission
        Thaiprompt_MLM_Database::add_commission($user_id, array(
            'commission_type' => 'rank_bonus',
            'amount' => $rank->bonus_amount,
            'status' => 'approved',
            'description' => sprintf(
                __('Rank Achievement Bonus: %s', 'thaiprompt-mlm'),
                $rank->rank_name
            )
        ));

        // Add to wallet
        global $wpdb;
        $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';

        $wallet = Thaiprompt_MLM_Database::get_wallet_balance($user_id);

        $wpdb->query($wpdb->prepare(
            "UPDATE $wallet_table
            SET balance = balance + %f,
                total_earned = total_earned + %f
            WHERE user_id = %d",
            $rank->bonus_amount,
            $rank->bonus_amount,
            $user_id
        ));

        // Add transaction
        Thaiprompt_MLM_Wallet::add_transaction($user_id, array(
            'transaction_type' => 'rank_bonus',
            'amount' => $rank->bonus_amount,
            'balance_before' => floatval($wallet->balance),
            'balance_after' => floatval($wallet->balance) + $rank->bonus_amount,
            'description' => sprintf(
                __('Rank Achievement Bonus: %s', 'thaiprompt-mlm'),
                $rank->rank_name
            ),
            'status' => 'completed'
        ));

        // Send notification
        do_action('thaiprompt_mlm_rank_bonus_awarded', $user_id, $rank);
    }

    /**
     * Calculate monthly rank bonus
     */
    public static function calculate_monthly_rank_bonus($user_id) {
        $rank = Thaiprompt_MLM_Database::get_user_rank($user_id);
        if (!$rank || $rank->bonus_percentage <= 0) {
            return 0;
        }

        // Get user's group sales for the month
        $monthly_sales = self::get_monthly_group_sales($user_id);
        $bonus_amount = ($monthly_sales * $rank->bonus_percentage) / 100;

        if ($bonus_amount > 0) {
            // Add monthly rank bonus
            Thaiprompt_MLM_Database::add_commission($user_id, array(
                'commission_type' => 'monthly_rank_bonus',
                'amount' => $bonus_amount,
                'percentage' => $rank->bonus_percentage,
                'status' => 'approved',
                'description' => sprintf(
                    __('Monthly Rank Bonus: %s (%s%%)', 'thaiprompt-mlm'),
                    $rank->rank_name,
                    $rank->bonus_percentage
                )
            ));

            // Add to wallet
            global $wpdb;
            $wallet_table = $wpdb->prefix . 'thaiprompt_mlm_wallet';
            $wallet = Thaiprompt_MLM_Database::get_wallet_balance($user_id);

            $wpdb->query($wpdb->prepare(
                "UPDATE $wallet_table
                SET balance = balance + %f,
                    total_earned = total_earned + %f
                WHERE user_id = %d",
                $bonus_amount,
                $bonus_amount,
                $user_id
            ));

            Thaiprompt_MLM_Wallet::add_transaction($user_id, array(
                'transaction_type' => 'monthly_rank_bonus',
                'amount' => $bonus_amount,
                'balance_before' => floatval($wallet->balance),
                'balance_after' => floatval($wallet->balance) + $bonus_amount,
                'description' => sprintf(
                    __('Monthly Rank Bonus: %s', 'thaiprompt-mlm'),
                    $rank->rank_name
                ),
                'status' => 'completed'
            ));
        }

        return $bonus_amount;
    }

    /**
     * Get monthly group sales
     */
    private static function get_monthly_group_sales($user_id) {
        global $wpdb;
        $commissions_table = $wpdb->prefix . 'thaiprompt_mlm_commissions';

        $first_day = date('Y-m-01 00:00:00');
        $last_day = date('Y-m-t 23:59:59');

        // Get all commissions generated by downline this month
        $sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount / (percentage / 100))
            FROM $commissions_table
            WHERE user_id = %d
            AND commission_type LIKE 'level%%'
            AND created_at BETWEEN %s AND %s",
            $user_id,
            $first_day,
            $last_day
        ));

        return floatval($sales ?? 0);
    }

    /**
     * Get rank leaderboard
     */
    public static function get_leaderboard($limit = 50) {
        global $wpdb;
        $user_ranks_table = $wpdb->prefix . 'thaiprompt_mlm_user_ranks';
        $ranks_table = $wpdb->prefix . 'thaiprompt_mlm_ranks';
        $network_table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT ur.user_id, r.rank_name, r.rank_color, n.group_sales, n.total_downline
            FROM $user_ranks_table ur
            INNER JOIN $ranks_table r ON ur.rank_id = r.id
            INNER JOIN $network_table n ON ur.user_id = n.user_id
            WHERE ur.is_current = 1
            ORDER BY r.rank_order DESC, n.group_sales DESC
            LIMIT %d",
            $limit
        ));

        $leaderboard = array();
        foreach ($results as $row) {
            $user = get_userdata($row->user_id);
            if ($user) {
                $leaderboard[] = array(
                    'user_id' => $row->user_id,
                    'name' => $user->display_name,
                    'rank' => $row->rank_name,
                    'rank_color' => $row->rank_color,
                    'group_sales' => floatval($row->group_sales),
                    'team_size' => intval($row->total_downline)
                );
            }
        }

        return $leaderboard;
    }

    /**
     * Get rank requirements display
     */
    public static function get_rank_requirements($rank_id = null) {
        if ($rank_id) {
            global $wpdb;
            $table = $wpdb->prefix . 'thaiprompt_mlm_ranks';
            $rank = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $rank_id
            ));

            if ($rank) {
                return self::format_rank_requirements($rank);
            }
        }

        // Get all ranks
        $ranks = Thaiprompt_MLM_Database::get_ranks();
        $formatted = array();

        foreach ($ranks as $rank) {
            $formatted[] = self::format_rank_requirements($rank);
        }

        return $formatted;
    }

    /**
     * Format rank requirements
     */
    private static function format_rank_requirements($rank) {
        return array(
            'id' => $rank->id,
            'name' => $rank->rank_name,
            'order' => $rank->rank_order,
            'color' => $rank->rank_color,
            'icon' => $rank->rank_icon,
            'requirements' => array(
                'personal_sales' => floatval($rank->required_personal_sales),
                'group_sales' => floatval($rank->required_group_sales),
                'active_legs' => intval($rank->required_active_legs)
            ),
            'benefits' => array(
                'bonus_percentage' => floatval($rank->bonus_percentage),
                'bonus_amount' => floatval($rank->bonus_amount)
            )
        );
    }

    /**
     * Get user's progress to next rank
     */
    public static function get_rank_progress($user_id) {
        $current_rank = Thaiprompt_MLM_Database::get_user_rank($user_id);
        $all_ranks = Thaiprompt_MLM_Database::get_ranks();

        // Find next rank
        $next_rank = null;
        foreach ($all_ranks as $rank) {
            if ($rank->rank_order > $current_rank->rank_order) {
                $next_rank = $rank;
                break;
            }
        }

        if (!$next_rank) {
            // Already at highest rank
            return array(
                'current_rank' => self::format_rank_requirements($current_rank),
                'next_rank' => null,
                'progress' => 100,
                'requirements_met' => array()
            );
        }

        $network_data = Thaiprompt_MLM_Database::get_user_network($user_id);

        // Default values if user is not in network yet
        $personal_sales = $network_data ? floatval($network_data->personal_sales) : 0;
        $group_sales = $network_data ? floatval($network_data->group_sales) : 0;

        $active_legs = self::count_active_legs($user_id, $next_rank->required_personal_sales);

        $requirements_met = array(
            'personal_sales' => array(
                'required' => floatval($next_rank->required_personal_sales),
                'current' => $personal_sales,
                'met' => $personal_sales >= $next_rank->required_personal_sales,
                'percentage' => $next_rank->required_personal_sales > 0
                    ? min(100, ($personal_sales / $next_rank->required_personal_sales) * 100)
                    : 100
            ),
            'group_sales' => array(
                'required' => floatval($next_rank->required_group_sales),
                'current' => $group_sales,
                'met' => $group_sales >= $next_rank->required_group_sales,
                'percentage' => $next_rank->required_group_sales > 0
                    ? min(100, ($group_sales / $next_rank->required_group_sales) * 100)
                    : 100
            ),
            'active_legs' => array(
                'required' => intval($next_rank->required_active_legs),
                'current' => $active_legs,
                'met' => $active_legs >= $next_rank->required_active_legs,
                'percentage' => $next_rank->required_active_legs > 0
                    ? min(100, ($active_legs / $next_rank->required_active_legs) * 100)
                    : 100
            )
        );

        // Calculate overall progress
        $total_percentage = (
            $requirements_met['personal_sales']['percentage'] +
            $requirements_met['group_sales']['percentage'] +
            $requirements_met['active_legs']['percentage']
        ) / 3;

        return array(
            'current_rank' => self::format_rank_requirements($current_rank),
            'next_rank' => self::format_rank_requirements($next_rank),
            'progress' => round($total_percentage, 2),
            'requirements_met' => $requirements_met
        );
    }

    /**
     * Schedule rank calculations (run daily)
     */
    public static function schedule_rank_calculations() {
        if (!wp_next_scheduled('thaiprompt_mlm_calculate_ranks')) {
            wp_schedule_event(time(), 'daily', 'thaiprompt_mlm_calculate_ranks');
        }
    }

    /**
     * Run rank calculations for all users
     */
    public static function calculate_all_ranks() {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $users = $wpdb->get_results("SELECT user_id FROM $table");

        foreach ($users as $user) {
            self::check_user_rank($user->user_id);
        }
    }
}
