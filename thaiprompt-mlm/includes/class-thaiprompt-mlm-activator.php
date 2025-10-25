<?php
/**
 * Fired during plugin activation
 */
class Thaiprompt_MLM_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Create database tables
        self::create_tables();

        // Set default options
        self::set_default_options();

        // Create default ranks
        self::create_default_ranks();

        // Create required pages
        self::create_pages();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // MLM Network table
        $table_network = $wpdb->prefix . 'thaiprompt_mlm_network';
        $sql_network = "CREATE TABLE $table_network (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            sponsor_id bigint(20) DEFAULT NULL,
            placement_id bigint(20) DEFAULT NULL,
            position varchar(10) DEFAULT NULL,
            level int(11) DEFAULT 1,
            left_count int(11) DEFAULT 0,
            right_count int(11) DEFAULT 0,
            total_downline int(11) DEFAULT 0,
            personal_sales decimal(15,2) DEFAULT 0.00,
            group_sales decimal(15,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id),
            KEY sponsor_id (sponsor_id),
            KEY placement_id (placement_id)
        ) $charset_collate;";
        dbDelta($sql_network);

        // Commissions table
        $table_commissions = $wpdb->prefix . 'thaiprompt_mlm_commissions';
        $sql_commissions = "CREATE TABLE $table_commissions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            from_user_id bigint(20) DEFAULT NULL,
            order_id bigint(20) DEFAULT NULL,
            commission_type varchar(50) NOT NULL,
            amount decimal(15,2) NOT NULL,
            percentage decimal(5,2) DEFAULT NULL,
            level int(11) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY order_id (order_id),
            KEY commission_type (commission_type),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_commissions);

        // Wallet table
        $table_wallet = $wpdb->prefix . 'thaiprompt_mlm_wallet';
        $sql_wallet = "CREATE TABLE $table_wallet (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            balance decimal(15,2) DEFAULT 0.00,
            pending_balance decimal(15,2) DEFAULT 0.00,
            total_earned decimal(15,2) DEFAULT 0.00,
            total_withdrawn decimal(15,2) DEFAULT 0.00,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_wallet);

        // Wallet transactions table
        $table_transactions = $wpdb->prefix . 'thaiprompt_mlm_transactions';
        $sql_transactions = "CREATE TABLE $table_transactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            transaction_type varchar(50) NOT NULL,
            amount decimal(15,2) NOT NULL,
            balance_before decimal(15,2) DEFAULT 0.00,
            balance_after decimal(15,2) DEFAULT 0.00,
            reference_id bigint(20) DEFAULT NULL,
            description text,
            status varchar(20) DEFAULT 'completed',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY transaction_type (transaction_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_transactions);

        // Ranks table
        $table_ranks = $wpdb->prefix . 'thaiprompt_mlm_ranks';
        $sql_ranks = "CREATE TABLE $table_ranks (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            rank_name varchar(100) NOT NULL,
            rank_order int(11) NOT NULL,
            required_personal_sales decimal(15,2) DEFAULT 0.00,
            required_group_sales decimal(15,2) DEFAULT 0.00,
            required_active_legs int(11) DEFAULT 0,
            bonus_percentage decimal(5,2) DEFAULT 0.00,
            bonus_amount decimal(15,2) DEFAULT 0.00,
            rank_color varchar(20) DEFAULT '#3498db',
            rank_icon varchar(255) DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY rank_order (rank_order)
        ) $charset_collate;";
        dbDelta($sql_ranks);

        // User ranks table
        $table_user_ranks = $wpdb->prefix . 'thaiprompt_mlm_user_ranks';
        $sql_user_ranks = "CREATE TABLE $table_user_ranks (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            rank_id bigint(20) NOT NULL,
            achieved_at datetime DEFAULT CURRENT_TIMESTAMP,
            is_current tinyint(1) DEFAULT 1,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY rank_id (rank_id)
        ) $charset_collate;";
        dbDelta($sql_user_ranks);

        // Product settings table
        $table_product_settings = $wpdb->prefix . 'thaiprompt_mlm_product_settings';
        $sql_product_settings = "CREATE TABLE $table_product_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            mlm_enabled tinyint(1) DEFAULT 1,
            commission_type varchar(50) DEFAULT 'percentage',
            commission_value decimal(15,2) DEFAULT 0.00,
            fast_start_enabled tinyint(1) DEFAULT 0,
            fast_start_value decimal(15,2) DEFAULT 0.00,
            max_level int(11) DEFAULT 10,
            settings_override text,
            PRIMARY KEY  (id),
            UNIQUE KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql_product_settings);

        // Withdrawals table
        $table_withdrawals = $wpdb->prefix . 'thaiprompt_mlm_withdrawals';
        $sql_withdrawals = "CREATE TABLE $table_withdrawals (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            method varchar(50) NOT NULL,
            bank_name varchar(255) DEFAULT NULL,
            account_number varchar(255) DEFAULT NULL,
            account_name varchar(255) DEFAULT NULL,
            payment_details text,
            status varchar(20) DEFAULT 'pending',
            requested_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime DEFAULT NULL,
            processed_by bigint(20) DEFAULT NULL,
            admin_note text,
            rejection_reason text,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY requested_at (requested_at)
        ) $charset_collate;";
        dbDelta($sql_withdrawals);

        // Update database version
        update_option('thaiprompt_mlm_db_version', THAIPROMPT_MLM_DB_VERSION);
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        $default_settings = array(
            'placement_type' => 'auto', // auto, left, right
            'max_level' => 10,
            'commission_type' => 'percentage',
            'fast_start_enabled' => true,
            'fast_start_percentage' => 10,
            'fast_start_days' => 30,
            'rank_bonus_enabled' => true,
            'payout_minimum' => 100,
            'payout_schedule' => 'monthly',
            'currency' => 'THB',
            'genealogy_animation' => true,
            'woocommerce_integration' => true,
            'dokan_integration' => true,
            'level_commissions' => array(
                1 => 10,
                2 => 5,
                3 => 3,
                4 => 2,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 0.5,
                9 => 0.5,
                10 => 0.5
            )
        );

        add_option('thaiprompt_mlm_settings', $default_settings);
    }

    /**
     * Create default ranks
     */
    private static function create_default_ranks() {
        global $wpdb;

        $table_ranks = $wpdb->prefix . 'thaiprompt_mlm_ranks';

        $default_ranks = array(
            array(
                'rank_name' => 'Member',
                'rank_order' => 1,
                'required_personal_sales' => 0,
                'required_group_sales' => 0,
                'required_active_legs' => 0,
                'bonus_percentage' => 0,
                'bonus_amount' => 0,
                'rank_color' => '#95a5a6'
            ),
            array(
                'rank_name' => 'Bronze',
                'rank_order' => 2,
                'required_personal_sales' => 10000,
                'required_group_sales' => 50000,
                'required_active_legs' => 2,
                'bonus_percentage' => 2,
                'bonus_amount' => 1000,
                'rank_color' => '#cd7f32'
            ),
            array(
                'rank_name' => 'Silver',
                'rank_order' => 3,
                'required_personal_sales' => 30000,
                'required_group_sales' => 150000,
                'required_active_legs' => 3,
                'bonus_percentage' => 3,
                'bonus_amount' => 3000,
                'rank_color' => '#c0c0c0'
            ),
            array(
                'rank_name' => 'Gold',
                'rank_order' => 4,
                'required_personal_sales' => 50000,
                'required_group_sales' => 300000,
                'required_active_legs' => 4,
                'bonus_percentage' => 5,
                'bonus_amount' => 5000,
                'rank_color' => '#ffd700'
            ),
            array(
                'rank_name' => 'Platinum',
                'rank_order' => 5,
                'required_personal_sales' => 100000,
                'required_group_sales' => 500000,
                'required_active_legs' => 5,
                'bonus_percentage' => 7,
                'bonus_amount' => 10000,
                'rank_color' => '#e5e4e2'
            ),
            array(
                'rank_name' => 'Diamond',
                'rank_order' => 6,
                'required_personal_sales' => 200000,
                'required_group_sales' => 1000000,
                'required_active_legs' => 6,
                'bonus_percentage' => 10,
                'bonus_amount' => 20000,
                'rank_color' => '#b9f2ff'
            )
        );

        foreach ($default_ranks as $rank) {
            $wpdb->insert($table_ranks, $rank);
        }
    }

    /**
     * Create required pages
     */
    private static function create_pages() {
        $pages = array(
            'portal' => array(
                'title' => __('MLM Portal', 'thaiprompt-mlm'),
                'content' => '',
                'slug' => 'mlm-portal',
                'template' => 'mlm-portal-template.php'
            ),
            'mlm_dashboard' => array(
                'title' => __('MLM Dashboard', 'thaiprompt-mlm'),
                'content' => '[mlm_dashboard]',
                'slug' => 'mlm-dashboard'
            ),
            'mlm_genealogy' => array(
                'title' => __('My Genealogy', 'thaiprompt-mlm'),
                'content' => '[mlm_genealogy]',
                'slug' => 'mlm-genealogy'
            ),
            'mlm_network' => array(
                'title' => __('My Network', 'thaiprompt-mlm'),
                'content' => '[mlm_team_stats]<br>[mlm_referral_link]',
                'slug' => 'mlm-network'
            ),
            'mlm_wallet' => array(
                'title' => __('My Wallet', 'thaiprompt-mlm'),
                'content' => '[mlm_wallet]',
                'slug' => 'mlm-wallet'
            ),
            'mlm_commissions' => array(
                'title' => __('My Commissions', 'thaiprompt-mlm'),
                'content' => '[mlm_commissions]',
                'slug' => 'mlm-commissions'
            ),
            'mlm_rank' => array(
                'title' => __('My Rank Progress', 'thaiprompt-mlm'),
                'content' => '[mlm_rank_progress]',
                'slug' => 'mlm-rank-progress'
            ),
            'mlm_leaderboard' => array(
                'title' => __('Leaderboard', 'thaiprompt-mlm'),
                'content' => '[mlm_leaderboard]',
                'slug' => 'mlm-leaderboard'
            )
        );

        foreach ($pages as $page_key => $page_data) {
            // Check if page already exists
            $page_id = get_option('thaiprompt_mlm_page_' . $page_key);

            if (!$page_id || !get_post($page_id)) {
                // Create page
                $new_page = array(
                    'post_title'    => $page_data['title'],
                    'post_content'  => $page_data['content'],
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_name'     => $page_data['slug'],
                    'post_author'   => 1,
                    'comment_status' => 'closed'
                );

                $page_id = wp_insert_post($new_page);

                if ($page_id) {
                    // Save page ID
                    update_option('thaiprompt_mlm_page_' . $page_key, $page_id);

                    // Set page template if specified
                    if (isset($page_data['template'])) {
                        update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                    }
                }
            }
        }
    }
}
