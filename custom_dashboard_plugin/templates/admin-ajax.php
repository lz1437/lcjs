<?php

// 后端处理逻辑
function handle_enterprise_review_action() {
    check_ajax_referer('enterprise-review-action', 'security');  // Nonce 验证

    // 检查必需的参数
    if (!isset($_POST['id']) || !isset($_POST['review_action'])) {
        wp_send_json_error('缺少必要的参数');
    }

    $enterprise_id = intval($_POST['id']);
    $action = sanitize_text_field($_POST['review_action']);
    $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : ''; // 驳回原因

    global $wpdb;
    $current_user = wp_get_current_user();  // 获取当前登录用户信息
    $table_name = $wpdb->prefix . 'enterprise_applications';

    // 获取当前企业的状态
    $enterprise = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $enterprise_id));

    if (!$enterprise) {
        wp_send_json_error('未找到该企业');
    }

    // 获取当前用户角色
    $user_roles = $current_user->roles;

    // 根据请求的 action 更新状态
    $new_status = '';
    $update_fields = array();  // 用于保存需要更新的字段

    // 处理 "通过" 审核的逻辑
    if ($action === 'approve') {
        // 验证用户角色，并更新对应角色的审核状态
        if (in_array('chief_engineer', $user_roles) && $enterprise->status === '技术总工审核中') {
            $new_status = '项目经理审核中';
            $update_fields['chief_engineer_status'] = '通过';
            $update_fields['chief_engineer_user'] = $current_user->user_login;
        } elseif (in_array('manager', $user_roles) && $enterprise->status === '项目经理审核中') {
            $new_status = '总经理审核中';
            $update_fields['manager_status'] = '通过';
            $update_fields['manager_user'] = $current_user->user_login;
        } elseif (in_array('vice_president', $user_roles) && $enterprise->status === '总经理审核中') {
            $new_status = '董事长审核中';
            $update_fields['vice_president_status'] = '通过';
            $update_fields['vice_president_user'] = $current_user->user_login;
        } elseif (in_array('president', $user_roles) && $enterprise->status === '董事长审核中') {
            $new_status = '已通过';
            $update_fields['president_status'] = '通过';
            $update_fields['president_user'] = $current_user->user_login;
        } else {
            wp_send_json_error('您已通过审核，无需重复点击，请刷新页面');
        }
    } elseif ($action === 'reject') {
        // 处理 "驳回" 的逻辑
        $new_status = '已驳回';
        $update_fields['rejection_reason'] = $reason;  // 存储驳回原因
    }

    if (!empty($new_status)) {
        // 更新状态和其他字段到数据库
        $update_fields['status'] = $new_status;
        $wpdb->update($table_name, $update_fields, array('id' => $enterprise_id));
        
        // 返回成功的 JSON 响应
        wp_send_json_success(array('new_status' => $new_status));
    } else {
        wp_send_json_error('无法更新状态');
    }
}

add_action('wp_ajax_handle_enterprise_review_action', 'handle_enterprise_review_action');

?>
