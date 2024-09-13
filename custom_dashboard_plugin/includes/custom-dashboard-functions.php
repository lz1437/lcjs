<?php
// 显示企业用户中心
function render_enterprise_dashboard() {
    if (!is_user_logged_in() || !current_user_can('enterprise')) {
        // 使用 WordPress 的默认登录页面 URL
        wp_redirect(wp_login_url()); 
        exit;
    }

    $current_user = wp_get_current_user();
    global $wpdb;

    // 获取企业申请信息
    $application = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}enterprise_applications WHERE user_id = %d",
        $current_user->ID
    ));

    if (!$application) {
        echo "<h2>未找到您的企业入库申请信息。</h2>";
        return;
    }

    // 获取用户所属角色和用户名
    $user_roles = '企业用户';  // 将 enterprise 角色显示为 "企业用户"
    $user_name = esc_html($current_user->user_login);

    echo '<div class="enterprise-dashboard">';
    echo '<h1 class="dashboard-title">企业用户中心</h1>';

    echo '<div class="dashboard-info">';
    echo '<p><strong>用户组:</strong> ' . esc_html($user_roles) . '</p>';
    echo '<p><strong>用户名:</strong> ' . esc_html($user_name) . '</p>';
    echo '<p><strong>公司名称:</strong> ' . esc_html($application->company_name) . '</p>';
    echo '<p><strong>统一社会信用代码:</strong> ' . esc_html($application->credit_code) . '</p>';
    echo '<p><strong>法人代表:</strong> ' . esc_html($application->legal_representative) . '</p>';
    echo '<p><strong>身份证号:</strong> ' . esc_html($application->id_number) . '</p>';
    echo '<p><strong>注册地址:</strong> ' . esc_html($application->address) . '</p>';
    echo '<p><strong>注册资本:</strong> ' . esc_html($application->registered_capital) . '</p>';
    echo '<p><strong>成立日期:</strong> ' . esc_html($application->establishment_date) . '</p>';
    echo '<p><strong>联系方式:</strong> ' . esc_html($application->contact) . '</p>';
    echo '<p><strong>邮箱地址:</strong> ' . esc_html($current_user->user_email) . '</p>';
    echo '<p><strong>审核状态:</strong> ' . esc_html($application->status) . '</p>';
    echo '</div>'; // End dashboard-info

    // 显示公司业绩
    if (!empty($application->performance_file)) {
        echo '<div class="dashboard-files">';
        echo '<p><strong>公司业绩：</strong><a href="' . esc_url($application->performance_file) . '" target="_blank">查看公司业绩</a></p>';
        echo '</div>';
    }

    // 显示财务报表
    if (!empty($application->financial_report)) {
        echo '<div class="dashboard-files">';
        echo '<p><strong>财务报表：</strong><a href="' . esc_url($application->financial_report) . '" target="_blank">查看财务报表</a></p>';
        echo '</div>';
    }

    // 判断审核状态，未通过时允许修改信息
    if ($application->status !== '已通过') {
        echo '<button id="edit-info-button">修改信息</button>';
        render_edit_form($application);
    } else {
        echo "<p>审核通过，信息不可修改。</p>";
    }
    echo '</div>'; // End enterprise-dashboard

}

// 显示修改信息的表单
function render_edit_form($application) {
    ?>
    <form id="edit-form" method="POST" enctype="multipart/form-data" style="display:none;">
        <div class="form-row">
            <label for="password">新密码（留空则不修改）</label>
            <input type="password" name="password">
        </div>
        <div class="form-row">
            <label for="credit_code">信用代码</label>
            <input type="text" name="credit_code" value="<?php echo esc_attr($application->credit_code); ?>" required>
        </div>
        <div class="form-row">
            <label for="legal_representative">法人姓名</label>
            <input type="text" name="legal_representative" value="<?php echo esc_attr($application->legal_representative); ?>" required>
        </div>
        <div class="form-row">
            <label for="id_number">身份证号</label>
            <input type="text" name="id_number" value="<?php echo esc_attr($application->id_number); ?>" required>
        </div>
        <div class="form-row">
            <label for="address">注册地址</label>
            <input type="text" name="address" value="<?php echo esc_attr($application->address); ?>" required>
        </div>
        <div class="form-row">
            <label for="registered_capital">注册资本</label>
            <input type="text" name="registered_capital" value="<?php echo esc_attr($application->registered_capital); ?>" required>
        </div>
        <div class="form-row">
            <label for="establishment_date">成立日期</label>
            <input type="date" name="establishment_date" value="<?php echo esc_attr($application->establishment_date); ?>" required>
        </div>
        <div class="form-row">
            <label for="contact">联系方式</label>
            <input type="text" name="contact" value="<?php echo esc_attr($application->contact); ?>" required>
        </div>
        <div class="form-row">
            <label for="email">邮箱地址</label>
            <input type="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required>
        </div>
        <div class="form-row">
            <label for="performance">公司业绩</label>
            <input type="file" name="performance">
        </div>
        <div class="form-row">
            <label for="financial_report">财务报表</label>
            <input type="file" name="financial_report">
        </div>
        <div class="form-row">
            <input type="submit" name="update_info" value="确认修改">
        </div>
    </form>
    <?php
}
// 处理表单提交
function handle_user_info_update() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // 获取当前用户的角色
        $current_role = $current_user->roles ? $current_user->roles[0] : 'enterprise';

        // 处理密码更新
        if (!empty($_POST['password'])) {
            wp_set_password($_POST['password'], $user_id);
            // 获取你的自定义登录页面 URL，假设它是通过短代码 `[auth_form]` 生成的页面
            $custom_login_page = home_url('/%e6%99%ba%e6%85%a7%e5%b9%b3%e5%8f%b0');  // 替换为实际的登录页面地址
            wp_redirect($custom_login_page);  // 跳转到自定义登录页面
            exit;  // 停止执行
        }

        // 更新企业申请信息
        $wpdb->update(
            "{$wpdb->prefix}enterprise_applications",
            array(
                'credit_code' => sanitize_text_field($_POST['credit_code']),
                'legal_representative' => sanitize_text_field($_POST['legal_representative']),
                'id_number' => sanitize_text_field($_POST['id_number']),
                'address' => sanitize_text_field($_POST['address']),
                'registered_capital' => sanitize_text_field($_POST['registered_capital']),
                'establishment_date' => sanitize_text_field($_POST['establishment_date']),
                'contact' => sanitize_text_field($_POST['contact']),
            ),
            array('user_id' => $user_id)
        );

        // 更新邮箱地址
        wp_update_user(array('ID' => $user_id, 'user_email' => sanitize_email($_POST['email'])));

        // 处理文件上传逻辑
        if (!empty($_FILES['performance']['name'])) {
            $uploaded_performance = wp_handle_upload($_FILES['performance'], array('test_form' => false));
            if ($uploaded_performance && !isset($uploaded_performance['error'])) {
                $performance_url = $uploaded_performance['url'];
                $wpdb->update(
                    "{$wpdb->prefix}enterprise_applications",
                    array('performance_file' => $performance_url),
                    array('user_id' => $user_id)
                );
            }
        }

        if (!empty($_FILES['financial_report']['name'])) {
            $uploaded_financial_report = wp_handle_upload($_FILES['financial_report'], array('test_form' => false));
            if ($uploaded_financial_report && !isset($uploaded_financial_report['error'])) {
                $financial_report_url = $uploaded_financial_report['url'];
                $wpdb->update(
                    "{$wpdb->prefix}enterprise_applications",
                    array('financial_report' => $financial_report_url),
                    array('user_id' => $user_id)
                );
            }
        }

        // 保留用户角色，防止其被重置为“订阅者”
        $user = new WP_User($user_id);
        $user->set_role($current_role); // 确保用户角色保持不变

        // 显示信息更新成功提示，并保持在企业用户中心页面
        echo '<div class="notice notice-success">信息更新成功！</div>';

        // 刷新页面以显示更新后的信息
        wp_redirect(home_url('/enterprise-dashboard'));
        exit;
    }
}

add_action('init', 'handle_user_info_update');

