<div class="wrap wp-friendlink-apply-help">
    <h1>
        <span class="settings-header-icon">📖</span>
        <?php _e('使用说明', 'wp-friendlink-apply'); ?>
    </h1>
    
    <div class="help-container">
        <div class="help-section">
            <div class="help-header">
                <span class="icon">🚀</span>
                <h3><?php _e('快速开始', 'wp-friendlink-apply'); ?></h3>
            </div>
            <div class="help-content">
                <h4><?php _e('1. 创建友链申请页面', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('在WordPress后台，创建一个新页面，选择页面模板为"友情链接申请页面"。发布后，用户即可通过该页面提交友链申请。', 'wp-friendlink-apply'); ?></p>
                
                <h4><?php _e('2. 使用简码嵌入', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('您也可以在任何页面或文章中使用以下简码来嵌入友链申请表单：', 'wp-friendlink-apply'); ?></p>
                <code class="shortcode-example">[friendlink_apply]</code>
                
                <h4><?php _e('3. 配置设置', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('在"插件设置"页面配置友链显示样式、是否显示回链状态等选项。', 'wp-friendlink-apply'); ?></p>
            </div>
        </div>
        
        <div class="help-section">
            <div class="help-header">
                <span class="icon">📋</span>
                <h3><?php _e('申请列表管理', 'wp-friendlink-apply'); ?></h3>
            </div>
            <div class="help-content">
                <h4><?php _e('状态说明', 'wp-friendlink-apply'); ?></h4>
                <ul>
                    <li><strong><?php _e('待审核', 'wp-friendlink-apply'); ?></strong> - <?php _e('新提交的申请，等待管理员审核', 'wp-friendlink-apply'); ?></li>
                    <li><strong><?php _e('已通过', 'wp-friendlink-apply'); ?></strong> - <?php _e('审核通过的申请，已添加到友链列表', 'wp-friendlink-apply'); ?></li>
                    <li><strong><?php _e('已拒绝', 'wp-friendlink-apply'); ?></strong> - <?php _e('审核被拒绝的申请', 'wp-friendlink-apply'); ?></li>
                </ul>
                
                <h4><?php _e('操作说明', 'wp-friendlink-apply'); ?></h4>
                <ul>
                    <li><strong>🔗 <?php _e('检测回链', 'wp-friendlink-apply'); ?></strong> - <?php _e('检测对方网站是否包含指向本站的友情链接', 'wp-friendlink-apply'); ?></li>
                    <li><strong>✅ <?php _e('通过', 'wp-friendlink-apply'); ?></strong> - <?php _e('批准申请，将站点添加到友链列表', 'wp-friendlink-apply'); ?></li>
                    <li><strong>❌ <?php _e('拒绝', 'wp-friendlink-apply'); ?></strong> - <?php _e('拒绝申请，可填写拒绝理由', 'wp-friendlink-apply'); ?></li>
                    <li><strong>🗑️ <?php _e('删除', 'wp-friendlink-apply'); ?></strong> - <?php _e('删除申请记录', 'wp-friendlink-apply'); ?></li>
                </ul>
                
                <h4><?php _e('筛选功能', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('您可以通过以下条件筛选申请：', 'wp-friendlink-apply'); ?></p>
                <ul>
                    <li><?php _e('申请状态（待审核/已通过/已拒绝）', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('站点状态（正常/异常）', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('回链状态（有回链/无回链）', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('关键词搜索（站点名称或地址）', 'wp-friendlink-apply'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="help-section">
            <div class="help-header">
                <span class="icon">🎨</span>
                <h3><?php _e('前台显示设置', 'wp-friendlink-apply'); ?></h3>
            </div>
            <div class="help-content">
                <h4><?php _e('显示样式', 'wp-friendlink-apply'); ?></h4>
                <div class="style-comparison">
                    <div class="style-item">
                        <h5><?php _e('图片卡片式', 'wp-friendlink-apply'); ?></h5>
                        <ul>
                            <li><?php _e('左侧圆形图标显示站点Logo', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('右侧显示站点名称和描述', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('右下角显示响应时长', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('右上角状态点指示回链状态（绿色=正常，红色=异常）', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('响应式布局，PC端自适应显示，移动端每行2个', 'wp-friendlink-apply'); ?></li>
                        </ul>
                    </div>
                    <div class="style-item">
                        <h5><?php _e('表格式', 'wp-friendlink-apply'); ?></h5>
                        <ul>
                            <li><?php _e('传统表格布局，信息一目了然', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('显示站点名称、链接地址、网站状态、响应时长、回链状态', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('顶部显示状态汇总信息', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('支持按状态筛选', 'wp-friendlink-apply'); ?></li>
                            <li><?php _e('移动端自适应，隐藏部分列', 'wp-friendlink-apply'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <h4><?php _e('其他选项', 'wp-friendlink-apply'); ?></h4>
                <ul>
                    <li><strong><?php _e('显示友链站点', 'wp-friendlink-apply'); ?></strong> - <?php _e('控制是否在前端显示已通过的友链列表', 'wp-friendlink-apply'); ?></li>
                    <li><strong><?php _e('显示回链状态', 'wp-friendlink-apply'); ?></strong> - <?php _e('控制是否显示每个站点的回链检测状态', 'wp-friendlink-apply'); ?></li>
                    <li><strong><?php _e('友链列表标题', 'wp-friendlink-apply'); ?></strong> - <?php _e('自定义友链列表的标题名称', 'wp-friendlink-apply'); ?></li>
                    <li><strong><?php _e('自动通过申请', 'wp-friendlink-apply'); ?></strong> - <?php _e('开启后所有申请自动通过（不推荐）', 'wp-friendlink-apply'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="help-section">
            <div class="help-header">
                <span class="icon">🔗</span>
                <h3><?php _e('回链检测机制', 'wp-friendlink-apply'); ?></h3>
            </div>
            <div class="help-content">
                <h4><?php _e('检测方式', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('本插件采用多层次、全方位的回链检测策略，支持四种检测方式：', 'wp-friendlink-apply'); ?></p>
                
                <table class="help-table detection-table">
                    <thead>
                        <tr>
                            <th><?php _e('序号', 'wp-friendlink-apply'); ?></th>
                            <th><?php _e('检测方式', 'wp-friendlink-apply'); ?></th>
                            <th><?php _e('说明', 'wp-friendlink-apply'); ?></th>
                            <th><?php _e('适用场景', 'wp-friendlink-apply'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><strong><?php _e('静态 HTML <a> 标签检测', 'wp-friendlink-apply'); ?></strong></td>
                            <td><?php _e('解析页面 HTML，检查所有 <a> 标签的 href 属性', 'wp-friendlink-apply'); ?></td>
                            <td><?php _e('通用网站', 'wp-friendlink-apply'); ?></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong><?php _e('Base64 编码 URL 解码检测', 'wp-friendlink-apply'); ?></strong></td>
                            <td><?php _e('解码 Base64 编码的链接（如 golink= 参数）', 'wp-friendlink-apply'); ?></td>
                            <td><?php _e('使用加密跳转的网站', 'wp-friendlink-apply'); ?></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><strong><?php _e('data-local-links 属性检测', 'wp-friendlink-apply'); ?></strong></td>
                            <td><?php _e('解析 AeroCore 主题的本地友链数据（Base64 编码的 JSON）', 'wp-friendlink-apply'); ?></td>
                            <td><?php _e('AeroCore 主题', 'wp-friendlink-apply'); ?></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><strong><?php _e('动态链接 API 检测', 'wp-friendlink-apply'); ?></strong></td>
                            <td><?php _e('通过 AJAX API (getLinkListLinks) 获取友链列表', 'wp-friendlink-apply'); ?></td>
                            <td><?php _e('AeroCore 主题', 'wp-friendlink-apply'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h4><?php _e('检测流程', 'wp-friendlink-apply'); ?></h4>
                <div class="detection-flow">
                    <div class="flow-step">
                        <div class="flow-number">1</div>
                        <div class="flow-content">
                            <strong><?php _e('静态 HTML 检测', 'wp-friendlink-apply'); ?></strong>
                            <ul>
                                <li><?php _e('解析 <a> 标签', 'wp-friendlink-apply'); ?></li>
                                <li><?php _e('解码 Base64 编码链接', 'wp-friendlink-apply'); ?></li>
                                <li><?php _e('解析 data-local-links 属性', 'wp-friendlink-apply'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="flow-arrow">↓</div>
                    <div class="flow-step">
                        <div class="flow-number">2</div>
                        <div class="flow-content">
                            <strong><?php _e('动态 API 检测（如果静态检测未找到）', 'wp-friendlink-apply'); ?></strong>
                            <ul>
                                <li><?php _e('调用 AeroCore API 获取友链列表', 'wp-friendlink-apply'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="flow-arrow">↓</div>
                    <div class="flow-step">
                        <div class="flow-number">3</div>
                        <div class="flow-content">
                            <strong><?php _e('返回检测结果', 'wp-friendlink-apply'); ?></strong>
                            <ul>
                                <li><?php _e('has_backlink: 是否存在回链', 'wp-friendlink-apply'); ?></li>
                                <li><?php _e('response_time: 响应时间（毫秒）', 'wp-friendlink-apply'); ?></li>
                                <li><?php _e('site_status: 网站状态', 'wp-friendlink-apply'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <h4><?php _e('注意事项', 'wp-friendlink-apply'); ?></h4>
                <ul>
                    <li><?php _e('部分网站可能禁止爬虫访问，导致检测失败', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('友链位置需要能被公开访问才能检测到', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('建议定期检测已通过友链的回链状态', 'wp-friendlink-apply'); ?></li>
                    <li><?php _e('AeroCore 主题的友链可能存储在多个位置，插件会逐一检测', 'wp-friendlink-apply'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="help-section">
            <div class="help-header">
                <span class="icon">❓</span>
                <h3><?php _e('常见问题', 'wp-friendlink-apply'); ?></h3>
            </div>
            <div class="help-content">
                <h4><?php _e('Q: 为什么检测不到回链？', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('A: 可能的原因：1) 对方网站禁止爬虫访问；2) 友链通过JavaScript动态加载；3) 友链页面需要登录才能访问；4) 网络连接问题。', 'wp-friendlink-apply'); ?></p>
                
                <h4><?php _e('Q: 如何修改已通过的友链信息？', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('A: 通过审核的友链会添加到WordPress的"链接"管理中，您可以在后台"链接"菜单中编辑修改。', 'wp-friendlink-apply'); ?></p>
                
                <h4><?php _e('Q: 前端显示样式可以自定义吗？', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('A: 插件提供了两种预设样式（卡片式和表格式），如需进一步自定义，可以通过CSS覆盖插件样式。', 'wp-friendlink-apply'); ?></p>
                
                <h4><?php _e('Q: 如何批量检测回链状态？', 'wp-friendlink-apply'); ?></h4>
                <p><?php _e('A: 目前需要逐个点击"检测回链"按钮进行检测，后续版本可能会添加批量检测功能。', 'wp-friendlink-apply'); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.help-container {
    max-width: 1000px;
}

.help-section {
    margin-bottom: 20px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.help-header {
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.help-header .icon {
    font-size: 24px;
}

.help-header h3 {
    margin: 0;
    font-size: 18px;
}

.help-content {
    padding: 20px;
}

.help-content h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 15px;
    border-left: 3px solid #667eea;
    padding-left: 10px;
}

.help-content h5 {
    margin: 10px 0 5px 0;
    color: #555;
    font-size: 14px;
}

.help-content p {
    margin: 0 0 15px 0;
    color: #666;
    line-height: 1.6;
}

.help-content ul,
.help-content ol {
    margin: 0 0 15px 20px;
    color: #666;
    line-height: 1.8;
}

.help-content li {
    margin-bottom: 5px;
}

.shortcode-example {
    display: inline-block;
    background: #f0f0f1;
    padding: 8px 15px;
    border-radius: 5px;
    font-family: monospace;
    font-size: 14px;
    color: #667eea;
    border: 1px dashed #667eea;
}

.style-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.style-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.style-item h5 {
    margin: 0 0 10px 0;
    color: #667eea;
}

.help-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.help-table th,
.help-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.help-table th {
    width: 180px;
    background: #f8f9fa;
}

.help-table code {
    background: #f0f0f1;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 13px;
}

.detection-table th:first-child,
.detection-table td:first-child {
    width: 50px;
    text-align: center;
}

.detection-table th:nth-child(2),
.detection-table td:nth-child(2) {
    width: 200px;
}

.detection-flow {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.flow-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    background: #fff;
    padding: 15px 20px;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.flow-number {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.flow-content {
    flex: 1;
}

.flow-content strong {
    display: block;
    margin-bottom: 8px;
    color: #333;
}

.flow-content ul {
    margin: 0;
    padding-left: 20px;
    color: #666;
}

.flow-content ul li {
    margin-bottom: 3px;
}

.flow-arrow {
    font-size: 24px;
    color: #667eea;
    margin: 10px 0;
}
</style>
