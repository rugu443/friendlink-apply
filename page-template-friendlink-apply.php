<?php

namespace AeroCore;

$config = \WP_Friendlink_Apply_Config::get_instance();

wp_enqueue_style('wp-friendlink-apply-frontend', WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/css/frontend.css', array(), WP_FRIENDLINK_APPLY_VERSION);
wp_enqueue_script('jquery');
wp_enqueue_script('wp-friendlink-apply-frontend', WP_FRIENDLINK_APPLY_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WP_FRIENDLINK_APPLY_VERSION, true);
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

Template::renderHeader([
    'title' => get_the_title() . ' - ' . get_bloginfo('name')
]);

?>

<div class="page-container">
    <?php
    global $wp_query;
    if (!empty($wp_query->posts)) {
        $wp_query->the_post();
    }
    ?>
    <div class="single-article" style="background: var(--bg-card); border-radius: var(--border-radius); padding: 24px 32px; margin-bottom: 14px;">
        <div class="single-article__header">
            <h1 class="single-article__title"><?php the_title(); ?></h1>
            <div class="single-article__meta">
                <span><?php echo get_the_date(); ?></span>
                <span><?php echo get_the_author(); ?></span>
                <?php if (current_user_can('edit_post', get_the_ID())): ?>
                <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="single-article__edit" target="_blank" rel="noopener"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>编辑</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="single-article__content">
            <?php the_content(); ?>
            
            <?php 
            $config = \WP_Friendlink_Apply_Config::get_instance();
            $show_friends = $config->get('wp_friendlink_apply_show_friends', 0);
            $show_backlink = $config->get('wp_friendlink_apply_show_backlink', 0);
            $display_style = $config->get('wp_friendlink_apply_display_style', 'card');
            $list_title = $config->get('wp_friendlink_apply_list_title', '我的邻居');
            $apply_title = $config->get('wp_friendlink_apply_apply_title', '友链申请');
            $apply_description = $config->get('wp_friendlink_apply_apply_description', '请填写以下信息申请友链');
            
            if ($show_friends) :
                $friendlinks = get_bookmarks(array(
                    'orderby' => 'rating',
                    'order' => 'DESC',
                    'hide_invisible' => 1
                ));
                
                if (!empty($friendlinks)) :
                    $links_data = array();
                    
                    foreach ($friendlinks as $flink) {
                        $link_data = array(
                            'name' => $flink->link_name,
                            'url' => $flink->link_url,
                            'description' => $flink->link_description,
                            'image' => $flink->link_image,
                            'has_backlink' => null,
                            'response_time' => null,
                            'site_status' => null
                        );
                        
                        $links_data[] = $link_data;
                    }
            ?>
                <div class="wp-friendlink-list-wrapper wp-friendlink-style-<?php echo esc_attr($display_style); ?>">
                    <div class="friendlink-list-title">
                        <?php echo esc_html($list_title); ?>
                    </div>
                    
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
                        <?php
                        $total_count = count($links_data);
                        $normal_count = 0;
                        $abnormal_count = 0;
                        $no_backlink_count = 0;
                        ?>
                        <div class="friendlink-stats-bar">
                            <div class="friendlink-stats-info">
                                <span class="stats-item"><strong><?php _e('检测总数：', 'wp-friendlink-apply'); ?></strong><?php echo esc_html($total_count); ?></span>
                                <span class="stats-item stats-normal"><strong><?php _e('正常链接：', 'wp-friendlink-apply'); ?></strong><span id="normal-count"><?php echo esc_html($normal_count); ?></span></span>
                                <span class="stats-item stats-abnormal"><strong><?php _e('异常链接：', 'wp-friendlink-apply'); ?></strong><span id="abnormal-count"><?php echo esc_html($abnormal_count); ?></span></span>
                                <span class="stats-item stats-no-backlink"><strong><?php _e('回链异常：', 'wp-friendlink-apply'); ?></strong><span id="no-backlink-count"><?php echo esc_html($no_backlink_count); ?></span></span>
                            </div>
                            <div class="friendlink-filter-buttons">
                                <button type="button" class="friendlink-filter-btn active" data-filter="all"><?php _e('全部', 'wp-friendlink-apply'); ?></button>
                                <button type="button" class="friendlink-filter-btn" data-filter="normal"><?php _e('正常', 'wp-friendlink-apply'); ?></button>
                                <button type="button" class="friendlink-filter-btn" data-filter="abnormal"><?php _e('异常', 'wp-friendlink-apply'); ?></button>
                                <button type="button" class="friendlink-filter-btn" data-filter="no-backlink"><?php _e('异常', 'wp-friendlink-apply'); ?></button>
                            </div>
                        </div>
                        <div class="friendlink-table-wrapper">
                            <table class="friendlink-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('网站名称', 'wp-friendlink-apply'); ?></th>
                                        <th class="hide-mobile"><?php _e('链接地址', 'wp-friendlink-apply'); ?></th>
                                        <th class="show-mobile"><?php _e('网站状态', 'wp-friendlink-apply'); ?></th>
                                        <th class="hide-mobile"><?php _e('网站状态', 'wp-friendlink-apply'); ?></th>
                                        <th><?php _e('响应时长', 'wp-friendlink-apply'); ?></th>
                                        <th><?php _e('回链状态', 'wp-friendlink-apply'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($links_data as $link) : ?>
                                        <tr data-url="<?php echo esc_attr($link['url']); ?>" data-status="unknown" data-backlink="0">
                                            <td data-label="<?php _e('网站名称', 'wp-friendlink-apply'); ?>">
                                                <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="friendlink-table-link">
                                                    <?php echo esc_html($link['name']); ?>
                                                </a>
                                            </td>
                                            <td data-label="<?php _e('链接地址', 'wp-friendlink-apply'); ?>" class="hide-mobile">
                                                <span class="friendlink-table-url"><?php echo esc_html($link['url']); ?></span>
                                            </td>
                                            <td data-label="<?php _e('网站状态', 'wp-friendlink-apply'); ?>" class="show-mobile">
                                                <span class="friendlink-status-badge status-unknown"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                            </td>
                                            <td data-label="<?php _e('网站状态', 'wp-friendlink-apply'); ?>" class="hide-mobile">
                                                <span class="friendlink-status-badge status-unknown"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                            </td>
                                            <td data-label="<?php _e('响应时长', 'wp-friendlink-apply'); ?>">
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
                endif;
            endif;
            ?>
            
            <div class="wp-friendlink-apply-form-wrapper">
                <div class="wp-friendlink-apply-header">
                    <h3 class="form-title"><?php echo esc_html($apply_title); ?></h3>
                    <p class="form-description"><?php echo esc_html($apply_description); ?></p>
                </div>
                
                <form id="wp-friendlink-apply-form" class="wp-friendlink-apply-form">
                    <?php wp_nonce_field('wp-friendlink-apply-nonce', 'wp_friendlink_apply_nonce'); ?>
                    
                    <div class="form-group">
                        <label for="site_name">
                            <?php _e('网站标题', 'wp-friendlink-apply'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="site_name" name="site_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_url">
                            <?php _e('网站地址', 'wp-friendlink-apply'); ?>
                            <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="url" id="site_url" name="site_url" required placeholder="https://example.com">
                            <button type="button" id="fetch-site-info" class="button button-secondary">
                                <?php _e('自动获取', 'wp-friendlink-apply'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_icon"><?php _e('网站图标', 'wp-friendlink-apply'); ?></label>
                        <div class="icon-wrapper">
                            <input type="url" id="site_icon" name="site_icon" placeholder="https://example.com/favicon.ico">
                            <div class="icon-preview empty" id="icon-preview">
                                <img id="icon-preview-img" src="" alt="" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_description"><?php _e('网站描述', 'wp-friendlink-apply'); ?></label>
                        <textarea id="site_description" name="site_description" rows="4" placeholder="<?php _e('请简要描述您的网站', 'wp-friendlink-apply'); ?>"></textarea>
                    </div>
                    
                    <?php if (!is_user_logged_in()) : ?>
                        <div class="form-group">
                            <label for="email">
                                <?php _e('邮箱地址（用于接收审核结果通知）', 'wp-friendlink-apply'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email" required placeholder="admin@qq.com">
                        </div>
                    <?php else : ?>
                        <div class="form-group logged-in-info">
                            <p>
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php printf(__('已登录用户：%s，将使用您的账户邮箱接收通知', 'wp-friendlink-apply'), wp_get_current_user()->display_name); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group submit-group">
                        <button type="submit" id="submit-application" class="button button-primary button-large">
                            <?php _e('提交申请', 'wp-friendlink-apply'); ?>
                        </button>
                    </div>
                    
                    <div class="form-message" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
Template::renderFooter([
    'vite_entries' => []
]);
