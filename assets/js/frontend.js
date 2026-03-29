jQuery(document).ready(function($) {
    'use strict';
    
    var wpFriendlinkApply = window.wpFriendlinkApply || {};
    var $form = $('#wp-friendlink-apply-form');
    var $message = $('.form-message');
    var isSubmitting = false;
    
    var Utils = {
        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },
        
        isValidEmail: function(string) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(string);
        },
        
        normalizeUrl: function(url) {
            url = url.trim();
            if (url && !/^https?:\/\//i.test(url)) {
                url = 'https://' + url;
            }
            return url;
        },
        
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    var Validator = {
        validateForm: function(formData) {
            var errors = [];
            
            if (!formData.site_name) {
                errors.push(wpFriendlinkApply.strings.required_fields || '请填写所有必填字段');
            }
            
            if (!formData.site_url) {
                errors.push(wpFriendlinkApply.strings.required_fields || '请填写所有必填字段');
            } else if (!Utils.isValidUrl(formData.site_url)) {
                errors.push(wpFriendlinkApply.strings.invalid_url || '请输入有效的网站地址');
            }
            
            if (formData.email && !Utils.isValidEmail(formData.email)) {
                errors.push(wpFriendlinkApply.strings.invalid_email || '请输入有效的邮箱地址');
            }
            
            return errors;
        }
    };
    
    var UI = {
        showMessage: function(message, type) {
            var $modal = $('.wp-friendlink-modal');
            
            if ($modal.length === 0) {
                UI.createModal();
                $modal = $('.wp-friendlink-modal');
            }
            
            var icon = type === 'success' ? '✓' : '✕';
            var title = type === 'success' ? '成功' : '错误';
            
            $modal.removeClass('success error').addClass(type);
            $modal.find('.modal-icon').text(icon);
            $modal.find('.modal-header h3').text(title);
            $modal.find('.modal-body p').text(message);
            
            $modal.addClass('show');
            
            $modal.find('.modal-close-btn').off('click').on('click', function() {
                $modal.removeClass('show');
            });
            
            $modal.find('.modal-overlay').off('click').on('click', function() {
                $modal.removeClass('show');
            });
            
            setTimeout(function() {
                $modal.removeClass('show');
            }, 15000);
        },
        
        createModal: function() {
            var modalHtml = '<div class="wp-friendlink-modal">' +
                '<div class="modal-overlay"></div>' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<span class="modal-icon">✓</span>' +
                '<h3>成功</h3>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p></p>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="modal-close-btn">确定</button>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            $('body').append(modalHtml);
        },
        
        hideMessage: function() {
            var $modal = $('.wp-friendlink-modal');
            $modal.removeClass('show');
        },
        
        setButtonLoading: function($button, loading, text) {
            if (loading) {
                $button.prop('disabled', true);
                $button.text(text || '提交中...');
            } else {
                $button.prop('disabled', false);
                $button.text(text || '提交申请');
            }
        },
        
        resetForm: function(clearAll) {
            if (clearAll) {
                $form[0].reset();
            } else {
                $('#site_icon').val('');
                $('#site_description').val('');
            }
            
            UI.updateIconPreview('');
        },
        
        updateIconPreview: function(iconUrl) {
            var $preview = $('.icon-preview');
            var $img = $('#icon-preview-img');
            
            if (iconUrl) {
                $img.attr('src', iconUrl);
                $img.show();
                $preview.removeClass('empty');
            } else {
                $img.hide();
                $preview.addClass('empty');
            }
        }
    };
    
    var API = {
        submitApplication: function(data, successCallback, errorCallback) {
            return $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        errorCallback(response.data.message || wpFriendlinkApply.strings.error || '提交失败，请稍后重试');
                    }
                },
                error: function(xhr, status, error) {
                    errorCallback(wpFriendlinkApply.strings.error || '提交失败，请稍后重试');
                }
            });
        },
        
        fetchSiteInfo: function(url, successCallback, errorCallback) {
            return $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_friendlink_fetch_site_info',
                    nonce: wpFriendlinkApply.nonce,
                    url: url
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        errorCallback(response.data.message || wpFriendlinkApply.strings.fetch_error || '获取网站信息失败');
                    }
                },
                error: function(xhr, status, error) {
                    errorCallback(wpFriendlinkApply.strings.fetch_error || '获取网站信息失败');
                }
            });
        }
    };
    
    if ($form.length === 0) {
        return;
    }
    
    // 表单提交
    $form.on('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) {
            return;
        }
        
        UI.hideMessage();
        
        var formData = {
            site_name: $('#site_name').val().trim(),
            site_url: Utils.normalizeUrl($('#site_url').val()),
            site_icon: $('#site_icon').val().trim(),
            site_description: $('#site_description').val().trim(),
            email: $('#email').length ? $('#email').val().trim() : ''
        };
        
        var errors = Validator.validateForm(formData);
        if (errors.length > 0) {
            UI.showMessage(errors[0], 'error');
            return;
        }
        
        isSubmitting = true;
        
        var $submitBtn = $('#submit-application');
        var originalText = $submitBtn.text();
        
        UI.setButtonLoading($submitBtn, true, wpFriendlinkApply.strings.submitting || '提交中...');
        
        var submitData = {
            action: 'wp_friendlink_submit_application',
            nonce: wpFriendlinkApply.nonce,
            site_name: formData.site_name,
            site_url: formData.site_url,
            site_icon: formData.site_icon,
            site_description: formData.site_description,
            email: formData.email
        };
        
        API.submitApplication(submitData, function(data) {
            UI.showMessage(data.message || wpFriendlinkApply.strings.success || '提交成功', 'success');
            UI.resetForm(false);
        }, function(errorMessage) {
            UI.showMessage(errorMessage, 'error');
        }).always(function() {
            isSubmitting = false;
            UI.setButtonLoading($submitBtn, false, originalText);
        });
    });
    
    $('#fetch-site-info').on('click', function() {
        var $button = $(this);
        var url = Utils.normalizeUrl($('#site_url').val());
        
        if (!url || !Utils.isValidUrl(url)) {
            UI.showMessage(wpFriendlinkApply.strings.invalid_url || '请输入有效的网站地址', 'error');
            return;
        }
        
        var originalText = $button.html();
        UI.setButtonLoading($button, true, wpFriendlinkApply.strings.fetching || '获取中...');
        
        API.fetchSiteInfo(url, function(data) {
            if (data.title) {
                $('#site_name').val(data.title);
            }
            if (data.description) {
                $('#site_description').val(data.description);
            }
            if (data.icon) {
                $('#site_icon').val(data.icon);
                UI.updateIconPreview(data.icon);
            }
            
            UI.showMessage(wpFriendlinkApply.strings.fetch_success || '网站信息获取成功！', 'success');
        }, function(errorMessage) {
            UI.showMessage(errorMessage, 'error');
        }).always(function() {
            UI.setButtonLoading($button, false, originalText);
        });
    });
    
    $('#site_icon').on('change', function() {
        var iconUrl = $(this).val().trim();
        UI.updateIconPreview(iconUrl);
    });
    
    $('.friendlink-filter-btn').on('click', function() {
        var $btn = $(this);
        var filter = $btn.data('filter');
        
        $('.friendlink-filter-btn').removeClass('active');
        $btn.addClass('active');
        
        var $rows = $('.friendlink-table tbody tr');
        
        $rows.removeClass('friendlink-row-hidden');
        
        if (filter === 'all') {
            return;
        }
        
        $rows.each(function() {
            var $row = $(this);
            var status = $row.data('status');
            var hasBacklink = $row.data('backlink');
            
            var shouldHide = false;
            
            if (filter === 'normal') {
                if (status !== 'ok') {
                    shouldHide = true;
                }
            } else if (filter === 'abnormal') {
                if (status === 'ok' || status === 'unknown') {
                    shouldHide = true;
                }
            } else if (filter === 'no-backlink') {
                if (hasBacklink === 1) {
                    shouldHide = true;
                }
            }
            
            if (shouldHide) {
                $row.addClass('friendlink-row-hidden');
            }
        });
    });
    
    var FriendLinkChecker = {
        checkAll: function() {
            var $items = $('.friendlink-card-item[data-url], .friendlink-table tbody tr[data-url]');
            
            $items.each(function() {
                var $item = $(this);
                var url = $item.data('url');
                
                if (!url) {
                    return;
                }
                
                FriendLinkChecker.checkSingle(url, $item);
            });
        },
        
        checkSingle: function(url, $item) {
            $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_friendlink_check_single',
                    nonce: wpFriendlinkApply.nonce,
                    site_url: url
                },
                dataType: 'json',
                timeout: 15000,
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        FriendLinkChecker.updateUI($item, data);
                    } else {
                        FriendLinkChecker.showError($item);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Backlink check error:', url, status, error);
                    FriendLinkChecker.showError($item);
                }
            });
        },
        
        updateUI: function($item, data) {
            var responseTime = data.response_time;
            var hasBacklink = data.has_backlink;
            var siteStatus = data.site_status;
            
            var timeClass = 'slow';
            if (responseTime < 1000) {
                timeClass = 'fast';
            } else if (responseTime < 3000) {
                timeClass = 'medium';
            }
            
            var timeText = (responseTime / 1000).toFixed(2) + 's';
            
            var $cardMeta = $item.find('.friendlink-card-meta');
            if ($cardMeta.length) {
                $cardMeta.html('<span class="friendlink-response-time ' + timeClass + '">' + timeText + '</span>');
            }
            
            var $statusDot = $item.find('.friendlink-card-status-dot');
            if ($statusDot.length) {
                $statusDot.removeClass('has-backlink no-backlink').addClass(hasBacklink ? 'has-backlink' : 'no-backlink');
            }
            
            var $tableTime = $item.find('.friendlink-response-time');
            if ($tableTime.length && $item.is('tr')) {
                $tableTime.removeClass('fast medium slow').addClass(timeClass).text(timeText);
            }
            
            var $tableStatus = $item.find('.friendlink-status-badge');
            if ($tableStatus.length) {
                var statusClass = 'status-unknown';
                var statusText = '未知';
                if (siteStatus === 'ok') {
                    statusClass = 'status-ok';
                    statusText = '正常';
                } else if (siteStatus === 'client_error') {
                    statusClass = 'status-warning';
                    statusText = '客户端错误';
                } else if (siteStatus === 'server_error') {
                    statusClass = 'status-error';
                    statusText = '服务器错误';
                }
                $tableStatus.removeClass('status-ok status-warning status-error status-unknown').addClass(statusClass).text(statusText);
            }
            
            var $tableBacklink = $item.find('.friendlink-backlink-badge');
            if ($tableBacklink.length) {
                $tableBacklink.removeClass('has-backlink no-backlink').addClass(hasBacklink ? 'has-backlink' : 'no-backlink').text(hasBacklink ? '正常' : '异常');
            }
            
            $item.attr('data-status', siteStatus);
            $item.attr('data-backlink', hasBacklink ? '1' : '0');
            
            FriendLinkChecker.updateStats();
        },
        
        updateStats: function() {
            var normalCount = 0;
            var abnormalCount = 0;
            var noBacklinkCount = 0;
            
            $('.friendlink-table tbody tr').each(function() {
                var $row = $(this);
                var status = $row.attr('data-status');
                var backlink = $row.attr('data-backlink');
                
                if (status === 'ok') {
                    normalCount++;
                } else if (status !== 'unknown') {
                    abnormalCount++;
                }
                if (backlink === '0') {
                    noBacklinkCount++;
                }
            });
            
            $('#normal-count').text(normalCount);
            $('#abnormal-count').text(abnormalCount);
            $('#no-backlink-count').text(noBacklinkCount);
        },
        
        showError: function($item) {
            var $cardMeta = $item.find('.friendlink-card-meta');
            if ($cardMeta.length) {
                $cardMeta.html('<span class="friendlink-response-time">-</span>');
            }
            
            var $tableTime = $item.find('.friendlink-response-time');
            if ($tableTime.length) {
                $tableTime.text('-');
            }
        }
    };
    
    if (wpFriendlinkApply.show_backlink && $('.friendlink-card-item[data-url], .friendlink-table tbody tr[data-url]').length > 0) {
        setTimeout(function() {
            FriendLinkChecker.checkAll();
        }, 500);
    }
});
