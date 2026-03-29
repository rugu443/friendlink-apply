<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Friendlink_Apply_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('manage_link-manager_columns', array($this, 'add_link_columns'));
        add_action('manage_link_custom_column', array($this, 'display_link_columns'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wp_friendlink_approve', array($this, 'ajax_approve_application'));
        add_action('wp_ajax_wp_friendlink_reject', array($this, 'ajax_reject_application'));
        add_action('wp_ajax_wp_friendlink_delete', array($this, 'ajax_delete_application'));
        add_action('wp_ajax_wp_friendlink_check_backlink', array($this, 'ajax_check_backlink'));
        add_action('wp_ajax_wp_friendlink_bulk_approve', array($this, 'ajax_bulk_approve'));
        add_action('wp_ajax_wp_friendlink_bulk_reject', array($this, 'ajax_bulk_reject'));
        add_action('wp_ajax_wp_friendlink_bulk_delete', array($this, 'ajax_bulk_delete'));
        add_action('wp_ajax_wp_friendlink_bulk_check', array($this, 'ajax_bulk_check'));
        add_action('wp_ajax_wp_friendlink_check_site_status', array($this, 'ajax_check_site_status'));
        add_action('wp_ajax_wp_friendlink_bulk_check_links', array($this, 'ajax_bulk_check_links'));
        add_action('wp_ajax_wp_friendlink_bulk_enable_links', array($this, 'ajax_bulk_enable_links'));
        add_action('wp_ajax_wp_friendlink_bulk_disable_links', array($this, 'ajax_bulk_disable_links'));
        add_action('wp_ajax_wp_friendlink_bulk_delete_links', array($this, 'ajax_bulk_delete_links'));
        add_action('wp_ajax_wp_friendlink_toggle_link', array($this, 'ajax_toggle_link'));
        add_action('wp_ajax_wp_friendlink_delete_link', array($this, 'ajax_delete_link'));
        add_action('wp_ajax_wp_friendlink_update_link', array($this, 'ajax_update_link'));
        add_filter('get_bookmarks', array($this, 'modify_link_display'), 10, 2);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('友链申请', 'wp-friendlink-apply'),
            __('友链申请', 'wp-friendlink-apply'),
            'manage_options',
            'wp-friendlink-apply',
            array($this, 'render_applications_page'),
            'dashicons-admin-links',
            99
        );
        
        add_submenu_page(
            'wp-friendlink-apply',
            __('申请列表', 'wp-friendlink-apply'),
            __('申请列表', 'wp-friendlink-apply'),
            'manage_options',
            'wp-friendlink-apply',
            array($this, 'render_applications_page')
        );
        
        add_submenu_page(
            'wp-friendlink-apply',
            __('所有链接', 'wp-friendlink-apply'),
            __('所有链接', 'wp-friendlink-apply'),
            'manage_options',
            'wp-friendlink-apply-links',
            array($this, 'render_links_page')
        );
        
        add_submenu_page(
            'wp-friendlink-apply',
            __('插件设置', 'wp-friendlink-apply'),
            __('插件设置', 'wp-friendlink-apply'),
            'manage_options',
            'wp-friendlink-apply-frontend-settings',
            array($this, 'render_frontend_settings_page')
        );
        
        add_submenu_page(
            'wp-friendlink-apply',
            __('使用说明', 'wp-friendlink-apply'),
            __('使用说明', 'wp-friendlink-apply'),
            'manage_options',
            'wp-friendlink-apply-help',
            array($this, 'render_help_page')
        );
    }
    
    public function register_settings() {
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_show_friends', array(
            'type' => 'integer',
            'sanitize_callback' => 'intval'
        ));
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_show_backlink', array(
            'type' => 'integer',
            'sanitize_callback' => 'intval'
        ));
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_enable_auto_approve');
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_display_style');
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_list_title');
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_apply_title');
        register_setting('wp_friendlink_apply_settings', 'wp_friendlink_apply_apply_description');
    }
    
    public function render_applications_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        $applications = $wpdb->get_results(
            "SELECT id, user_id, site_name, site_url, site_icon, site_description, email, status, has_backlink, response_time, reject_reason, created_at, updated_at FROM {$table_name} ORDER BY created_at DESC"
        );
        
        include WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'templates/admin-applications.php';
    }
    
    public function render_frontend_settings_page() {
        include WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'templates/admin-frontend-settings.php';
    }
    
    public function render_help_page() {
        include WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'templates/admin-help.php';
    }
    
    public function render_links_page() {
        include WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'templates/admin-links.php';
    }
    
    public function add_link_columns($columns) {
        $columns['friendlink_status'] = __('友链状态', 'wp-friendlink-apply');
        $columns['friendlink_applicant'] = __('申请人', 'wp-friendlink-apply');
        return $columns;
    }
    
    public function display_link_columns($column, $link_id) {
        global $wpdb;
        
        $link = get_bookmark($link_id);
        
        if (!$link) {
            return;
        }
        
        if ($column === 'friendlink_status') {
            $table_name = $wpdb->prefix . 'friendlink_applications';
            $application = $wpdb->get_row($wpdb->prepare(
                "SELECT status FROM $table_name WHERE site_url = %s ORDER BY created_at DESC LIMIT 1",
                $link->link_url
            ));
            
            if ($application) {
                $status_labels = array(
                    'pending' => __('待审核', 'wp-friendlink-apply'),
                    'approved' => __('已通过', 'wp-friendlink-apply'),
                    'rejected' => __('已拒绝', 'wp-friendlink-apply')
                );
                echo isset($status_labels[$application->status]) ? $status_labels[$application->status] : $application->status;
            } else {
                echo __('无申请记录', 'wp-friendlink-apply');
            }
        }
        
        if ($column === 'friendlink_applicant') {
            $table_name = $wpdb->prefix . 'friendlink_applications';
            $application = $wpdb->get_row($wpdb->prepare(
                "SELECT email, user_id FROM $table_name WHERE site_url = %s ORDER BY created_at DESC LIMIT 1",
                $link->link_url
            ));
            
            if ($application) {
                if ($application->user_id) {
                    $user = get_userdata($application->user_id);
                    if ($user) {
                        echo esc_html($user->display_name);
                    }
                } else {
                    echo esc_html($application->email);
                }
            } else {
                echo esc_html($link->link_owner);
            }
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wp-friendlink-apply') !== false) {
            wp_enqueue_style(
                'wp-friendlink-apply-admin',
                WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_FRIENDLINK_APPLY_VERSION
            );
            
            wp_enqueue_script(
                'wp-friendlink-apply-admin',
                WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                WP_FRIENDLINK_APPLY_VERSION,
                true
            );
            
            wp_localize_script('wp-friendlink-apply-admin', 'wpFriendlinkApply', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp-friendlink-apply-nonce'),
                'strings' => array(
                    'confirm_approve' => __('确定通过此申请？', 'wp-friendlink-apply'),
                    'confirm_reject' => __('确定拒绝此申请？', 'wp-friendlink-apply'),
                    'confirm_delete' => __('确定删除此申请？', 'wp-friendlink-apply'),
                    'processing' => __('处理中...', 'wp-friendlink-apply'),
                    'success' => __('操作成功', 'wp-friendlink-apply'),
                    'error' => __('操作失败', 'wp-friendlink-apply')
                )
            ));
        }
    }
    
    public function ajax_approve_application() {
        $this->verify_ajax_request();
        
        $application_id = $this->get_application_id();
        $application = $this->get_application($application_id);
        
        if (!$application) {
            wp_send_json_error(array('message' => __('申请不存在', 'wp-friendlink-apply')));
        }
        
        $link_id = $this->create_link($application);
        
        if (is_wp_error($link_id)) {
            wp_send_json_error(array('message' => $link_id->get_error_message()));
        }
        
        $this->update_application_status($application_id, 'approved');
        do_action('wp_friendlink_application_approved', $application, $link_id);
        
        wp_send_json_success(array('message' => __('申请已通过', 'wp-friendlink-apply')));
    }
    
    public function ajax_reject_application() {
        $this->verify_ajax_request();
        
        $application_id = $this->get_application_id();
        $reject_reason = isset($_POST['reject_reason']) ? sanitize_textarea_field($_POST['reject_reason']) : '';
        
        $application = $this->get_application($application_id);
        
        if (!$application) {
            wp_send_json_error(array('message' => __('申请不存在', 'wp-friendlink-apply')));
        }
        
        $updated = $this->update_application_status($application_id, 'rejected', $reject_reason);
        
        // $wpdb->update 返回 false 表示SQL错误，返回 0 表示没有行被更新（数据相同或条件不匹配）
        // 返回 1 或更大的数字表示成功更新了相应数量的行
        if ($updated === false) {
            global $wpdb;
            wp_send_json_error(array('message' => __('操作失败：', 'wp-friendlink-apply') . $wpdb->last_error));
        }
        
        // 重新获取申请信息以确保数据最新
        $application = $this->get_application($application_id);
        $application->reject_reason = $reject_reason;
        do_action('wp_friendlink_application_rejected', $application);
        
        wp_send_json_success(array('message' => __('申请已拒绝', 'wp-friendlink-apply')));
    }
    
    public function ajax_delete_application() {
        $this->verify_ajax_request();
        
        $application_id = $this->get_application_id();
        
        $deleted = $this->delete_application($application_id);
        
        if ($deleted === false) {
            wp_send_json_error(array('message' => __('删除失败', 'wp-friendlink-apply')));
        }
        
        wp_send_json_success(array('message' => __('申请已删除', 'wp-friendlink-apply')));
    }
    
    private function verify_ajax_request() {
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        
        if (!wp_verify_nonce($nonce, 'wp-friendlink-apply-nonce')) {
            wp_send_json_error(array('message' => __('安全验证失败', 'wp-friendlink-apply')));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('权限不足', 'wp-friendlink-apply')));
        }
    }
    
    private function get_application_id() {
        $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
        
        if (!$application_id) {
            wp_send_json_error(array('message' => __('无效的申请ID', 'wp-friendlink-apply')));
        }
        
        return $application_id;
    }
    
    private function get_application($application_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $application_id
        ));
    }
    
    private function create_link($application) {
        $link_data = array(
            'link_name' => $application->site_name,
            'link_url' => $application->site_url,
            'link_description' => $application->site_description,
            'link_image' => $application->site_icon,
            'link_owner' => $application->email,
            'link_visible' => 'Y'
        );
        
        return wp_insert_link($link_data);
    }
    
    private function update_application_status($application_id, $status, $reject_reason = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        $update_data = array('status' => $status);
        
        if (!empty($reject_reason)) {
            $update_data['reject_reason'] = $reject_reason;
        }
        
        return $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $application_id)
        );
    }
    
    private function delete_application($application_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $application_id),
            array('%d')
        );
    }
    
    public function ajax_check_backlink() {
        $this->verify_ajax_request();
        
        $site_url = isset($_POST['site_url']) ? esc_url_raw($_POST['site_url']) : '';
        
        if (empty($site_url)) {
            wp_send_json_error(array('message' => __('请提供网站地址', 'wp-friendlink-apply')));
        }
        
        if (!filter_var($site_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('请提供有效的网站地址', 'wp-friendlink-apply')));
        }
        
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        $result = $backlink_checker->check_backlink($site_url);
        
        wp_send_json_success(array(
            'has_backlink' => $result['has_backlink'],
            'message' => $result['has_backlink'] ? __('检测到回链', 'wp-friendlink-apply') : __('未检测到回链', 'wp-friendlink-apply'),
            'source' => $result['source'],
            'debug' => $result['debug']
        ));
    }
    
    public function modify_link_display($results, $args) {
        if (is_array($results)) {
            foreach ($results as $link) {
                if (isset($link->link_name)) {
                    $link_name = $link->link_name;
                    
                    if (strpos($link_name, '-') !== false) {
                        $parts = explode(' - ', $link_name);
                        if (count($parts) > 1) {
                            $link->link_name = $parts[0];
                        }
                    }
                }
            }
        }
        return $results;
    }
    
    public function ajax_bulk_approve() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的申请', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $application = $this->get_application($id);
            if ($application && $application->status !== 'approved') {
                $link_id = $this->create_link($application);
                if ($link_id && !is_wp_error($link_id)) {
                    $this->update_application_status($id, 'approved');
                    $success_count++;
                }
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功通过 %d 个申请', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_bulk_reject() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的申请', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $application = $this->get_application($id);
            if ($application && $application->status !== 'rejected') {
                $this->update_application_status($id, 'rejected');
                $success_count++;
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功拒绝 %d 个申请', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_bulk_delete() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的申请', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $deleted = $this->delete_application($id);
            if ($deleted !== false) {
                $success_count++;
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功删除 %d 个申请', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_bulk_check() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的申请', 'wp-friendlink-apply')));
        }
        
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        $results = array();
        
        foreach ($ids as $id) {
            $application = $this->get_application($id);
            if ($application) {
                $has_backlink = $backlink_checker->check_backlink($application->site_url);
                $results[] = array(
                    'id' => $id,
                    'site_name' => $application->site_name,
                    'site_url' => $application->site_url,
                    'has_backlink' => $has_backlink
                );
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('完成 %d 个站点的回链检测', 'wp-friendlink-apply'), count($results)),
            'results' => $results
        ));
    }
    
    public function ajax_check_site_status() {
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        
        if (!wp_verify_nonce($nonce, 'wp-friendlink-apply-nonce')) {
            wp_send_json_error(array('message' => __('安全验证失败', 'wp-friendlink-apply')));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('权限不足', 'wp-friendlink-apply')));
        }
        
        $site_url = isset($_POST['site_url']) ? esc_url_raw($_POST['site_url']) : '';
        
        if (empty($site_url)) {
            wp_send_json_error(array('message' => __('请提供网站地址', 'wp-friendlink-apply')));
        }
        
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        $site_info = $backlink_checker->get_site_info($site_url);
        
        $status_labels = array(
            'ok' => __('正常', 'wp-friendlink-apply'),
            'client_error' => __('客户端错误', 'wp-friendlink-apply'),
            'server_error' => __('服务器错误', 'wp-friendlink-apply'),
            'error' => __('无法访问', 'wp-friendlink-apply')
        );
        
        $response_time = $site_info['response_time'];
        $speed_class = $response_time < 1000 ? 'fast' : ($response_time < 3000 ? 'medium' : 'slow');
        
        wp_send_json_success(array(
            'status' => $site_info['site_status'],
            'status_label' => isset($status_labels[$site_info['site_status']]) ? $status_labels[$site_info['site_status']] : $site_info['site_status'],
            'response_time' => round($response_time / 1000, 2) . 's',
            'speed_class' => $speed_class
        ));
    }
    
    public function ajax_bulk_check_links() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的链接', 'wp-friendlink-apply')));
        }
        
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        $results = array();
        
        foreach ($ids as $id) {
            $link = get_bookmark($id);
            if ($link) {
                $has_backlink = $backlink_checker->check_backlink($link->link_url);
                $results[] = array(
                    'id' => $id,
                    'site_name' => $link->link_name,
                    'site_url' => $link->link_url,
                    'has_backlink' => $has_backlink['has_backlink']
                );
            }
        }
        
        $normal_count = 0;
        $abnormal_count = 0;
        foreach ($results as $r) {
            if ($r['has_backlink']) {
                $normal_count++;
            } else {
                $abnormal_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('检测完成：正常 %d 个，异常 %d 个', 'wp-friendlink-apply'), $normal_count, $abnormal_count),
            'results' => $results
        ));
    }
    
    public function ajax_bulk_enable_links() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的链接', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $link = get_bookmark($id);
            if ($link) {
                wp_update_link(array(
                    'link_id' => $id,
                    'link_visible' => 'Y'
                ));
                $success_count++;
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功启用 %d 个链接', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_bulk_disable_links() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的链接', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $link = get_bookmark($id);
            if ($link) {
                wp_update_link(array(
                    'link_id' => $id,
                    'link_visible' => 'N'
                ));
                $success_count++;
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功禁用 %d 个链接', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_bulk_delete_links() {
        $this->verify_ajax_request();
        
        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
        
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('请选择要操作的链接', 'wp-friendlink-apply')));
        }
        
        $success_count = 0;
        foreach ($ids as $id) {
            $deleted = wp_delete_link($id);
            if ($deleted) {
                $success_count++;
            }
        }
        
        wp_send_json_success(array('message' => sprintf(__('成功删除 %d 个链接', 'wp-friendlink-apply'), $success_count)));
    }
    
    public function ajax_toggle_link() {
        $this->verify_ajax_request();
        
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $visible = isset($_POST['visible']) ? sanitize_text_field($_POST['visible']) : 'Y';
        
        if (empty($link_id)) {
            wp_send_json_error(array('message' => __('请提供链接ID', 'wp-friendlink-apply')));
        }
        
        $link = get_bookmark($link_id);
        if (!$link) {
            wp_send_json_error(array('message' => __('链接不存在', 'wp-friendlink-apply')));
        }
        
        $result = wp_update_link(array(
            'link_id' => $link_id,
            'link_visible' => $visible
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => $visible === 'Y' ? __('链接已启用', 'wp-friendlink-apply') : __('链接已禁用', 'wp-friendlink-apply')
        ));
    }
    
    public function ajax_delete_link() {
        $this->verify_ajax_request();
        
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (empty($link_id)) {
            wp_send_json_error(array('message' => __('请提供链接ID', 'wp-friendlink-apply')));
        }
        
        $deleted = wp_delete_link($link_id);
        
        if (!$deleted) {
            wp_send_json_error(array('message' => __('删除失败', 'wp-friendlink-apply')));
        }
        
        wp_send_json_success(array('message' => __('链接已删除', 'wp-friendlink-apply')));
    }
    
    public function ajax_update_link() {
        $this->verify_ajax_request();
        
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (empty($link_id)) {
            wp_send_json_error(array('message' => __('请提供链接ID', 'wp-friendlink-apply')));
        }
        
        $link = get_bookmark($link_id);
        if (!$link) {
            wp_send_json_error(array('message' => __('链接不存在', 'wp-friendlink-apply')));
        }
        
        $link_data = array(
            'link_id' => $link_id,
            'link_name' => isset($_POST['link_name']) ? sanitize_text_field($_POST['link_name']) : $link->link_name,
            'link_url' => isset($_POST['link_url']) ? esc_url_raw($_POST['link_url']) : $link->link_url,
            'link_description' => isset($_POST['link_description']) ? sanitize_textarea_field($_POST['link_description']) : $link->link_description,
            'link_image' => isset($_POST['link_image']) ? esc_url_raw($_POST['link_image']) : $link->link_image
        );
        
        $result = wp_update_link($link_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'friendlink_applications';
        $wpdb->update(
            $table_name,
            array(
                'site_name' => $link_data['link_name'],
                'site_url' => $link_data['link_url'],
                'site_description' => $link_data['link_description']
            ),
            array('site_url' => $link->link_url)
        );
        
        wp_send_json_success(array('message' => __('链接信息已更新', 'wp-friendlink-apply')));
    }
}
