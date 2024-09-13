<?php
// 这里可以添加与插件相关的其他钩子或功能
add_shortcode('auth_form', 'render_auth_form');

// 确保管理员登录后重定向到 wp-admin 后台
add_filter('login_redirect', 'custom_admin_login_redirect', 10, 3);
function custom_admin_login_redirect($redirect_to, $request, $user) {
    if (is_wp_error($user)) {
        return $redirect_to; // 如果用户对象是错误对象，返回默认跳转
    }

    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return admin_url(); // 管理员跳转到后台
        }
        if (in_array('enterprise', $user->roles)) {
            return home_url('/enterprise-dashboard'); // 企业用户跳转到企业后台
        }
    }
    
    return $redirect_to; // 默认跳转
}

function custom_enqueue_scripts() {
    wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'assets/js/custom.js', array('jquery'), null, true);

    // 使用 wp_localize_script 传递 nonce 和 ajaxurl 给 JS 文件
    wp_localize_script('custom-js', 'custom_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('enterprise-review-action')
    ));
}
add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');

// 注册企业用户中心的短代码
function enterprise_dashboard_shortcode() {
    ob_start(); // 启用输出缓冲
    render_enterprise_dashboard(); // 调用企业用户中心的渲染函数
    return ob_get_clean(); // 返回缓冲区内容
}
add_shortcode('enterprise_dashboard', 'enterprise_dashboard_shortcode');


?>
