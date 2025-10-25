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
     * Upgrade database only (for auto-updates without flushing rewrite rules)
     */
    public static function upgrade_database() {
        // Create/update database tables
        self::create_tables();

        // Create default ranks if they don't exist
        global $wpdb;
        $table_ranks = $wpdb->prefix . 'thaiprompt_mlm_ranks';
        $rank_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_ranks");

        if ($rank_count == 0) {
            self::create_default_ranks();
        }

        // Create pages if they don't exist
        self::create_pages();
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

        // Landing Pages table
        $table_landing_pages = $wpdb->prefix . 'thaiprompt_mlm_landing_pages';
        $sql_landing_pages = "CREATE TABLE $table_landing_pages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            headline text,
            description longtext,
            image1_url varchar(500) DEFAULT NULL,
            image2_url varchar(500) DEFAULT NULL,
            image3_url varchar(500) DEFAULT NULL,
            cta_text varchar(100) DEFAULT 'Join Now',
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            views bigint(20) DEFAULT 0,
            conversions bigint(20) DEFAULT 0,
            is_active tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            approved_at datetime DEFAULT NULL,
            approved_by bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql_landing_pages);

        // Landing Page Versions table
        $table_landing_versions = $wpdb->prefix . 'thaiprompt_mlm_landing_page_versions';
        $sql_landing_versions = "CREATE TABLE $table_landing_versions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            landing_page_id bigint(20) NOT NULL,
            version int NOT NULL DEFAULT 1,
            title varchar(255) NOT NULL,
            headline text NOT NULL,
            description longtext NOT NULL,
            image1_url varchar(500) DEFAULT NULL,
            image2_url varchar(500) DEFAULT NULL,
            image3_url varchar(500) DEFAULT NULL,
            cta_text varchar(100) DEFAULT NULL,
            edited_by bigint(20) NOT NULL,
            edited_at datetime DEFAULT CURRENT_TIMESTAMP,
            change_note text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY landing_page_id (landing_page_id),
            KEY version (version),
            KEY edited_at (edited_at)
        ) $charset_collate;";
        dbDelta($sql_landing_versions);

        // Landing Page Templates table
        $table_landing_templates = $wpdb->prefix . 'thaiprompt_mlm_landing_templates';
        $sql_landing_templates = "CREATE TABLE $table_landing_templates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            template_name varchar(255) NOT NULL,
            template_description text,
            thumbnail_url varchar(500) DEFAULT NULL,
            layout_type varchar(50) DEFAULT 'modern',
            color_scheme varchar(50) DEFAULT 'purple',
            default_title varchar(255) NOT NULL,
            default_headline text NOT NULL,
            default_description longtext NOT NULL,
            default_cta varchar(100) DEFAULT 'Join Now',
            is_active tinyint(1) DEFAULT 1,
            sort_order int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        dbDelta($sql_landing_templates);

        // Scheduled Transfers table
        $table_scheduled_transfers = $wpdb->prefix . 'thaiprompt_mlm_scheduled_transfers';
        $sql_scheduled_transfers = "CREATE TABLE $table_scheduled_transfers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            from_user_id bigint(20) DEFAULT NULL,
            to_user_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            schedule_datetime datetime NOT NULL,
            repeat_type varchar(20) DEFAULT 'once',
            next_execution_at datetime DEFAULT NULL,
            note text DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_executed_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY from_user_id (from_user_id),
            KEY to_user_id (to_user_id),
            KEY schedule_datetime (schedule_datetime),
            KEY status (status),
            KEY next_execution_at (next_execution_at)
        ) $charset_collate;";
        dbDelta($sql_scheduled_transfers);

        // Create default landing page templates
        self::create_default_landing_templates();

        // Create default top-up products
        self::create_default_topup_products();

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
     * Create MLM Portal page
     */
    private static function create_pages() {
        $pages = array(
            'portal' => array(
                'title' => __('MLM Portal', 'thaiprompt-mlm'),
                'content' => '',
                'slug' => 'mlm-portal',
                'template' => 'mlm-portal-template.php'
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

    /**
     * Create default landing page templates
     */
    private static function create_default_landing_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_landing_templates';

        $templates = array(
            array(
                'template_name' => 'Modern Business',
                'template_description' => 'เทมเพลตธุรกิจสมัยใหม่ พร้อมภาพและคำกระตุ้นการตัดสินใจ',
                'layout_type' => 'modern',
                'color_scheme' => 'purple',
                'default_title' => 'เริ่มต้นธุรกิจออนไลน์กับเรา',
                'default_headline' => 'สร้างรายได้เสริม ทำงานที่ไหนก็ได้ เมื่อไหร่ก็ได้',
                'default_description' => 'ร่วมเป็นส่วนหนึ่งของครอบครัว MLM ที่ใหญ่ที่สุด พร้อมระบบที่ทันสมัย รายได้ชัดเจน และโอกาสไม่จำกัด',
                'default_cta' => 'สมัครเลย',
                'sort_order' => 1
            ),
            array(
                'template_name' => 'Minimalist',
                'template_description' => 'เทมเพลตเรียบง่าย เน้นเนื้อหา อ่านง่าย',
                'layout_type' => 'minimalist',
                'color_scheme' => 'blue',
                'default_title' => 'โอกาสทองสำหรับคุณ',
                'default_headline' => 'เข้าร่วมเครือข่ายธุรกิจที่เติบโตเร็วที่สุด',
                'default_description' => 'ระบบรายได้แบบหลายชั้น พร้อมการสนับสนุนจากทีมงานมืออาชีพ เริ่มต้นได้ง่าย สร้างรายได้ได้จริง',
                'default_cta' => 'เข้าร่วมเลย',
                'sort_order' => 2
            ),
            array(
                'template_name' => 'Bold Impact',
                'template_description' => 'เทมเพลตสะดุดตา โดดเด่น เหมาะกับการโปรโมท',
                'layout_type' => 'bold',
                'color_scheme' => 'green',
                'default_title' => 'เปลี่ยนชีวิตคุณวันนี้',
                'default_headline' => 'รายได้ไม่จำกัด ทำงานยืดหยุ่น มีอิสระ',
                'default_description' => 'ระบบการตลาดแบบเครือข่ายที่ช่วยให้คุณสร้างรายได้แบบ Passive Income พร้อมทีมงานที่พร้อมสนับสนุนตลอดเวลา',
                'default_cta' => 'ลงทะเบียนฟรี',
                'sort_order' => 3
            )
        );

        foreach ($templates as $template) {
            $wpdb->insert($table, $template);
        }
    }

    /**
     * Create default wallet top-up products
     */
    private static function create_default_topup_products() {
        // Only if WooCommerce is active
        if (!class_exists('WC_Product')) {
            return;
        }

        // Create products for default amounts
        $amounts = array(100, 500, 1000, 2000, 5000, 10000);

        foreach ($amounts as $amount) {
            Thaiprompt_MLM_Wallet_Topup::get_or_create_wallet_product($amount);
        }
    }
}
