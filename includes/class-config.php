<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Friendlink_Apply_Config {
    
    private static $instance = null;
    private $table_name;
    private $cache = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'friendlink_config';
        $this->create_table();
    }
    
    private function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            config_key varchar(100) NOT NULL,
            config_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY config_key (config_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function get($key, $default = false) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        global $wpdb;
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT config_value FROM {$this->table_name} WHERE config_key = %s",
            $key
        ));
        
        if ($value === null) {
            $this->cache[$key] = $default;
            return $default;
        }
        
        $decoded = maybe_unserialize($value);
        $this->cache[$key] = $decoded;
        return $decoded;
    }
    
    public function set($key, $value) {
        global $wpdb;
        
        $serialized = maybe_serialize($value);
        
        $result = $wpdb->replace(
            $this->table_name,
            array(
                'config_key' => $key,
                'config_value' => $serialized
            ),
            array('%s', '%s')
        );
        
        $this->cache[$key] = $value;
        
        return $result !== false;
    }
    
    public function delete($key) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('config_key' => $key),
            array('%s')
        );
        
        unset($this->cache[$key]);
        
        return $result !== false;
    }
    
    public function get_all() {
        global $wpdb;
        
        $results = $wpdb->get_results("SELECT config_key, config_value FROM {$this->table_name}");
        
        $config = array();
        foreach ($results as $row) {
            $config[$row->config_key] = maybe_unserialize($row->config_value);
        }
        
        return $config;
    }
    
    public function delete_all() {
        global $wpdb;
        
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        $this->cache = array();
    }
    
    public function migrate_from_options() {
        $options_map = array(
            'wp_friendlink_apply_enable_auto_approve',
            'wp_friendlink_apply_show_friends',
            'wp_friendlink_apply_show_backlink',
            'wp_friendlink_apply_display_style',
            'wp_friendlink_apply_list_title',
        );
        
        foreach ($options_map as $option_name) {
            $value = get_option($option_name);
            if ($value !== false) {
                $this->set($option_name, $value);
                delete_option($option_name);
            }
        }
    }
}
