<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Friendlink_Apply_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('friendlink_apply_form', array($this, 'render_form_shortcode'));
        add_shortcode('friendlink_list', array($this, 'render_list_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_nopriv_wp_friendlink_submit_application', array($this, 'ajax_submit_application'));
        add_action('wp_ajax_wp_friendlink_submit_application', array($this, 'ajax_submit_application'));
        add_action('wp_ajax_nopriv_wp_friendlink_fetch_site_info', array($this, 'ajax_fetch_site_info'));
        add_action('wp_ajax_wp_friendlink_fetch_site_info', array($this, 'ajax_fetch_site_info'));
        add_action('wp_ajax_wp_friendlink_get_friends', array($this, 'ajax_get_friends'));
        add_action('wp_ajax_nopriv_wp_friendlink_get_friends', array($this, 'ajax_get_friends'));
        add_action('wp_ajax_wp_friendlink_check_single', array($this, 'ajax_check_single_backlink'));
        add_action('wp_ajax_nopriv_wp_friendlink_check_single', array($this, 'ajax_check_single_backlink'));
    }
    
    public function enqueue_frontend_scripts() {
        $load_assets = false;
        
        if (is_page_template('page-template-friendlink-apply.php')) {
            $load_assets = true;
        }
        
        if (!$load_assets) {
            global $post;
            if (is_a($post, 'WP_Post')) {
                if (has_shortcode($post->post_content, 'friendlink_apply_form') || 
                    has_shortcode($post->post_content, 'friendlink_list')) {
                    $load_assets = true;
                }
            }
        }
        
        if (!$load_assets && isset($_GET['page']) && $_GET['page'] === 'wp-friendlink-apply') {
            $load_assets = true;
        }
        
        if ($load_assets) {
            $this->load_frontend_assets();
        }
    }
    
    private function load_frontend_assets() {
        $config = WP_Friendlink_Apply_Config::get_instance();
        
        wp_enqueue_style(
            'wp-friendlink-apply-frontend',
            WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WP_FRIENDLINK_APPLY_VERSION
        );
        
        wp_enqueue_script(
            'wp-friendlink-apply-frontend',
            WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WP_FRIENDLINK_APPLY_VERSION,
            true
        );
        
        wp_localize_script('wp-friendlink-apply-frontend', 'wpFriendlinkApply', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-friendlink-apply-nonce'),
            'is_logged_in' => is_user_logged_in(),
            'show_backlink' => $config->get('wp_friendlink_apply_show_backlink', 0),
            'strings' => array(
                'required_fields' => __('请填写所有必填字段', 'wp-friendlink-apply'),
                'invalid_url' => __('请输入有效的网站地址', 'wp-friendlink-apply'),
                'invalid_email' => __('请输入有效的邮箱地址', 'wp-friendlink-apply'),
                'submitting' => __('提交中...', 'wp-friendlink-apply'),
                'success' => __('申请提交成功！请注意邮件！', 'wp-friendlink-apply'),
                'error' => __('提交失败，请稍后重试', 'wp-friendlink-apply'),
                'fetching' => __('获取中...', 'wp-friendlink-apply'),
                'fetch_error' => __('获取网站信息失败', 'wp-friendlink-apply'),
                'fetch_success' => __('网站信息获取成功！', 'wp-friendlink-apply')
            )
        ));
        
        add_action('wp_footer', array($this, 'add_umami_tracking'));
    }
    
    public function add_umami_tracking() {
        ?>
        <script defer src="https://umami.x8xx.cn/analytics.js" data-website-id="9287e91f-1a27-407a-8ffa-f01e2605b4b5"></script>
        <?php
    }
    
    public function render_form_shortcode($atts) {
        $config = WP_Friendlink_Apply_Config::get_instance();
        $atts = shortcode_atts(array(
            'title' => $config->get('wp_friendlink_apply_apply_title', '友链申请'),
            'description' => $config->get('wp_friendlink_apply_apply_description', '请填写以下信息申请友链')
        ), $atts);
        
        ob_start();
        include WP_FRIENDLINK_APPLY_PLUGIN_DIR . 'templates/frontend-form.php';
        return ob_get_clean();
    }
    
    public function ajax_submit_application() {
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        
        if (!wp_verify_nonce($nonce, 'wp-friendlink-apply-nonce')) {
            wp_send_json_error(array('message' => __('安全验证失败', 'wp-friendlink-apply')));
        }
        
        $site_name = isset($_POST['site_name']) ? sanitize_text_field($_POST['site_name']) : '';
        $site_url = isset($_POST['site_url']) ? esc_url_raw($_POST['site_url']) : '';
        $site_icon = isset($_POST['site_icon']) ? esc_url_raw($_POST['site_icon']) : '';
        $site_description = isset($_POST['site_description']) ? sanitize_textarea_field($_POST['site_description']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (empty($site_name) || empty($site_url)) {
            wp_send_json_error(array('message' => __('请填写网站标题和地址', 'wp-friendlink-apply')));
        }
        
        if (!filter_var($site_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('请输入有效的网站地址', 'wp-friendlink-apply')));
        }
        
        $user_id = get_current_user_id();
        
        if ($user_id) {
            $user = get_userdata($user_id);
            $email = $user->user_email;
        } else {
            if (empty($email)) {
                wp_send_json_error(array('message' => __('请填写邮箱地址', 'wp-friendlink-apply')));
            }
            
            if (!is_email($email)) {
                wp_send_json_error(array('message' => __('请输入有效的邮箱地址', 'wp-friendlink-apply')));
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'friendlink_applications';
        
        // 先检查表是否存在
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            // 表不存在，先创建表
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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
                has_backlink tinyint(1) DEFAULT NULL,
                response_time float DEFAULT NULL,
                reject_reason text DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY status (status)
            ) $charset_collate;";
            
            // 执行数据库表创建
            dbDelta($sql);
            
            // 再次检查表是否创建成功
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
                wp_send_json_error(array('message' => __('数据库表创建失败，请联系管理员', 'wp-friendlink-apply')));
            }
        }
        
        // 检查表结构是否完整（确保 reject_reason 字段存在）
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_name} LIKE 'reject_reason'"
        );
        
        if (empty($column_exists)) {
            // 添加 reject_reason 字段
            $wpdb->query(
                "ALTER TABLE {$table_name} ADD COLUMN reject_reason text DEFAULT NULL AFTER status"
            );
        }
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE site_url = %s AND status = 'pending'",
            $site_url
        ));
        
        if ($existing) {
            wp_send_json_error(array('message' => __('该网站已有待审核的申请', 'wp-friendlink-apply')));
        }
        
        // 确保user_id为0时使用null
        $user_id_value = empty($user_id) ? null : $user_id;
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id_value,
                'site_name' => $site_name,
                'site_url' => $site_url,
                'site_icon' => $site_icon,
                'site_description' => $site_description,
                'email' => $email,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            // 记录详细错误信息
            $error_message = $wpdb->last_error;
            // 确保返回有意义的错误信息
            $user_message = __('提交失败，请稍后重试', 'wp-friendlink-apply');
            wp_send_json_error(array('message' => $user_message, 'error' => $error_message));
        }
        
        $application_id = $wpdb->insert_id;
        
        do_action('wp_friendlink_application_submitted', $application_id, array(
            'user_id' => $user_id,
            'site_name' => $site_name,
            'site_url' => $site_url,
            'site_icon' => $site_icon,
            'site_description' => $site_description,
            'email' => $email
        ));
        
        wp_send_json_success(array('message' => __('申请提交成功！', 'wp-friendlink-apply')));
    }
    
    public function ajax_fetch_site_info() {
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        
        if (!wp_verify_nonce($nonce, 'wp-friendlink-apply-nonce')) {
            wp_send_json_error(array('message' => __('安全验证失败', 'wp-friendlink-apply')));
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('请输入有效的网站地址', 'wp-friendlink-apply')));
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'sslverify' => false,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('无法访问该网站', 'wp-friendlink-apply')));
        }
        
        $body = wp_remote_retrieve_body($response);
        $html = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
        
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        $title = '';
        $description = '';
        $icon = '';
        
        $title_nodes = $xpath->query('//title');
        if ($title_nodes->length > 0) {
            $title = trim($title_nodes->item(0)->nodeValue);
            
            // 处理标题格式：网站名 - 网站描述，只保留网站名部分
            // 使用正则表达式匹配各种分隔符
            $patterns = array(
                '/\s+-\s+/',           // 空格-空格
                '/\s+\|\s+/',          // 空格|空格
                '/\s+–\s+/',           // 空格短破折号空格
                '/\s+—\s+/',           // 空格长破折号空格
                '/\s+_\s+/',           // 空格_空格
                '/\s+•\s+/',           // 空格•空格
                '/\s+·\s+/',           // 空格·空格
                '/[-–—_·•]/u'          // 任何单个分隔符字符
            );
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $title)) {
                    $parts = preg_split($pattern, $title, 2);
                    $title = trim($parts[0]);
                    break;
                }
            }
        }
        
        $description_nodes = $xpath->query('//meta[@name="description"]/@content');
        if ($description_nodes->length > 0) {
            $description = trim($description_nodes->item(0)->nodeValue);
        }
        
        $og_title_nodes = $xpath->query('//meta[@property="og:title"]/@content');
        if ($og_title_nodes->length > 0 && empty($title)) {
            $title = trim($og_title_nodes->item(0)->nodeValue);
            
            // 处理标题格式：网站名 - 网站描述，只保留网站名部分
            if (strpos($title, ' - ') !== false) {
                $parts = explode(' - ', $title);
                $title = trim($parts[0]);
            } elseif (strpos($title, ' | ') !== false) {
                $parts = explode(' | ', $title);
                $title = trim($parts[0]);
            } elseif (strpos($title, ' – ') !== false) {
                $parts = explode(' – ', $title);
                $title = trim($parts[0]);
            } elseif (strpos($title, ' — ') !== false) {
                $parts = explode(' — ', $title);
                $title = trim($parts[0]);
            }
        }
        
        $og_description_nodes = $xpath->query('//meta[@property="og:description"]/@content');
        if ($og_description_nodes->length > 0 && empty($description)) {
            $description = trim($og_description_nodes->item(0)->nodeValue);
        }
        
        $icon_nodes = $xpath->query('//link[@rel="icon"]/@href');
        if ($icon_nodes->length > 0) {
            $icon = $icon_nodes->item(0)->nodeValue;
        }
        
        $shortcut_icon_nodes = $xpath->query('//link[@rel="shortcut icon"]/@href');
        if ($shortcut_icon_nodes->length > 0 && empty($icon)) {
            $icon = $shortcut_icon_nodes->item(0)->nodeValue;
        }
        
        $apple_touch_icon_nodes = $xpath->query('//link[@rel="apple-touch-icon"]/@href');
        if ($apple_touch_icon_nodes->length > 0 && empty($icon)) {
            $icon = $apple_touch_icon_nodes->item(0)->nodeValue;
        }
        
        if (!empty($icon) && !preg_match('/^https?:\/\//i', $icon)) {
            $parsed_url = parse_url($url);
            $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
            if (strpos($icon, '/') === 0) {
                $icon = $base_url . $icon;
            } else {
                $path = isset($parsed_url['path']) ? dirname($parsed_url['path']) : '';
                $icon = $base_url . ($path ? $path . '/' : '') . $icon;
            }
        }
        
        if (empty($icon)) {
            $parsed_url = parse_url($url);
            $icon = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/favicon.ico';
        }
        
        wp_send_json_success(array(
            'title' => $title,
            'description' => $description,
            'icon' => $icon
        ));
    }
    
    public function render_list_shortcode($atts) {
        $config = WP_Friendlink_Apply_Config::get_instance();
        
        $atts = shortcode_atts(array(
            'title' => $config->get('wp_friendlink_apply_list_title', '合作伙伴'),
            'show_backlink' => $config->get('wp_friendlink_apply_show_backlink', 0)
        ), $atts);
        
        $show_friends = $config->get('wp_friendlink_apply_show_friends', 0);
        
        if (!$show_friends) {
            return '';
        }
        
        $links = get_bookmarks(array(
            'orderby' => 'rating',
            'order' => 'DESC',
            'hide_invisible' => 1
        ));
        
        if (empty($links)) {
            return '';
        }
        
        $show_backlink = $atts['show_backlink'] || $config->get('wp_friendlink_apply_show_backlink', 0);
        $display_style = $config->get('wp_friendlink_apply_display_style', 'card');
        
        $links_data = array();
        
        foreach ($links as $link) {
            $link_data = array(
                'name' => $link->link_name,
                'url' => $link->link_url,
                'description' => $link->link_description,
                'image' => $link->link_image,
                'has_backlink' => null,
                'response_time' => null,
                'site_status' => null
            );
            
            $links_data[] = $link_data;
        }
        
        ob_start();
        ?>
        <div class="wp-friendlink-list-wrapper wp-friendlink-style-<?php echo esc_attr($display_style); ?>">
            <h3 class="friendlink-list-title"><?php echo esc_html($atts['title']); ?></h3>
            
            <?php if ($display_style === 'card') : ?>
                <div class="friendlink-card-list">
                    <?php foreach ($links_data as $link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="friendlink-card-item" data-url="<?php echo esc_attr($link['url']); ?>" title="<?php echo esc_attr($link['description']); ?>">
                            <div class="friendlink-card-icon-wrapper">
                                <?php if ($link['image']) : ?>
                                    <img src="<?php echo esc_url($link['image']); ?>" alt="<?php echo esc_attr($link['name']); ?>" class="friendlink-card-icon">
                                <?php else : ?>
                                    <span class="friendlink-card-icon-default">🔗</span>
                                <?php endif; ?>
                                <?php if ($show_backlink) : ?>
                                    <span class="friendlink-card-status-dot"></span>
                                <?php endif; ?>
                            </div>
                            <div class="friendlink-card-content">
                                <div class="friendlink-card-name"><?php echo esc_html($link['name']); ?></div>
                                <div class="friendlink-card-desc"><?php echo esc_html($link['description'] ?: ''); ?></div>
                            </div>
                            <?php if ($show_backlink) : ?>
                                <div class="friendlink-card-meta">
                                    <span class="friendlink-response-time">检测中...</span>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="friendlink-table-wrapper">
                    <table class="friendlink-table">
                        <thead>
                            <tr>
                                <th><?php _e('站点名称', 'wp-friendlink-apply'); ?></th>
                                <th class="hide-mobile"><?php _e('站点地址', 'wp-friendlink-apply'); ?></th>
                                <th class="hide-mobile"><?php _e('站点状态', 'wp-friendlink-apply'); ?></th>
                                <th><?php _e('响应时间', 'wp-friendlink-apply'); ?></th>
                                <th><?php _e('回链状态', 'wp-friendlink-apply'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links_data as $link) : ?>
                                <tr data-url="<?php echo esc_attr($link['url']); ?>" data-status="unknown" data-backlink="0">
                                    <td data-label="<?php _e('站点名称', 'wp-friendlink-apply'); ?>">
                                        <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="friendlink-table-link">
                                            <?php echo esc_html($link['name']); ?>
                                        </a>
                                    </td>
                                    <td data-label="<?php _e('站点地址', 'wp-friendlink-apply'); ?>" class="hide-mobile">
                                        <span class="friendlink-table-url"><?php echo esc_html($link['url']); ?></span>
                                    </td>
                                    <td data-label="<?php _e('站点状态', 'wp-friendlink-apply'); ?>" class="hide-mobile">
                                        <span class="friendlink-status-badge status-unknown"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                    </td>
                                    <td data-label="<?php _e('响应时间', 'wp-friendlink-apply'); ?>">
                                        <span class="friendlink-response-time"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                    </td>
                                    <td data-label="<?php _e('回链状态', 'wp-friendlink-apply'); ?>">
                                        <span class="friendlink-backlink-badge"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_get_friends() {
        $config = WP_Friendlink_Apply_Config::get_instance();
        
        $show_friends = $config->get('wp_friendlink_apply_show_friends', 0);
        
        if (!$show_friends) {
            wp_send_json_error(array('message' => __('功能未启用', 'wp-friendlink-apply')));
        }
        
        $show_backlink = $config->get('wp_friendlink_apply_show_backlink', 0);
        $display_style = $config->get('wp_friendlink_apply_display_style', 'card');
        
        $links = get_bookmarks(array(
            'orderby' => 'rating',
            'order' => 'DESC',
            'hide_invisible' => 1
        ));
        
        $friends = array();
        foreach ($links as $link) {
            $friend = array(
                'name' => $link->link_name,
                'url' => $link->link_url,
                'description' => $link->link_description,
                'icon' => $link->link_image,
                'image' => $link->link_image,
                'has_backlink' => null,
                'response_time' => null,
                'site_status' => null,
                'checking' => $show_backlink
            );
            
            $friends[] = $friend;
        }
        
        wp_send_json_success(array(
            'friends' => $friends,
            'display_style' => $display_style
        ));
    }
    
    public function ajax_check_single_backlink() {
        check_ajax_referer('wp-friendlink-apply-nonce', 'nonce');
        
        $site_url = isset($_POST['site_url']) ? esc_url_raw($_POST['site_url']) : '';
        
        if (empty($site_url)) {
            wp_send_json_error(array('message' => __('无效的站点地址', 'wp-friendlink-apply')));
        }
        
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        $result = $backlink_checker->check_backlink($site_url);
        
        wp_send_json_success(array(
            'has_backlink' => $result['has_backlink'],
            'response_time' => isset($result['response_time']) ? $result['response_time'] : 0,
            'site_status' => isset($result['site_status']) ? $result['site_status'] : 'unknown'
        ));
    }
    
    private function check_single_backlink($site_url) {
        $backlink_checker = WP_Friendlink_Apply_Backlink::get_instance();
        return $backlink_checker->check_backlink_simple($site_url);
    }
}
