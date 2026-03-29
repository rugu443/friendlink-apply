<div class="wrap wp-friendlink-apply-settings">
    <h1>
        <span class="settings-header-icon">⚙️</span>
        <?php _e('插件设置', 'wp-friendlink-apply'); ?>
    </h1>
    
    <?php
    $config = WP_Friendlink_Apply_Config::get_instance();
    
    if (isset($_POST['wp_friendlink_save_settings']) && wp_verify_nonce($_POST['wp_friendlink_settings_nonce'], 'wp_friendlink_settings')) {
        $config->set('wp_friendlink_apply_show_friends', isset($_POST['wp_friendlink_apply_show_friends']) ? 1 : 0);
        $config->set('wp_friendlink_apply_show_backlink', isset($_POST['wp_friendlink_apply_show_backlink']) ? 1 : 0);
        $config->set('wp_friendlink_apply_display_style', sanitize_text_field($_POST['wp_friendlink_apply_display_style']));
        $config->set('wp_friendlink_apply_list_title', sanitize_text_field($_POST['wp_friendlink_apply_list_title']));
        $config->set('wp_friendlink_apply_apply_title', sanitize_text_field($_POST['wp_friendlink_apply_apply_title']));
        $config->set('wp_friendlink_apply_apply_description', sanitize_text_field($_POST['wp_friendlink_apply_apply_description']));
        $config->set('wp_friendlink_apply_enable_auto_approve', isset($_POST['wp_friendlink_apply_enable_auto_approve']) ? 1 : 0);
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('设置已保存', 'wp-friendlink-apply') . '</p></div>';
    }
    
    $show_friends = $config->get('wp_friendlink_apply_show_friends', 0);
    $show_backlink = $config->get('wp_friendlink_apply_show_backlink', 0);
    $display_style = $config->get('wp_friendlink_apply_display_style', 'card');
    $list_title = $config->get('wp_friendlink_apply_list_title', '合作伙伴');
    $apply_title = $config->get('wp_friendlink_apply_apply_title', '友链申请');
    $apply_description = $config->get('wp_friendlink_apply_apply_description', '请填写以下信息申请友链');
    $enable_auto_approve = $config->get('wp_friendlink_apply_enable_auto_approve', 0);
    ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wp_friendlink_settings', 'wp_friendlink_settings_nonce'); ?>
        
        <div class="email-section">
            <div class="email-section-header">
                <span class="icon">🔗</span>
                <h3><?php _e('友链显示设置', 'wp-friendlink-apply'); ?></h3>
                <span class="badge"><?php _e('前台显示', 'wp-friendlink-apply'); ?></span>
            </div>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_show_friends"><?php _e('显示友链站点', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" id="wp_friendlink_apply_show_friends" 
                                   name="wp_friendlink_apply_show_friends" 
                                   value="1" <?php checked($show_friends, 1); ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('在前端申请页面显示已通过审核的友链站点列表', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_show_backlink"><?php _e('显示回链状态', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" id="wp_friendlink_apply_show_backlink" 
                                   name="wp_friendlink_apply_show_backlink" 
                                   value="1" <?php checked($show_backlink, 1); ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('显示每个友链是否包含指向本站的回链状态', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_display_style"><?php _e('友链显示样式', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <div class="style-options">
                            <label class="style-option <?php echo $display_style === 'card' ? 'selected' : ''; ?>">
                                <input type="radio" name="wp_friendlink_apply_display_style" value="card" <?php checked($display_style, 'card'); ?>>
                                <div class="style-preview card-preview">
                                    <div class="preview-card">
                                        <div class="preview-icon"></div>
                                        <div class="preview-info">
                                            <div class="preview-name"></div>
                                            <div class="preview-desc"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="style-label"><?php _e('图片卡片式', 'wp-friendlink-apply'); ?></span>
                            </label>
                            <label class="style-option <?php echo $display_style === 'table' ? 'selected' : ''; ?>">
                                <input type="radio" name="wp_friendlink_apply_display_style" value="table" <?php checked($display_style, 'table'); ?>>
                                <div class="style-preview table-preview">
                                    <div class="preview-table">
                                        <div class="preview-row preview-header"></div>
                                        <div class="preview-row"></div>
                                        <div class="preview-row"></div>
                                    </div>
                                </div>
                                <span class="style-label"><?php _e('表格式', 'wp-friendlink-apply'); ?></span>
                            </label>
                        </div>
                        <p class="description">
                            <?php _e('图片卡片式：左侧圆形图标，右侧站点信息，带响应时长和回链状态指示；表格式：传统表格布局，显示完整站点信息', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_list_title"><?php _e('友链列表标题', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="wp_friendlink_apply_list_title" 
                               name="wp_friendlink_apply_list_title" 
                               value="<?php echo esc_attr($list_title); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('设置前端友链列表的标题名称，默认为"合作伙伴"', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_apply_title"><?php _e('友链申请标题', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="wp_friendlink_apply_apply_title" 
                               name="wp_friendlink_apply_apply_title" 
                               value="<?php echo esc_attr($apply_title); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('设置前端友链申请表单的标题名称，默认为"友链申请"', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_apply_description"><?php _e('友链申请描述', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="wp_friendlink_apply_apply_description" 
                               name="wp_friendlink_apply_apply_description" 
                               value="<?php echo esc_attr($apply_description); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('设置前端友链申请表单的描述文字，默认为"请填写以下信息申请友链"', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="email-section">
            <div class="email-section-header">
                <span class="icon">⚙️</span>
                <h3><?php _e('其他设置', 'wp-friendlink-apply'); ?></h3>
            </div>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wp_friendlink_apply_enable_auto_approve"><?php _e('自动通过申请', 'wp-friendlink-apply'); ?></label>
                    </th>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" id="wp_friendlink_apply_enable_auto_approve" 
                                   name="wp_friendlink_apply_enable_auto_approve" 
                                   value="1" <?php checked($enable_auto_approve, 1); ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('启用后，所有提交的友情链接申请将自动通过审核并直接添加到链接列表中（不推荐）', 'wp-friendlink-apply'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="settings-submit-area">
            <button type="submit" name="wp_friendlink_save_settings" class="submit-button">
                <?php _e('保存设置', 'wp-friendlink-apply'); ?>
            </button>
        </div>
    </form>
</div>

<style>
.style-options {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}

.style-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
}

.style-option:hover {
    border-color: #667eea;
}

.style-option.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.style-option input {
    display: none;
}

.style-preview {
    width: 120px;
    height: 80px;
    margin-bottom: 10px;
}

.card-preview {
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.preview-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.preview-name {
    width: 50px;
    height: 6px;
    background: #333;
    border-radius: 3px;
}

.preview-desc {
    width: 40px;
    height: 4px;
    background: #999;
    border-radius: 2px;
}

.table-preview {
    display: flex;
    justify-content: center;
}

.preview-table {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.preview-row {
    height: 10px;
    background: #f0f0f0;
    border-radius: 2px;
}

.preview-row.preview-header {
    background: #667eea;
}

.style-label {
    font-size: 13px;
    font-weight: 500;
    color: #333;
}

.switch-label {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.switch-label input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 26px;
}

.switch-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .switch-slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

input:checked + .switch-slider:before {
    transform: translateX(24px);
}
</style>
