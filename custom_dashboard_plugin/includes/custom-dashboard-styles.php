<?php
// 注册前台和后台的样式和脚本
function custom_dashboard_plugin_enqueue_scripts() {
    // 前台样式和脚本
    $frontend_css = plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin-dashboard.css';  // 前台样式
    $frontend_js = plugin_dir_url(dirname(__FILE__)) . 'assets/js/script.js';  // 前台脚本
    
    // 后台样式和脚本
    $backend_css = plugin_dir_url(dirname(__FILE__)) . 'assets/css/custom-dashboard-styles.css';  // 后台样式
    $backend_js = plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-script.js';  // 后台脚本

    // 判断是否是后台页面
    if (is_admin()) {
        // 后台页面加载
        wp_enqueue_style('custom-dashboard-style-backend', $backend_css);
        wp_enqueue_script('custom-dashboard-script-backend', $backend_js, array('jquery'), false, true);
    } else {
        // 前台页面加载
        wp_enqueue_style('custom-dashboard-style-frontend', $frontend_css);
        wp_enqueue_script('custom-dashboard-script-frontend', $frontend_js, array('jquery'), false, true);
    }
}

// 使用两个钩子分别在前台和后台加载资源
add_action('wp_enqueue_scripts', 'custom_dashboard_plugin_enqueue_scripts');  // 前台资源加载
add_action('admin_enqueue_scripts', 'custom_dashboard_plugin_enqueue_scripts');  // 后台资源加载

?>
