document.addEventListener('DOMContentLoaded', function() {
    // 注册页面显示与切换
    var showRegisterLink = document.getElementById('show-register-link');
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');

    if (showRegisterLink) {
        showRegisterLink.addEventListener('click', function(e) {
            e.preventDefault();
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });
    }

    var returnButton = document.getElementById('return-login');
    if (returnButton) {
        returnButton.addEventListener('click', function(e) {
            e.preventDefault();
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        });
    }

    // 审核操作
    var currentEnterpriseId = null;

    // 点击驳回按钮时显示模态框
    jQuery('.reject-button').on('click', function(e) {
        e.preventDefault();
        currentEnterpriseId = jQuery(this).data('id');
        jQuery('#reject-modal').show();  // 显示驳回模态框
    });

    // 取消驳回操作
    jQuery('#cancel-reject').on('click', function() {
        jQuery('#reject-modal').hide();  // 隐藏模态框
        jQuery('#reject-reason').val('');  // 清空原因输入框
    });

    // 提交驳回操作
    jQuery('#submit-reject').on('click', function() {
        var reason = jQuery('#reject-reason').val();
        if (reason === '') {
            alert('请填写驳回原因');
            return;
        }

        jQuery.ajax({
            url: custom_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'handle_enterprise_review_action',
                review_action: 'reject',
                id: currentEnterpriseId,
                reason: reason,
                security: custom_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    jQuery('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.status-column').text(response.new_status);
                    jQuery('#reject-modal').hide();
                    jQuery('#reject-reason').val('');
                    // 提示操作完成
                    alert('驳回操作成功，企业状态已更新。');
                    // 刷新企业表格
                    jQuery('#enterprise-table').load(window.location.href + ' #enterprise-table');
                } else {
                    alert('操作失败：' + response.data);
                }
            }
        });
    });

    // 通过操作的逻辑
    jQuery('.approve-button').on('click', function(e) {
        e.preventDefault();
        currentEnterpriseId = jQuery(this).data('id');
    
        jQuery.ajax({
            url: custom_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'handle_enterprise_review_action',
                review_action: 'approve',
                id: currentEnterpriseId,
                security: custom_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    // 更新企业状态
                    jQuery('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.status-column').text(response.new_status);
                    jQuery('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.approve-button').remove();
                    jQuery('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.reject-button').remove();
    
                    // 显示反馈消息，表明企业审核通过
                    alert('企业审核通过，状态已更新！');
                    
                    // 刷新企业表格
                    jQuery('#enterprise-table').load(window.location.href + ' #enterprise-table');
                } else {
                    alert('操作失败：' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // 在出现请求错误时处理
                alert('请求失败，请重试或检查网络连接：' + error);
            }
        });
    });

    // 修改信息的按钮逻辑
    const editButton = document.getElementById('edit-info-button');
    const editForm = document.getElementById('edit-form');

    if (editButton && editForm) {
        editButton.addEventListener('click', function() {
            editForm.style.display = 'block';
            editButton.style.display = 'none';
        });
    }
});
