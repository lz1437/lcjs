<?php
function display_application_table($applications, $can_modify) {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th class="col-id">ID</th>';
    echo '<th class="col-company-name">公司名称</th>';
    echo '<th class="col-credit-code">统一社会信用代码</th>';
    echo '<th class="col-legal-representative">法人姓名</th>';
    echo '<th class="col-address">地址</th>';
    echo '<th class="col-registered-capital">注册资本</th>';
    echo '<th class="col-establishment-date">成立日期</th>';
    echo '<th>审核状态</th>';
    echo '<th>操作</th>';
    echo '</tr></thead><tbody>';

    // 遍历申请并显示
    foreach ($applications as $row) {
        echo '<tr>';
        echo '<td class="col-id">' . esc_html($row->id) . '</td>';
        echo '<td class="col-company-name">' . esc_html($row->company_name) . '</td>';
        echo '<td class="col-credit-code">' . esc_html($row->credit_code) . '</td>';
        echo '<td class="col-legal-representative">' . esc_html($row->legal_representative) . '</td>';
        echo '<td class="col-address">' . esc_html($row->address) . '</td>';
        echo '<td class="col-registered-capital">' . esc_html($row->registered_capital) . '</td>';
        echo '<td class="col-establishment-date">' . esc_html($row->establishment_date) . '</td>';

        // 格式化审核状态显示
        echo '<td class="status-column">';
        if (!empty($row->chief_engineer_user)) {
            echo '<span>技术总工: ' . esc_html($row->chief_engineer_user) . ' [通过]</span><br>';
        }
        if (!empty($row->manager_user)) {
            echo '<span>项目经理: ' . esc_html($row->manager_user) . ' [通过]</span><br>';
        }
        if (!empty($row->vice_president_user)) {
            echo '<span>总经理: ' . esc_html($row->vice_president_user) . ' [通过]</span><br>';
        }
        if (!empty($row->president_user)) {
            echo '<span>董事长: ' . esc_html($row->president_user) . ' [通过]</span><br>';
        }
        echo '<span style="color:red;">' . esc_html($row->status) . '</span>';
        echo '</td>';

        // 操作列
        echo '<td>';
        echo '<a href="?page=enterprise_details&id=' . esc_html($row->id) . '">查看</a>';
        if ($can_modify && $row->status !== '已通过') {
            echo ' | <a href="#" class="approve-button" data-id="' . esc_html($row->id) . '">通过</a>';
            echo ' | <a href="#" class="reject-button" data-id="' . esc_html($row->id) . '">驳回</a>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // 以下是调试代码：驳回模态窗口及通过按钮的 AJAX 请求
?>
<div id="reject-modal" style="display:none;">
    <h2>请输入驳回原因</h2>
    <textarea id="reject-reason" rows="4" cols="50"></textarea><br><br>
    <button id="submit-reject">提交</button>
    <button id="cancel-reject">取消</button>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var currentEnterpriseId = null;

        // 点击驳回按钮时显示模态框
        $('.reject-button').on('click', function(e) {
            e.preventDefault();
            currentEnterpriseId = $(this).data('id');
            $('#reject-modal').show(); // 显示模态窗口
        });

        // 取消驳回
        $('#cancel-reject').on('click', function() {
            $('#reject-modal').hide();
            $('#reject-reason').val(''); // 清空原因输入框
        });

        // 提交驳回原因
        $('#submit-reject').on('click', function() {
            var reason = $('#reject-reason').val();
            if (reason === '') {
                alert('请填写驳回原因');
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'handle_enterprise_review_action',
                    review_action: 'reject', // 标记为驳回操作
                    id: currentEnterpriseId,
                    reason: reason,          // 驳回原因
                    security: '<?php echo wp_create_nonce("enterprise-review-action"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // 更新前端显示内容，比如更新审核状态
                        $('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.status-column').text(response.new_status);
                        $('#reject-modal').hide(); // 隐藏模态框
                        $('#reject-reason').val(''); // 清空原因输入框
                    } else {
                        alert('操作失败：' + response.data);
                    }
                }
            });
        });

        // 点击通过按钮时处理 AJAX 请求
        $('.approve-button').on('click', function(e) {
            e.preventDefault();
            currentEnterpriseId = $(this).data('id');

            // 发送 AJAX 请求
            $.ajax({
                url: ajaxurl,  // WordPress 提供的全局 AJAX 处理 URL
                method: 'POST',
                data: {
                    action: 'handle_enterprise_review_action',
                    review_action: 'approve', // 标记为通过操作
                    id: currentEnterpriseId,
                    security: '<?php echo wp_create_nonce("enterprise-review-action"); ?>'
                },
                success: function(response) {
                    console.log(response);  // 查看完整的响应对象
                    if (response.success) {
                        // 不弹出提示，直接更新状态列
                        $('tr').find('[data-id="'+currentEnterpriseId+'"]').closest('tr').find('.status-column').text(response.new_status);
                    } else {
                        console.log('操作失败：', response.data);  // 如果操作失败，调试错误信息
                        alert('操作失败：' + response.data);
                    }
                },
                error: function() {
                    console.log('AJAX 请求失败');
                }
            });
        });
    });
</script>
<?php

}
?>