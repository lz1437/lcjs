<?php
/*
Plugin Name: Custom Dashboard Plugin
Plugin URI: https://github.com/lz1437/lcjs
Description: 企业入库管理插件
Version: 2.0.0
Author: 南风未起
Author URI: https://lcjs.net.cn/
GitHub Plugin URI: lz1437/lcjs
*/

// 引入功能模块
include_once plugin_dir_path(__FILE__) . 'includes/custom-dashboard-hooks.php';
include_once plugin_dir_path(__FILE__) . 'includes/custom-dashboard-styles.php';
include_once plugin_dir_path(__FILE__) . 'includes/custom-dashboard-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/custom-dashboard-auth.php';
include_once plugin_dir_path(__FILE__) . 'includes/custom-dashboard-backend.php';
include_once plugin_dir_path(__FILE__) . 'templates/admin_review_template.php';
include_once plugin_dir_path(__FILE__) . 'templates/functions.php';
include_once plugin_dir_path(__FILE__) . 'templates/admin-ajax.php';
include_once plugin_dir_path(__FILE__) . 'templates/check_for_plugin_update.php';
// 插件初始化
function custom_dashboard_plugin_init() {
    // 插件初始化代码可以在这里处理
}
add_action('init', 'custom_dashboard_plugin_init');
?>
