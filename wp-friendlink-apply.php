<?php
/*
Plugin Name: 友链自助申请
Plugin URI: https://www.x8xx.cn/applink.html
Description: 允许用户自助申请友链，支持一键获取网站信息，自动发送邮件通知
Version: 2.2.0
Author: 树洞笔记
Author URI: https://www.x8xx.cn
Text Domain: wp-friendlink-apply
Requires at least: 5.0
Requires PHP: 5.6
*/

if (!defined('ABSPATH')) {
    exit;
}

define('WP_FRIENDLINK_APPLY_VERSION', '2.2.0');
define('WP_FRIENDLINK_APPLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_FRIENDLINK_APPLY_PLUGIN_URL', plugin_dir_url(__FILE__));



require_once WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'includes/class-config.php';
require_once WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'includes/class-backlink.php';
require_once WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'includes/class-admin.php';
require_once WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'includes/class-frontend.php';
require_once WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'includes/class-email.php';

class WP_Friendlink_Apply {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('WP_Friendlink_Apply', 'uninstall'));
        
        // 添加插件设置链接
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
    }
    
    /**
     * 添加插件操作链接
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-friendlink-apply-frontend-settings') . '">' . __('设置', 'wp-friendlink-apply') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function init() {
        load_plugin_textdomain('wp-friendlink-apply', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // 延迟数据库升级到admin_init，避免在前端输出SQL语句
        add_action('admin_init', array($this, 'upgrade_tables'));
        
        WP_Friendlink_Apply_Admin::get_instance();
        WP_Friendlink_Apply_Frontend::get_instance();
        WP_Friendlink_Apply_Email::get_instance();
        
        add_filter('theme_page_templates', array($this, 'add_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
    }
    

    
    public function activate() {
        $this->create_tables();
        $this->upgrade_tables();
        $this->set_default_options();
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('wp_friendlink_apply_cleanup');
    }
    
    public function add_page_template($templates) {
        $templates['page-template-friendlink-apply.php'] = __('[Sotms]友链申请', 'wp-friendlink-apply');
        return $templates;
    }
    
    public function load_page_template($template) {
        if (!is_page()) {
            return $template;
        }
        
        $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
        
        if ($page_template !== 'page-template-friendlink-apply.php') {
            return $template;
        }
        
        $theme_template = locate_template('page-template-friendlink-apply.php');
        
        if ($theme_template) {
            return $theme_template;
        }
        
        $plugin_template = WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'page-template-friendlink-apply.php';
        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
        
        return $template;
    }
    
    private function create_tables() {
        // 只在后台执行数据库操作
        if (!is_admin()) {
            return;
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'friendlink_applications';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            site_name varchar(255) NOT NULL,
            site_url varchar(255) NOT NULL,
            site_icon varchar(255) DEFAULT NULL,
            site_description text DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            reject_reason text DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function upgrade_tables() {
        // 只在后台执行数据库升级
        if (!is_admin()) {
            return;
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        // 先检查表是否存在
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            // 表不存在，先创建表
            $this->create_tables();
            return;
        }
        
        // 检查 reject_reason 字段是否存在
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_name} LIKE 'reject_reason'"
        );
        
        if (empty($column_exists)) {
            // 添加 reject_reason 字段
            $wpdb->query(
                "ALTER TABLE {$table_name} ADD COLUMN reject_reason text DEFAULT NULL AFTER status"
            );
        }
        
        // 检查 has_backlink 字段是否存在
        $has_backlink_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_name} LIKE 'has_backlink'"
        );
        
        if (empty($has_backlink_exists)) {
            // 添加 has_backlink 字段
            $wpdb->query(
                "ALTER TABLE {$table_name} ADD COLUMN has_backlink tinyint(1) DEFAULT NULL AFTER status"
            );
        }
        
        // 检查 response_time 字段是否存在
        $response_time_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_name} LIKE 'response_time'"
        );
        
        if (empty($response_time_exists)) {
            // 添加 response_time 字段
            $wpdb->query(
                "ALTER TABLE {$table_name} ADD COLUMN response_time float DEFAULT NULL AFTER has_backlink"
            );
        }
    }
    
    private function set_default_options() {
        $config = WP_Friendlink_Apply_Config::get_instance();
        
        $default_options = array(
            'wp_friendlink_apply_enable_auto_approve' => 0,
            'wp_friendlink_apply_show_friends' => 0,
            'wp_friendlink_apply_show_backlink' => 0,
            'wp_friendlink_apply_display_style' => 'card',
            'wp_friendlink_apply_list_title' => '合作伙伴',
            'wp_friendlink_apply_apply_title' => '友链申请',
            'wp_friendlink_apply_apply_description' => '请填写以下信息申请友链',
        );
        
        foreach ($default_options as $key => $value) {
            if ($config->get($key) === false) {
                $config->set($key, $value);
            }
        }
        
        $config->migrate_from_options();
    }
    
    /**
     * 插件卸载函数
     * 删除数据库表和相关选项
     */
    public static function uninstall() {
        global $wpdb;
        
        $applications_table = $wpdb->prefix . 'friendlink_applications';
        $wpdb->query("DROP TABLE IF EXISTS $applications_table");
        
        $config_table = $wpdb->prefix . 'friendlink_config';
        $wpdb->query("DROP TABLE IF EXISTS $config_table");
        
        $options = array(
            'wp_friendlink_apply_enable_auto_approve',
            'wp_friendlink_apply_show_friends',
            'wp_friendlink_apply_show_backlink',
            'wp_friendlink_apply_display_style',
            'wp_friendlink_apply_list_title',
            'wp_friendlink_apply_apply_title',
            'wp_friendlink_apply_apply_description',
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        wp_clear_scheduled_hook('wp_friendlink_apply_cleanup');
    }
}

WP_Friendlink_Apply::get_instance();
