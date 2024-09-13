<?php
if ($_FILES) {
    // 确保 wp_handle_upload() 函数已加载
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $performance = $_FILES['performance'];
    $financial_report = $_FILES['financial_report'];

    // 允许的文件类型
    $allowed_types = array('application/pdf');
    
    // 检查并处理文件上传逻辑...
}

// 处理自定义登录表单的函数
function handle_user_auth() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 检查是否提交了登录请求
        if (isset($_POST['login'])) {
            // 清理用户名和密码输入
            $username = sanitize_text_field($_POST['username']);
            $password = sanitize_text_field($_POST['password']);

            // 凭据数组
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true
            );

            // 使用 wp_signon() 登录用户
            $user = wp_signon($creds, false);

            // 检查登录是否成功
            if (is_wp_error($user)) {
                // 登录失败，输出错误信息
                echo '登录失败：' . $user->get_error_message();
                return;
            } else {
                // 登录成功后，判断用户角色并重定向
                if (in_array('administrator', $user->roles)) {
                    // 如果用户是管理员，重定向到 WordPress 后台
                    wp_redirect(admin_url());
                } else {
                    // 如果是企业用户，重定向到自定义企业后台
                    wp_redirect(home_url('/enterprise-dashboard'));
                }
                exit; // 停止脚本执行，确保不会继续加载页面
            }
        }

        // 处理注册信息
        if (isset($_POST['register'])) {
            $username = sanitize_text_field($_POST['username']);
            $password = sanitize_text_field($_POST['password']);
            $password_repeat = sanitize_text_field($_POST['password_repeat']);
            $email = sanitize_email($_POST['email']); // 获取邮箱地址

            // 验证用户名是否存在
            if (username_exists($username)) {
                echo '用户名已存在，请选择另一个用户名。';
                return;
            }

            // 验证密码是否一致
            if ($password !== $password_repeat) {
                echo '两次输入的密码不一致，请重新输入。';
                return;
            }

            // 创建用户，使用填写的邮箱地址
            $user_id = wp_create_user($username, $password, $email); // 这里使用 $_POST['email']
            if (is_wp_error($user_id)) {
                echo '注册失败：' . $user_id->get_error_message();
                return;
            }
            
            // 为用户分配 'enterprise' 角色
            $user = new WP_User($user_id);
            $user->set_role('enterprise');
            
            // 处理文件上传逻辑
            if ($_FILES) {
                $performance = $_FILES['performance'];
                $financial_report = $_FILES['financial_report'];
                // 允许的文件类型
                $allowed_types = array('application/pdf');
                // 检查业绩文件是否是PDF
                if (in_array($performance['type'], $allowed_types)) {
                    $uploaded_performance = wp_handle_upload($performance, array('test_form' => false));
                    if ($uploaded_performance && !isset($uploaded_performance['error'])) {
                        $performance_url = $uploaded_performance['url'];
                    } else {
                        echo '业绩文件上传失败: ' . $uploaded_performance['error'];
                        return;
                    }
                } else {
                    echo '业绩文件必须为PDF格式。';
                    return;
                }
                // 检查财务报表是否是PDF
                if (in_array($financial_report['type'], $allowed_types)) {
                    $uploaded_financial_report = wp_handle_upload($financial_report, array('test_form' => false));
                    if ($uploaded_financial_report && !isset($uploaded_financial_report['error'])) {
                        $financial_report_url = $uploaded_financial_report['url'];
                    } else {
                        echo '财务报表上传失败: ' . $uploaded_financial_report['error'];
                        return;
                    }
                } else {
                    echo '财务报表必须为PDF格式。';
                    return;
                }

                $max_file_size = 2 * 1024 * 1024; // 5MB 文件大小限制
                if ($performance['size'] > $max_file_size) {
                    echo '业绩文件大小不能超过2MB。';
                    return;
                }

                if ($financial_report['size'] > $max_file_size) {
                    echo '财务报表文件大小不能超过2MB。';
                    return;
                }
            }
            
            // 将上传的文件URL保存到数据库
            global $wpdb;
            $table_name = $wpdb->prefix . 'enterprise_applications';
            $wpdb->insert($table_name, array(
                'user_id' => $user_id,
                'company_name' => sanitize_text_field($_POST['company_name']),
                'credit_code' => sanitize_text_field($_POST['credit_code']),
                'legal_representative' => sanitize_text_field($_POST['legal_representative']),
                'id_number' => sanitize_text_field($_POST['id_number']),
                'address' => sanitize_text_field($_POST['address']),
                'registered_capital' => sanitize_text_field($_POST['registered_capital']),
                'establishment_date' => sanitize_text_field($_POST['establishment_date']),
                'performance' => $performance_url,  // 保存上传的业绩文件URL
                'financial_report' => $financial_report_url,  // 保存上传的财务报表URL
                'contact' => sanitize_text_field($_POST['contact']),
                'status' => '技术总工审核中'
            ));
        
            // 自动登录用户
            $creds = array(
                'user_login' => $username,
                'user_password' => $password,
                'remember' => true
            );
        
            $logged_in_user = wp_signon($creds, false);
            if (is_wp_error($logged_in_user)) {
                echo '自动登录失败：' . $logged_in_user->get_error_message();
                return;
            }
        
            // 注册成功后跳转
            wp_redirect(home_url('/enterprise-dashboard'));
            exit;
        }
    }
}
add_action('init', 'handle_user_auth');

// 显示注册和登录表单
function render_auth_form() {
    ob_start();
    ?>
    <div class="auth-container">

        <!-- 登录表单 -->
        <div id="login-form">
            <form method="POST">
                <div class="form-row">
                    <label for="username">用户名</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-row">
                    <label for="password">密码</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-row-button">
                    <input type="submit" name="login" value="登录" class="submit-button">
                </div>
                <p class="register-prompt">
                    还没有账号？<a href="#" id="show-register-link">点击注册</a>。
                </p>
            </form>
        </div>

        <!-- 注册表单 -->
        <div id="register-form" style="display: none;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <label for="username">用 户 名:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-row">
                    <label for="password">密    码:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-row">
                    <label for="password_repeat">确认密码:</label>
                    <input type="password" name="password_repeat" required>
                </div>
                <!-- 企业信息 -->
                <div class="form-row">
                    <label for="company_name">公司名称:</label>
                    <input type="text" name="company_name" required>
                </div>
                <div class="form-row">
                    <label for="credit_code">信用代码:</label>
                    <input type="text" name="credit_code" required>
                </div>
                <div class="form-row">
                    <label for="legal_representative">法人姓名:</label>
                    <input type="text" name="legal_representative" required>
                </div>
                <div class="form-row">
                    <label for="id_number">身份证号:</label>
                    <input type="text" name="id_number" required>
                </div>
                <div class="form-row">
                    <label for="address">注册地址:</label>
                    <input type="text" name="address" required>
                </div>
                <div class="form-row">
                    <label for="registered_capital">注册资本:</label>
                    <input type="text" name="registered_capital" required>
                </div>
                <div class="form-row">
                    <label for="contact">联系方式:</label>
                    <input type="text" name="contact" required>
                </div>
                <div class="form-row">
                    <label for="email">邮箱地址:</label> <!-- 新增邮箱字段 -->
                    <input type="email" name="email" required>
                </div>
                <div class="form-row">
                    <label for="establishment_date">成立日期:</label>
                    <input type="date" name="establishment_date" required>
                </div>
                <div class="form-row">
                    <label for="performance">公司业绩:</label>
                    <input type="file" name="performance" required>
                </div>
                <div class="form-row">
                    <label for="financial_report">财务报表:</label>
                    <input type="file" name="financial_report" required>
                </div>
                <div class="form-row-button">
                    <input type="submit" name="register" value="注册" class="submit-button">
                </div>    
            </form>
            <div id="progress-status"></div> <!-- 显示上传进度和结果 -->
        </div>
    </div>

    <script>
        document.getElementById('show-login').addEventListener('click', function() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
        });

        document.getElementById('show-register').addEventListener('click', function() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        });
    </script>
    <?php
    return ob_get_clean();
}
?>
