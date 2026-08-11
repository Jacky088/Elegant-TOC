<?php
/**
 * Elegant TOC 卸载清理
 *
 * 仅在用户于「插件」页面执行「删除」时由 WordPress 调用；
 * 停用（deactivate）不会触发此文件，用户数据在停用后仍保留。
 *
 * @package Elegant_TOC
 */

// 防止直接访问：必须由 WordPress 卸载流程触发
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * 清理单个站点的插件数据
 */
function elegant_toc_uninstall_site() {
    global $wpdb;

    // 1. 删除插件设置
    delete_option('elegant_toc_options');

    // 2. 删除所有文章上的「禁用目录」自定义字段
    //    注意：meta_key 为 'disable_toc'（无下划线前缀），与 save_post_meta_box() 保持一致
    delete_post_meta_by_key('disable_toc');

    // 3. 清理可能残留的 autoload 缓存
    wp_cache_delete('elegant_toc_options', 'options');
}

if (is_multisite()) {
    global $wpdb;

    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

    if (!empty($blog_ids)) {
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            elegant_toc_uninstall_site();
            restore_current_blog();
        }
    }
} else {
    elegant_toc_uninstall_site();
}
