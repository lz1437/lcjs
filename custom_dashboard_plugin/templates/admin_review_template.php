<?php
// 后台管理页面显示函数
function custom_dashboard_plugin_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';

    // 统计企业总数
    $total_companies = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // 统计审核中的企业数量
    $in_progress_companies = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status IN ('技术总工审核中', '项目经理审核中', '总经理审核中', '董事长审核中')");

    // 统计已通过的企业数量
    $approved_companies = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = '已通过'");

    // 统计驳回的企业数量
    $rejected_companies = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = '已驳回'");

    echo '<div class="wrap">';

    // 添加主标题和说明
    echo '<div class="admin-dashboard-header">';
    echo '<h1 class="admin-dashboard-title">企业入库管理</h1>';
    echo '<p class="admin-dashboard-description">欢迎使用企业入库管理系统。在此页面中，您可以查看并管理企业的入库申请。</p>';
    echo '</div>';

    // 添加一些统计或快捷按钮
    echo '<div class="admin-dashboard-actions">';
    
    // 企业总数
    echo '<div class="admin-dashboard-stat-box">';
    echo '<h2>企业总数</h2>';
    echo '<p>' . esc_html($total_companies) . '</p>';  // 动态显示企业总数
    echo '</div>';

    // 审核中企业
    echo '<div class="admin-dashboard-stat-box">';
    echo '<h2>审核中企业</h2>';
    echo '<p>' . esc_html($in_progress_companies) . '</p>';  // 动态显示审核中企业数量
    echo '</div>';

    // 已通过企业
    echo '<div class="admin-dashboard-stat-box">';
    echo '<h2>已通过企业</h2>';
    echo '<p>' . esc_html($approved_companies) . '</p>';  // 动态显示已通过企业数量
    echo '</div>';

    // 已驳回企业
    echo '<div class="admin-dashboard-stat-box">';
    echo '<h2>已驳回企业</h2>';
    echo '<p>' . esc_html($rejected_companies) . '</p>';  // 动态显示驳回企业数量
    echo '</div>';
    
    echo '</div>';  // 结束 admin-dashboard-actions

    // 可添加管理按钮
    echo '<div class="admin-dashboard-buttons">';
    echo '<a href="?page=enterprise_in_progress_reviews" class="button button-primary">查看审核中企业</a>';
    echo '<a href="?page=enterprise_approved_reviews" class="button">查看已通过企业</a>';
    echo '<a href="?page=enterprise_rejected_reviews" class="button">查看驳回企业</a>';
    echo '</div>';

    echo '</div>';  // 结束 wrap
}


// 后台管理菜单
function custom_dashboard_plugin_admin_menu() {
    if (current_user_can('manage_options') || current_user_can('manager') || current_user_can('chief_engineer') || current_user_can('staff')) {
        add_menu_page(
            '企业入库管理',     // 页面标题
            '企业入库',          // 菜单标题
            'read',              // 权限，设为 'read' 使更多角色能访问
            'enterprise_applications',  // 菜单别名
            'custom_dashboard_plugin_admin_page',  // 显示页面的函数
            'dashicons-businessman',    // 图标
            6  // 菜单位置
        );

        // 审核中企业
        add_submenu_page(
            'enterprise_applications',
            '审核中企业',
            '审核中企业',
            'read',
            'enterprise_in_progress_reviews',
            'display_in_progress_reviews' // 定义审核中申请的页面显示逻辑
        );

        // 已通过企业
        add_submenu_page(
            'enterprise_applications',
            '已通过企业',
            '已通过企业',
            'read',
            'enterprise_approved_reviews',
            'display_approved_reviews' // 定义已通过申请的页面显示逻辑
        );

        // 驳回的企业
        add_submenu_page(
            'enterprise_applications',
            '驳回的企业',
            '驳回的企业',
            'read',
            'enterprise_rejected_reviews',
            'display_rejected_reviews' // 定义驳回申请的页面显示逻辑
        );
        
            // 添加企业详情的子菜单
         add_submenu_page(
            null, // 不在菜单中显示这个页面
            '企业详情',
            '企业详情',
            'read',
            'enterprise_details',
            'display_enterprise_details' // 显示企业详情页面的函数
        );
    }
}
add_action('admin_menu', 'custom_dashboard_plugin_admin_menu');

// 后台管理页面的逻辑
function display_pending_reviews() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';
    $pending_reviews = $wpdb->get_results("SELECT * FROM $table_name WHERE status = '未审核'");

    echo '<h2>未审核企业</h2>';
    if (!empty($pending_reviews)) {
        display_application_table($pending_reviews, true);
    } else {
        echo '<p>目前没有未审核的企业。</p>';
    }
}

// 显示审核中申请
function display_in_progress_reviews() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';

    // 查询所有审核中的申请
    $in_progress_reviews = $wpdb->get_results("
        SELECT * FROM $table_name 
        WHERE status IN ('技术总工审核中', '项目经理审核中', '总经理审核中', '董事长审核中')
    ");

    echo '<h2>审核中企业</h2>';
    if (!empty($in_progress_reviews)) {
        display_application_table($in_progress_reviews, true);
    } else {
        echo '<p>目前没有正在审核的企业。</p>';
    }
}

// 显示已通过申请
function display_approved_reviews() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';
    $approved_reviews = $wpdb->get_results("SELECT * FROM $table_name WHERE status = '已通过'");

    echo '<h2>已通过企业</h2>';
    if (!empty($approved_reviews)) {
        display_application_table($approved_reviews, false); // 已通过申请无修改权限
    } else {
        echo '<p>目前没有已通过的企业。</p>';
    }
}

// 显示驳回的申请
function display_rejected_reviews() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';
    $rejected_reviews = $wpdb->get_results("SELECT * FROM $table_name WHERE status = '已驳回'");

    echo '<h2>驳回的申请</h2>';
    if (!empty($rejected_reviews)) {
        display_application_table($rejected_reviews, false); // 驳回的申请无修改权限
    } else {
        echo '<p>目前没有驳回的企业。</p>';
    }
}
// 显示企业详情的函数
function display_enterprise_details() {
    if (!isset($_GET['id'])) {
        echo '<p>企业ID无效。</p>';
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';
    $enterprise_id = intval($_GET['id']);
    $enterprise = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $enterprise_id));

    if ($enterprise) {
        // 获取注册用户的信息
        $user_info = get_userdata($enterprise->user_id);

        // 显示企业详细信息
        echo '<h2>企业详情</h2>';
        echo '<p><strong>公司名称：</strong>' . esc_html($enterprise->company_name) . '</p>';
        echo '<p><strong>统一社会信用代码：</strong>' . esc_html($enterprise->credit_code) . '</p>';
        echo '<p><strong>法人姓名：</strong>' . esc_html($enterprise->legal_representative) . '</p>';
        echo '<p><strong>地址：</strong>' . esc_html($enterprise->address) . '</p>';
        echo '<p><strong>注册资本：</strong>' . esc_html($enterprise->registered_capital) . '</p>';
        echo '<p><strong>成立日期：</strong>' . esc_html($enterprise->establishment_date) . '</p>';
        echo '<p><strong>审核状态：</strong>' . esc_html($enterprise->status) . '</p>';

        // 显示用户的注册信息
        if ($user_info) {
            echo '<p><strong>用户名：</strong>' . esc_html($user_info->user_login) . '</p>';
            echo '<p><strong>用户邮箱：</strong>' . esc_html($user_info->user_email) . '</p>';
            echo '<p><strong>联系方式：</strong>' . esc_html($enterprise->contact) . '</p>';
        }

        // 显示公司资质和业绩文件（如果存在）
        if (!empty($enterprise->performance)) {
            echo '<p><strong>公司业绩：</strong><a href="' . esc_url($enterprise->performance) . '" target="_blank">查看公司业绩</a></p>';
        }
        if (!empty($enterprise->financial_report)) {
            echo '<p><strong>财务报表：</strong><a href="' . esc_url($enterprise->financial_report) . '" target="_blank">查看财务报表</a></p>';
        }

        // 如果存在驳回原因，则显示
        if (!empty($enterprise->rejection_reason)) {
            echo "<p><strong>驳回原因：</strong> {$enterprise->rejection_reason}</p>";
        }

        // 显示删除按钮
        echo '<form method="post">';
        echo '<input type="hidden" name="enterprise_id" value="' . esc_attr($enterprise_id) . '">';
        echo '<input type="submit" name="delete_enterprise" value="删除企业" class="button button-danger">';
        echo '</form>';

        // 处理删除操作
        if (isset($_POST['delete_enterprise'])) {
            // 删除企业记录
            $wpdb->delete($table_name, array('id' => $enterprise_id));

            // 删除用户账户
            wp_delete_user($enterprise->user_id);

            // 删除企业上传的文件（假设文件路径存储在某个字段中）
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . '/enterprise_files/' . $enterprise_id;
            if (is_dir($file_path)) {
                array_map('unlink', glob("$file_path/*.*")); // 删除文件
                rmdir($file_path); // 删除目录
            }

            // 显示删除成功消息
            echo '<p>企业及其用户信息已成功删除。</p>';
        }
    } else {
        echo '<p>未找到该企业的详细信息。</p>';
    }
}
?>