jQuery(document).ready(function($) {
    'use strict';
    
    var wpFriendlinkApply = window.wpFriendlinkApply || {};
    
    var Utils = {
        confirmAction: function(message) {
            return confirm(message);
        },
        
        showAlert: function(message) {
            alert(message);
        },
        
        reloadPage: function() {
            setTimeout(function() {
                location.reload();
            }, 500);
        }
    };
    
    var UI = {
        setButtonLoading: function($button, loading, originalText) {
            if (loading) {
                $button.prop('disabled', true).text(wpFriendlinkApply.strings.processing);
            } else {
                $button.prop('disabled', false).text(originalText);
            }
        },
        
        showNotification: function(message, type) {
            alert(message);
        }
    };
    
    var API = {
        performAction: function(action, applicationId, successCallback, errorCallback) {
            $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: wpFriendlinkApply.nonce,
                    application_id: applicationId
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        errorCallback(response.data.message || wpFriendlinkApply.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Action error:', error);
                    errorCallback(wpFriendlinkApply.strings.error);
                }
            });
        },
        
        checkBacklink: function(siteUrl, successCallback, errorCallback) {
            $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_friendlink_check_backlink',
                    nonce: wpFriendlinkApply.nonce,
                    site_url: siteUrl
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        errorCallback(response.data.message || wpFriendlinkApply.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Check backlink error:', error);
                    errorCallback(wpFriendlinkApply.strings.check_error || '检测失败');
                }
            });
        }
    };
    
    var Actions = {
        approve: function(applicationId, $button) {
            if (!Utils.confirmAction(wpFriendlinkApply.strings.confirm_approve)) {
                return;
            }
            
            var originalText = $button.text();
            UI.setButtonLoading($button, true);
            
            API.performAction('wp_friendlink_approve', applicationId, function(data) {
                UI.showNotification(data.message || wpFriendlinkApply.strings.success, 'success');
                Utils.reloadPage();
            }, function(errorMessage) {
                UI.showNotification(errorMessage, 'error');
                UI.setButtonLoading($button, false, originalText);
            });
        },
        
        reject: function(applicationId, $button) {
            var rejectReason = prompt(wpFriendlinkApply.strings.reject_reason_prompt || '请输入拒绝理由（可选）：', '');
            
            if (rejectReason === null) {
                return;
            }
            
            if (!Utils.confirmAction(wpFriendlinkApply.strings.confirm_reject)) {
                return;
            }
            
            var originalText = $button.text();
            UI.setButtonLoading($button, true);
            
            $.ajax({
                url: wpFriendlinkApply.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_friendlink_reject',
                    nonce: wpFriendlinkApply.nonce,
                    application_id: applicationId,
                    reject_reason: rejectReason
                },
                success: function(response) {
                    if (response.success) {
                        UI.showNotification(response.data.message || wpFriendlinkApply.strings.success, 'success');
                        Utils.reloadPage();
                    } else {
                        UI.showNotification(response.data.message || wpFriendlinkApply.strings.error, 'error');
                        UI.setButtonLoading($button, false, originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Reject error:', error);
                    UI.showNotification(wpFriendlinkApply.strings.error, 'error');
                    UI.setButtonLoading($button, false, originalText);
                }
            });
        },
        
        delete: function(applicationId, $button) {
            if (!Utils.confirmAction(wpFriendlinkApply.strings.confirm_delete)) {
                return;
            }
            
            var originalText = $button.text();
            UI.setButtonLoading($button, true);
            
            API.performAction('wp_friendlink_delete', applicationId, function(data) {
                UI.showNotification(data.message || wpFriendlinkApply.strings.success, 'success');
                Utils.reloadPage();
            }, function(errorMessage) {
                UI.showNotification(errorMessage, 'error');
                UI.setButtonLoading($button, false, originalText);
            });
        },
        
        checkBacklink: function(applicationId, $button) {
            var siteUrl = $button.data('url');
            var originalText = $button.text();
            UI.setButtonLoading($button, true, '🔗 检测中...');
            
            API.checkBacklink(siteUrl, function(data) {
                var message = data.message;
                var alertType = data.has_backlink ? 'success' : 'error';
                UI.showNotification(message, alertType);
                UI.setButtonLoading($button, false, originalText);
            }, function(errorMessage) {
                UI.showNotification(errorMessage, 'error');
                UI.setButtonLoading($button, false, originalText);
            });
        }
    };
    
    $(document).on('click', '.approve-btn', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.approve(applicationId, $(this));
    });
    
    $(document).on('click', '.reject-btn', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.reject(applicationId, $(this));
    });
    
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.delete(applicationId, $(this));
    });
    
    $(document).on('click', '.check-backlink-btn', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.checkBacklink(applicationId, $(this));
    });
    
    $(document).on('click', '.btn-approve', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.approve(applicationId, $(this));
    });
    
    $(document).on('click', '.btn-reject', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.reject(applicationId, $(this));
    });
    
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var applicationId = $(this).data('id');
        Actions.delete(applicationId, $(this));
    });
    
    $(document).on('click', '.style-option', function(e) {
        e.preventDefault();
        $('.style-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
});
