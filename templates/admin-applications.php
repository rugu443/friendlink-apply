<div class="wrap wp-friendlink-apply-applications">
    <h1 class="page-title">
        <span class="icon">📋</span>
        <?php _e('申请列表', 'wp-friendlink-apply'); ?>
    </h1>
    
    <?php
    $total_count = count($applications);
    $pending_count = 0;
    $approved_count = 0;
    $rejected_count = 0;
    
    foreach ($applications as $app) {
        if ($app->status === 'pending') {
            $pending_count++;
        } elseif ($app->status === 'approved') {
            $approved_count++;
        } elseif ($app->status === 'rejected') {
            $rejected_count++;
        }
    }
    ?>
    
    <div class="stats-overview">
        <div class="stat-card pending clickable active" data-filter="pending">
            <span class="stat-number"><?php echo $pending_count; ?></span>
            <span class="stat-label"><?php _e('待审核', 'wp-friendlink-apply'); ?></span>
        </div>
        <div class="stat-card approved clickable" data-filter="approved">
            <span class="stat-number"><?php echo $approved_count; ?></span>
            <span class="stat-label"><?php _e('已通过', 'wp-friendlink-apply'); ?></span>
        </div>
        <div class="stat-card rejected clickable" data-filter="rejected">
            <span class="stat-number"><?php echo $rejected_count; ?></span>
            <span class="stat-label"><?php _e('已拒绝', 'wp-friendlink-apply'); ?></span>
        </div>
        <div class="stat-card total clickable" data-filter="all">
            <span class="stat-number"><?php echo $total_count; ?></span>
            <span class="stat-label"><?php _e('总计', 'wp-friendlink-apply'); ?></span>
        </div>
    </div>
    
    <div class="bulk-actions-bar">
        <label class="select-all-wrapper">
            <input type="checkbox" id="select-all">
            <span><?php _e('全选', 'wp-friendlink-apply'); ?></span>
        </label>
        <button class="button bulk-btn" id="bulk-approve" disabled><?php _e('批量通过', 'wp-friendlink-apply'); ?></button>
        <button class="button bulk-btn" id="bulk-reject" disabled><?php _e('批量拒绝', 'wp-friendlink-apply'); ?></button>
        <button class="button bulk-btn" id="bulk-check" disabled><?php _e('批量检测回链', 'wp-friendlink-apply'); ?></button>
        <button class="button bulk-btn bulk-btn-delete" id="bulk-delete" disabled><?php _e('批量删除', 'wp-friendlink-apply'); ?></button>
    </div>
    
    <table class="wp-list-table widefat fixed striped applications-table" id="applications-table">
        <thead>
            <tr>
                <th class="column-check"></th>
                <th class="column-name"><?php _e('站点名称', 'wp-friendlink-apply'); ?></th>
                <th class="column-url"><?php _e('站点地址', 'wp-friendlink-apply'); ?></th>
                <th class="column-status"><?php _e('审核状态', 'wp-friendlink-apply'); ?></th>
                <th class="column-response"><?php _e('状态/响应时间', 'wp-friendlink-apply'); ?></th>
                <th class="column-time"><?php _e('申请时间', 'wp-friendlink-apply'); ?></th>
                <th class="column-actions"><?php _e('操作', 'wp-friendlink-apply'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)) : ?>
                <tr class="no-items">
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="empty-icon">📭</span>
                            <p><?php _e('暂无申请记录', 'wp-friendlink-apply'); ?></p>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($applications as $app) : ?>
                    <tr data-status="<?php echo esc_attr($app->status); ?>" data-id="<?php echo $app->id; ?>">
                        <td class="column-check">
                            <input type="checkbox" class="row-checkbox" value="<?php echo $app->id; ?>">
                        </td>
                        <td class="column-name" data-label="<?php _e('站点名称', 'wp-friendlink-apply'); ?>">
                            <strong class="site-name"><?php echo esc_html($app->site_name); ?></strong>
                        </td>
                        <td class="column-url" data-label="<?php _e('站点地址', 'wp-friendlink-apply'); ?>">
                            <a href="<?php echo esc_url($app->site_url); ?>" target="_blank" class="site-url">
                                <?php echo esc_html($app->site_url); ?>
                            </a>
                        </td>
                        <td class="column-status" data-label="<?php _e('审核状态', 'wp-friendlink-apply'); ?>">
                            <span class="status-tag status-<?php echo esc_attr($app->status); ?>">
                                <?php 
                                $app_status_labels = array(
                                    'pending' => __('待审核', 'wp-friendlink-apply'),
                                    'approved' => __('已通过', 'wp-friendlink-apply'),
                                    'rejected' => __('已拒绝', 'wp-friendlink-apply')
                                );
                                echo isset($app_status_labels[$app->status]) ? $app_status_labels[$app->status] : $app->status;
                                ?>
                            </span>
                        </td>
                        <td class="column-response" data-label="<?php _e('状态/响应时间', 'wp-friendlink-apply'); ?>">
                            <div class="status-response-cell" data-url="<?php echo esc_url($app->site_url); ?>">
                                <span class="site-status-badge checking"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                <span class="response-time-small">--</span>
                            </div>
                        </td>
                        <td class="column-time" data-label="<?php _e('申请时间', 'wp-friendlink-apply'); ?>">
                            <span class="time-text"><?php echo mysql2date('Y-m-d H:i', $app->created_at); ?></span>
                        </td>
                        <td class="column-actions" data-label="<?php _e('操作', 'wp-friendlink-apply'); ?>">
                            <div class="actions">
                                <button class="button button-small check-backlink-btn" data-id="<?php echo $app->id; ?>" data-url="<?php echo esc_url($app->site_url); ?>" title="<?php _e('检测回链', 'wp-friendlink-apply'); ?>">
                                    🔗
                                </button>
                                <?php if ($app->status === 'pending') : ?>
                                    <button class="button button-small button-primary approve-btn" data-id="<?php echo $app->id; ?>" title="<?php _e('通过', 'wp-friendlink-apply'); ?>">
                                        ✅
                                    </button>
                                    <button class="button button-small reject-btn" data-id="<?php echo $app->id; ?>" title="<?php _e('拒绝', 'wp-friendlink-apply'); ?>">
                                        ❌
                                    </button>
                                <?php elseif ($app->status === 'approved') : ?>
                                    <?php
                                    $link_id = 0;
                                    $link_visible = 'Y';
                                    $existing_link = get_bookmarks(array(
                                        'search' => $app->site_url,
                                        'limit' => 1
                                    ));
                                    if (!empty($existing_link)) {
                                        $link_id = $existing_link[0]->link_id;
                                        $link_visible = $existing_link[0]->link_visible;
                                    }
                                    ?>
                                    <button class="button button-small edit-link-btn" data-id="<?php echo $app->id; ?>" data-link-id="<?php echo $link_id; ?>" data-name="<?php echo esc_attr($app->site_name); ?>" data-url="<?php echo esc_attr($app->site_url); ?>" data-description="<?php echo esc_attr($app->site_description); ?>" data-icon="<?php echo esc_attr(isset($app->site_icon) ? $app->site_icon : ''); ?>" title="<?php _e('编辑', 'wp-friendlink-apply'); ?>">
                                        ✏️
                                    </button>
                                    <?php if ($link_visible === 'Y') : ?>
                                        <button class="button button-small disable-link-btn" data-link-id="<?php echo $link_id; ?>" title="<?php _e('禁用', 'wp-friendlink-apply'); ?>">
                                            🚫
                                        </button>
                                    <?php else : ?>
                                        <button class="button button-small button-primary enable-link-btn" data-link-id="<?php echo $link_id; ?>" title="<?php _e('启用', 'wp-friendlink-apply'); ?>">
                                            ✅
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($app->status === 'rejected') : ?>
                                    <button class="button button-small button-primary approve-btn" data-id="<?php echo $app->id; ?>" title="<?php _e('重新通过', 'wp-friendlink-apply'); ?>">
                                        ✅
                                    </button>
                                <?php endif; ?>
                                <button class="button button-small button-delete delete-btn" data-id="<?php echo $app->id; ?>" title="<?php _e('删除', 'wp-friendlink-apply'); ?>">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    function updateBulkButtons() {
        var checkedCount = $('.row-checkbox:checked').length;
        $('#bulk-approve, #bulk-reject, #bulk-check, #bulk-delete').prop('disabled', checkedCount === 0);
    }
    
    function applyFilter(filter) {
        $('.stat-card.clickable').removeClass('active');
        $('.stat-card.clickable[data-filter="' + filter + '"]').addClass('active');
        
        var $rows = $('#applications-table tbody tr').not('.no-items');
        
        $rows.show();
        
        if (filter !== 'all') {
            $rows.each(function() {
                if ($(this).data('status') !== filter) {
                    $(this).hide();
                }
            });
        }
        
        $('#select-all').prop('checked', false);
        $('.row-checkbox').prop('checked', false);
        updateBulkButtons();
    }
    
    applyFilter('pending');
    
    $('.stat-card.clickable').on('click', function() {
        var filter = $(this).data('filter');
        applyFilter(filter);
    });
    
    $('#select-all').on('change', function() {
        var checked = $(this).prop('checked');
        $('.row-checkbox:visible').prop('checked', checked);
        updateBulkButtons();
    });
    
    $(document).on('change', '.row-checkbox', function() {
        var visibleCheckboxes = $('.row-checkbox:visible');
        var checkedVisible = $('.row-checkbox:visible:checked');
        $('#select-all').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible.length);
        updateBulkButtons();
    });
    
    function getSelectedIds() {
        var ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
    
    $('#bulk-approve').on('click', function() {
        if (!confirm('<?php _e('确定批量通过选中的申请？', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkAction('bulk_approve', ids);
    });
    
    $('#bulk-reject').on('click', function() {
        if (!confirm('<?php _e('确定批量拒绝选中的申请？', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkAction('bulk_reject', ids);
    });
    
    $('#bulk-delete').on('click', function() {
        if (!confirm('<?php _e('确定批量删除选中的申请？', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkAction('bulk_delete', ids);
    });
    
    $('#bulk-check').on('click', function() {
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('检测中...', 'wp-friendlink-apply'); ?>');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_bulk_check',
                nonce: wpFriendlinkApply.nonce,
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    var results = response.data.results;
                    var html = '<div class="bulk-check-results">';
                    html += '<h3><?php _e('回链检测结果', 'wp-friendlink-apply'); ?></h3>';
                    html += '<table class="bulk-check-table">';
                    html += '<thead><tr>';
                    html += '<th><?php _e('站点名称', 'wp-friendlink-apply'); ?></th>';
                    html += '<th><?php _e('站点地址', 'wp-friendlink-apply'); ?></th>';
                    html += '<th><?php _e('回链状态', 'wp-friendlink-apply'); ?></th>';
                    html += '</tr></thead>';
                    html += '<tbody>';
                    
                    for (var i = 0; i < results.length; i++) {
                        var item = results[i];
                        var statusClass = item.has_backlink ? 'has-backlink' : 'no-backlink';
                        var statusText = item.has_backlink ? '<?php _e('正常', 'wp-friendlink-apply'); ?>' : '<?php _e('异常', 'wp-friendlink-apply'); ?>';
                        
                        html += '<tr>';
                        html += '<td>' + item.site_name + '</td>';
                        html += '<td><a href="' + item.site_url + '" target="_blank">' + item.site_url + '</a></td>';
                        html += '<td><span class="backlink-status ' + statusClass + '">' + statusText + '</span></td>';
                        html += '</tr>';
                    }
                    
                    html += '</tbody></table>';
                    html += '<p class="bulk-check-summary">' + response.data.message + '</p>';
                    html += '</div>';
                    
                    showResultModal(html);
                } else {
                    alert(response.data.message || '<?php _e('操作失败', 'wp-friendlink-apply'); ?>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('批量检测回链', 'wp-friendlink-apply'); ?>');
                updateBulkButtons();
            }
        });
    });
    
    function showResultModal(content) {
        var modalHtml = '<div class="bulk-check-modal-overlay" id="bulk-check-modal">';
        modalHtml += '<div class="bulk-check-modal">';
        modalHtml += '<div class="bulk-check-modal-header">';
        modalHtml += '<span class="bulk-check-modal-title"><?php _e('检测结果', 'wp-friendlink-apply'); ?></span>';
        modalHtml += '<button class="bulk-check-modal-close">&times;</button>';
        modalHtml += '</div>';
        modalHtml += '<div class="bulk-check-modal-body">' + content + '</div>';
        modalHtml += '</div></div>';
        
        $('body').append(modalHtml);
        
        $('#bulk-check-modal').fadeIn(200);
        
        $('#bulk-check-modal .bulk-check-modal-close, #bulk-check-modal.bulk-check-modal-overlay').on('click', function() {
            $('#bulk-check-modal').fadeOut(200, function() {
                $(this).remove();
            });
        });
        
        $('.bulk-check-modal').on('click', function(e) {
            e.stopPropagation();
        });
    }
    
    function bulkAction(action, ids) {
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_' + action,
                nonce: wpFriendlinkApply.nonce,
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('操作失败', 'wp-friendlink-apply'); ?>');
                }
            }
        });
    }
    
    function checkSiteStatus($cell, url) {
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_check_site_status',
                nonce: wpFriendlinkApply.nonce,
                site_url: url
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var $statusBadge = $cell.find('.site-status-badge');
                    var $responseTime = $cell.find('.response-time-small');
                    
                    $statusBadge.removeClass('checking').addClass('status-' + data.status);
                    $statusBadge.text(data.status_label);
                    
                    $responseTime.removeClass('fast medium slow').addClass(data.speed_class);
                    $responseTime.text(data.response_time);
                } else {
                    var $statusBadge = $cell.find('.site-status-badge');
                    $statusBadge.removeClass('checking').addClass('status-error');
                    $statusBadge.text('<?php _e('检测失败', 'wp-friendlink-apply'); ?>');
                }
            },
            error: function() {
                var $statusBadge = $cell.find('.site-status-badge');
                $statusBadge.removeClass('checking').addClass('status-error');
                $statusBadge.text('<?php _e('检测失败', 'wp-friendlink-apply'); ?>');
            }
        });
    }
    
    function checkAllSitesStatus() {
        var $cells = $('.status-response-cell');
        var index = 0;
        
        function checkNext() {
            if (index >= $cells.length) return;
            
            var $cell = $cells.eq(index);
            var url = $cell.data('url');
            
            if (url) {
                checkSiteStatus($cell, url);
            }
            
            index++;
            setTimeout(checkNext, 500);
        }
        
        checkNext();
    }
    
    setTimeout(checkAllSitesStatus, 300);
    
    $(document).on('click', '.edit-link-btn', function() {
        var $btn = $(this);
        var linkId = $btn.data('link-id');
        var name = $btn.data('name');
        var url = $btn.data('url');
        var description = $btn.data('description');
        var icon = $btn.data('icon');
        
        var modalHtml = '<div class="edit-link-modal-overlay" id="edit-link-modal">';
        modalHtml += '<div class="edit-link-modal">';
        modalHtml += '<div class="edit-link-modal-header">';
        modalHtml += '<span class="edit-link-modal-title"><?php _e('编辑链接信息', 'wp-friendlink-apply'); ?></span>';
        modalHtml += '<button class="edit-link-modal-close">&times;</button>';
        modalHtml += '</div>';
        modalHtml += '<div class="edit-link-modal-body">';
        modalHtml += '<form id="edit-link-form">';
        modalHtml += '<input type="hidden" name="link_id" value="' + linkId + '">';
        modalHtml += '<div class="form-field">';
        modalHtml += '<label><?php _e('站点名称', 'wp-friendlink-apply'); ?></label>';
        modalHtml += '<input type="text" name="link_name" value="' + name + '" required>';
        modalHtml += '</div>';
        modalHtml += '<div class="form-field">';
        modalHtml += '<label><?php _e('站点地址', 'wp-friendlink-apply'); ?></label>';
        modalHtml += '<input type="url" name="link_url" value="' + url + '" required>';
        modalHtml += '</div>';
        modalHtml += '<div class="form-field">';
        modalHtml += '<label><?php _e('站点描述', 'wp-friendlink-apply'); ?></label>';
        modalHtml += '<textarea name="link_description" rows="3">' + description + '</textarea>';
        modalHtml += '</div>';
        modalHtml += '<div class="form-field">';
        modalHtml += '<label><?php _e('站点图标URL', 'wp-friendlink-apply'); ?></label>';
        modalHtml += '<input type="url" name="link_image" value="' + icon + '" placeholder="<?php _e('可选，留空则显示默认图标', 'wp-friendlink-apply'); ?>">';
        modalHtml += '</div>';
        modalHtml += '<div class="form-actions">';
        modalHtml += '<button type="button" class="button cancel-edit-btn"><?php _e('取消', 'wp-friendlink-apply'); ?></button>';
        modalHtml += '<button type="submit" class="button button-primary"><?php _e('保存', 'wp-friendlink-apply'); ?></button>';
        modalHtml += '</div>';
        modalHtml += '</form>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        
        $('body').append(modalHtml);
        $('#edit-link-modal').addClass('show');
    });
    
    $(document).on('click', '.edit-link-modal-close, .cancel-edit-btn', function() {
        $('#edit-link-modal').removeClass('show');
        setTimeout(function() {
            $('#edit-link-modal').remove();
        }, 200);
    });
    
    $(document).on('click', '.edit-link-modal-overlay', function(e) {
        if (e.target === this) {
            $(this).removeClass('show');
            setTimeout(function() {
                $('#edit-link-modal').remove();
            }, 200);
        }
    });
    
    $(document).on('submit', '#edit-link-form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).text('<?php _e('保存中...', 'wp-friendlink-apply'); ?>');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_update_link',
                nonce: wpFriendlinkApply.nonce,
                link_id: $form.find('input[name="link_id"]').val(),
                link_name: $form.find('input[name="link_name"]').val(),
                link_url: $form.find('input[name="link_url"]').val(),
                link_description: $form.find('textarea[name="link_description"]').val(),
                link_image: $form.find('input[name="link_image"]').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('保存失败', 'wp-friendlink-apply'); ?>');
                    $submitBtn.prop('disabled', false).text('<?php _e('保存', 'wp-friendlink-apply'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('请求失败', 'wp-friendlink-apply'); ?>');
                $submitBtn.prop('disabled', false).text('<?php _e('保存', 'wp-friendlink-apply'); ?>');
            }
        });
    });
    
    $(document).on('click', '.disable-link-btn', function() {
        var linkId = $(this).data('link-id');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_toggle_link',
                nonce: wpFriendlinkApply.nonce,
                link_id: linkId,
                visible: 'N'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('操作失败', 'wp-friendlink-apply'); ?>');
                }
            }
        });
    });
    
    $(document).on('click', '.enable-link-btn', function() {
        var linkId = $(this).data('link-id');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_toggle_link',
                nonce: wpFriendlinkApply.nonce,
                link_id: linkId,
                visible: 'Y'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('操作失败', 'wp-friendlink-apply'); ?>');
                }
            }
        });
    });
});
</script>
