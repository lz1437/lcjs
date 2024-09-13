<?php
// 插件激活时创建用户角色
function custom_add_roles_on_plugin_activation() {
    add_role('enterprise', '企业用户', array('read' => true));
    add_role('admin', '管理员', array('read' => true, 'edit_posts' => true, 'delete_posts' => true, 'manage_options' => true));
    add_role('president', '董事长', array('read' => true, 'manage_options' => true));
    add_role('vice_president', '总经理', array('read' => true, 'manage_options' => true));
    add_role('manager', '项目经理', array('read' => true, 'edit_posts' => true));
    add_role('chief_engineer', '技术总工', array('read' => true, 'edit_posts' => true));
    add_role('staff', '正式职员', array('read' => true));

    custom_create_application_table(); // 创建或更新企业入库申请表
}
register_activation_hook(__FILE__, 'custom_add_roles_on_plugin_activation');

// 创建/更新企业入库申请表，添加所有审核状态字段
function custom_create_application_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enterprise_applications';
    $charset_collate = $wpdb->get_charset_collate();

    // 创建或更新表的SQL语句
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        company_name varchar(255) NOT NULL,
        credit_code varchar(20) NOT NULL,
        legal_representative varchar(255) NOT NULL,
        id_number varchar(20) NOT NULL,
        address varchar(255) NOT NULL,
        registered_capital varchar(100) NOT NULL,
        establishment_date date NOT NULL,
        performance text NOT NULL,
        financial_report varchar(255) NOT NULL,
        contact varchar(20) NOT NULL,
        status varchar(50) DEFAULT '未审核',
        rejection_reason text DEFAULT '',
        chief_engineer_status varchar(255) DEFAULT NULL,
        chief_engineer_user varchar(100) DEFAULT NULL, /* 技术总工用户名 */
        manager_status varchar(255) DEFAULT NULL,  
        manager_user varchar(100) DEFAULT NULL,  /* 项目经理用户名 */
        vice_president_status varchar(255) DEFAULT NULL,
        vice_president_user varchar(100) DEFAULT NULL,  /* 总经理用户名 */
        president_status varchar(255) DEFAULT NULL,
        president_user varchar(100) DEFAULT NULL,  /* 董事长用户名 */
        submission_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // 执行表结构更新
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>
