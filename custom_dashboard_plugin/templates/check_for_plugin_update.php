<?php
function check_for_plugin_update( $transient ) {
    // GitHub 仓库 API URL
    $api_url = 'https://api.github.com/repos/your-username/your-repository/releases/latest';
    
    // 获取 GitHub 最新版本信息
    $response = wp_remote_get( $api_url );
    
    if ( is_wp_error( $response ) ) {
        return $transient;
    }

    $release_info = json_decode( wp_remote_retrieve_body( $response ) );

    // 获取 GitHub 中的最新版本号
    $new_version = $release_info->tag_name;

    // 检查插件版本是否需要更新
    if ( version_compare( $new_version, PLUGIN_VERSION, '>' ) ) {
        // 插件更新信息
        $transient->response['your-plugin/your-plugin.php'] = array(
            'new_version' => $new_version,
            'package'     => $release_info->zipball_url,
            'slug'        => 'your-plugin',
            'url'         => 'https://github.com/your-username/your-repository',
        );
    }

    return $transient;
}
add_filter( 'site_transient_update_plugins', 'check_for_plugin_update' );
?>
