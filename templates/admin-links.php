<div class="wrap wp-friendlink-apply-links">
    <h1 class="page-title">
        <span class="icon">🔗</span>
        <?php _e('所有链接', 'wp-friendlink-apply'); ?>
    </h1>
    
    <?php
    $links = get_bookmarks(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_invisible' => false
    ));
    
    $total_count = count($links);
    $visible_count = 0;
    $hidden_count = 0;
    
    foreach ($links as $link) {
        if ($link->link_visible === 'Y') {
            $visible_count++;
        } else {
            $hidden_count++;
        }
    }
    ?>
    
    <div class="stats-overview">
        <div class="stat-card total clickable active" data-filter="all">
            <span class="stat-number"><?php echo $total_count; ?></span>
            <span class="stat-label"><?php _e('总计', 'wp-friendlink-apply'); ?></span>
        </div>
        <div class="stat-card visible clickable" data-filter="visible">
            <span class="stat-number"><?php echo $visible_count; ?></span>
            <span class="stat-label"><?php _e('已启用', 'wp-friendlink-apply'); ?></span>
        </div>
        <div class="stat-card disabled clickable" data-filter="hidden">
            <span class="stat-number"><?php echo $hidden_count; ?></span>
            <span class="stat-label"><?php _e('已禁用', 'wp-friendlink-apply'); ?></span>
        </div>
    </div>
    
    <div class="bulk-actions-bar">
        <div class="bulk-actions-left">
            <label class="select-all-wrapper">
                <input type="checkbox" id="select-all">
                <span><?php _e('全选', 'wp-friendlink-apply'); ?></span>
            </label>
            <button class="button bulk-btn" id="bulk-check" disabled><?php _e('批量检测回链', 'wp-friendlink-apply'); ?></button>
            <button class="button bulk-btn" id="bulk-enable" disabled><?php _e('批量启用', 'wp-friendlink-apply'); ?></button>
            <button class="button bulk-btn" id="bulk-disable" disabled><?php _e('批量禁用', 'wp-friendlink-apply'); ?></button>
            <button class="button bulk-btn bulk-btn-delete" id="bulk-delete" disabled><?php _e('批量删除', 'wp-friendlink-apply'); ?></button>
        </div>
        <div class="search-box">
            <input type="text" id="link-search" placeholder="<?php _e('搜索站点名称或地址...', 'wp-friendlink-apply'); ?>">
            <button class="button" id="search-btn"><?php _e('搜索', 'wp-friendlink-apply'); ?></button>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped links-table" id="links-table">
        <thead>
            <tr>
                <th class="column-check"></th>
                <th class="column-name"><?php _e('站点名称', 'wp-friendlink-apply'); ?></th>
                <th class="column-url"><?php _e('站点地址', 'wp-friendlink-apply'); ?></th>
                <th class="column-status"><?php _e('添加状态', 'wp-friendlink-apply'); ?></th>
                <th class="column-response"><?php _e('站点状态/响应时间', 'wp-friendlink-apply'); ?></th>
                <th class="column-actions"><?php _e('操作', 'wp-friendlink-apply'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($links)) : ?>
                <tr class="no-items">
                    <td colspan="6">
                        <div class="empty-state">
                            <span class="empty-icon">🔗</span>
                            <p><?php _e('暂无链接记录', 'wp-friendlink-apply'); ?></p>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($links as $link) : ?>
                    <tr data-visible="<?php echo $link->link_visible === 'Y' ? 'visible' : 'hidden'; ?>" data-id="<?php echo $link->link_id; ?>">
                        <td class="column-check">
                            <input type="checkbox" class="row-checkbox" value="<?php echo $link->link_id; ?>">
                        </td>
                        <td class="column-name" data-label="<?php _e('站点名称', 'wp-friendlink-apply'); ?>">
                            <strong class="site-name"><?php echo esc_html($link->link_name); ?></strong>
                        </td>
                        <td class="column-url" data-label="<?php _e('站点地址', 'wp-friendlink-apply'); ?>">
                            <a href="<?php echo esc_url($link->link_url); ?>" target="_blank" class="site-url">
                                <?php echo esc_html($link->link_url); ?>
                            </a>
                        </td>
                        <td class="column-status" data-label="<?php _e('添加状态', 'wp-friendlink-apply'); ?>">
                            <?php if ($link->link_visible === 'Y') : ?>
                                <span class="status-tag status-visible"><?php _e('已启用', 'wp-friendlink-apply'); ?></span>
                            <?php else : ?>
                                <span class="status-tag status-hidden"><?php _e('已禁用', 'wp-friendlink-apply'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-response" data-label="<?php _e('站点状态/响应时间', 'wp-friendlink-apply'); ?>">
                            <div class="status-response-cell" data-url="<?php echo esc_url($link->link_url); ?>">
                                <span class="site-status-badge checking"><?php _e('检测中...', 'wp-friendlink-apply'); ?></span>
                                <span class="response-time-small">--</span>
                            </div>
                        </td>
                        <td class="column-actions" data-label="<?php _e('操作', 'wp-friendlink-apply'); ?>">
                            <div class="actions">
                                <button class="button button-small check-backlink-btn" data-id="<?php echo $link->link_id; ?>" data-url="<?php echo esc_url($link->link_url); ?>" title="<?php _e('检测回链', 'wp-friendlink-apply'); ?>">
                                    🔗
                                </button>
                                <a href="<?php echo admin_url('link.php?action=edit&link_id=' . $link->link_id); ?>" class="button button-small" title="<?php _e('编辑', 'wp-friendlink-apply'); ?>">
                                    ✏️
                                </a>
                                <?php if ($link->link_visible === 'Y') : ?>
                                    <button class="button button-small disable-link-btn" data-id="<?php echo $link->link_id; ?>" title="<?php _e('禁用', 'wp-friendlink-apply'); ?>">
                                        🚫
                                    </button>
                                <?php else : ?>
                                    <button class="button button-small button-primary enable-link-btn" data-id="<?php echo $link->link_id; ?>" title="<?php _e('启用', 'wp-friendlink-apply'); ?>">
                                        ✅
                                    </button>
                                <?php endif; ?>
                                <button class="button button-small button-delete delete-link-btn" data-id="<?php echo $link->link_id; ?>" title="<?php _e('删除', 'wp-friendlink-apply'); ?>">
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
        $('#bulk-check, #bulk-enable, #bulk-disable, #bulk-delete').prop('disabled', checkedCount === 0);
    }
    
    var currentFilter = 'all';
    var searchKeyword = '';
    
    function filterRows() {
        var $rows = $('#links-table tbody tr').not('.no-items');
        
        $rows.each(function() {
            var $row = $(this);
            var visible = $row.data('visible');
            var name = $row.find('.site-name').text().toLowerCase();
            var url = $row.find('.site-url').text().toLowerCase();
            var matchSearch = searchKeyword === '' || name.indexOf(searchKeyword) > -1 || url.indexOf(searchKeyword) > -1;
            var matchFilter = currentFilter === 'all' || visible === currentFilter;
            
            if (matchSearch && matchFilter) {
                $row.show();
            } else {
                $row.hide();
            }
        });
        
        $('#select-all').prop('checked', false);
        $('.row-checkbox').prop('checked', false);
        updateBulkButtons();
    }
    
    function applyFilter(filter) {
        currentFilter = filter;
        $('.stat-card').removeClass('active');
        $('.stat-card[data-filter="' + filter + '"]').addClass('active');
        filterRows();
    }
    
    $('.stat-card').on('click', function() {
        var filter = $(this).data('filter');
        applyFilter(filter);
    });
    
    $('#link-search').on('input', function() {
        searchKeyword = $(this).val().toLowerCase();
        filterRows();
    });
    
    $('#search-btn').on('click', function() {
        searchKeyword = $('#link-search').val().toLowerCase();
        filterRows();
    });
    
    $('#link-search').on('keypress', function(e) {
        if (e.which === 13) {
            searchKeyword = $(this).val().toLowerCase();
            filterRows();
        }
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
    
    $('#bulk-check').on('click', function() {
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('检测中...', 'wp-friendlink-apply'); ?>');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_bulk_check_links',
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
    
    $('#bulk-enable').on('click', function() {
        if (!confirm('<?php _e('确定批量启用选中的链接？', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkLinkAction('bulk_enable_links', ids);
    });
    
    $('#bulk-disable').on('click', function() {
        if (!confirm('<?php _e('确定批量禁用选中的链接？', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkLinkAction('bulk_disable_links', ids);
    });
    
    $('#bulk-delete').on('click', function() {
        if (!confirm('<?php _e('确定批量删除选中的链接？此操作不可恢复！', 'wp-friendlink-apply'); ?>')) return;
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        
        bulkLinkAction('bulk_delete_links', ids);
    });
    
    function bulkLinkAction(action, ids) {
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
    
    $(document).on('click', '.check-backlink-btn', function() {
        var $btn = $(this);
        var url = $btn.data('url');
        var $row = $btn.closest('tr');
        var $cell = $row.find('.status-response-cell');
        
        $btn.prop('disabled', true);
        checkSiteStatus($cell, url, function() {
            $btn.prop('disabled', false);
        });
    });
    
    $(document).on('click', '.enable-link-btn', function() {
        var id = $(this).data('id');
        toggleLinkStatus(id, 'Y');
    });
    
    $(document).on('click', '.disable-link-btn', function() {
        var id = $(this).data('id');
        toggleLinkStatus(id, 'N');
    });
    
    $(document).on('click', '.delete-link-btn', function() {
        if (!confirm('<?php _e('确定删除此链接？此操作不可恢复！', 'wp-friendlink-apply'); ?>')) return;
        var id = $(this).data('id');
        
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_delete_link',
                nonce: wpFriendlinkApply.nonce,
                link_id: id
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
    
    function toggleLinkStatus(linkId, visible) {
        $.ajax({
            url: wpFriendlinkApply.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_friendlink_toggle_link',
                nonce: wpFriendlinkApply.nonce,
                link_id: linkId,
                visible: visible
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
    
    function checkSiteStatus($cell, url, callback) {
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
            },
            complete: function() {
                if (callback) callback();
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
});
</script>
