<?php
/**
 * Admin functionality
 *
 * @package ElegantTOC
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 */
class Elegant_TOC_Admin {
    private $main;

    public function __construct($main) {
        $this->main = $main;
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('add_meta_boxes', array($this, 'add_post_meta_box'));
        add_action('save_post', array($this, 'save_post_meta_box'));
        add_filter('plugin_action_links_' . plugin_basename($this->main->get_plugin_file()), array($this, 'add_plugin_action_links'));
    }

    public function add_admin_menu() {
        add_options_page(
            'Elegant TOC 设置',
            'Elegant TOC',
            'manage_options',
            'elegant-toc',
            array($this, 'render_settings_page')
        );
    }

    public function add_plugin_action_links($links) {
        if (!current_user_can('manage_options')) {
            return $links;
        }

        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=elegant-toc')) . '">' . __('设置', 'elegant-toc') . '</a>';

        $ordered = array();
        foreach ($links as $key => $value) {
            $ordered[$key] = $value;
            if ($key === 'deactivate') {
                $ordered['settings'] = $settings_link;
            }
        }

        if (!isset($ordered['settings'])) {
            $ordered['settings'] = $settings_link;
        }

        return $ordered;
    }

    public function register_settings() {
        register_setting(
            'elegant_toc_options',
            'elegant_toc_options',
            array($this, 'sanitize_options')
        );
    }

    public function sanitize_options($input) {
        $clean = array();
        $clean['enabled'] = !empty($input['enabled']);
        $clean['min_headings'] = max(1, min(10, intval(isset($input['min_headings']) ? $input['min_headings'] : 3)));

        $allowed_levels = array('h2', 'h3', 'h4', 'h5', 'h6');
        $clean['heading_levels'] = array();
        if (!empty($input['heading_levels']) && is_array($input['heading_levels'])) {
            foreach ($input['heading_levels'] as $level) {
                $level = sanitize_text_field($level);
                if (in_array($level, $allowed_levels, true)) {
                    $clean['heading_levels'][] = $level;
                }
            }
        }
        if (empty($clean['heading_levels'])) {
            $clean['heading_levels'] = array('h2');
        }

        $allowed_themes = $this->main->get_allowed_color_themes();
        $clean['color_theme'] = 'light';
        if (!empty($input['color_theme'])) {
            $theme = sanitize_text_field($input['color_theme']);
            if (in_array($theme, $allowed_themes, true)) {
                $clean['color_theme'] = $theme;
            }
        }

        $clean['custom_css'] = '';
        if (!empty($input['custom_css'])) {
            $css = wp_strip_all_tags($input['custom_css']);
            $css = preg_replace('/javascript:/i', '', $css);
            $css = preg_replace('/expression\s*\(/i', '', $css);
            $css = preg_replace('/behaviour\s*:/i', '', $css);
            $css = preg_replace(/-moz-binding\s*:/i', '', $css);
            $clean['custom_css'] = sanitize_textarea_field($css);
        }

        $this->main->clear_options_cache();

        return $clean;
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_elegant-toc') {
            return;
        }
        $css_ver = $this->main->get_css_version();
        wp_enqueue_style('elegant-toc-admin', plugins_url('assets/admin.css', $this->main->get_plugin_file()), array(), $css_ver);
        wp_enqueue_style('elegant-toc', plugins_url('assets/style.css', $this->main->get_plugin_file()), array(), $css_ver);
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'elegant-toc'));
        }

        $options = $this->main->get_options();
        include plugin_dir_path($this->main->get_plugin_file()) . 'admin/settings-page.php';
    }

    public function add_post_meta_box() {
        $post_types = $this->main->get_supported_post_types();
        foreach ($post_types as $post_type) {
            add_meta_box(
                'elegant_toc_meta_box',
                __('Elegant TOC', 'elegant-toc'),
                array($this, 'render_post_meta_box'),
                $post_type,
                'side',
                'default'
            );
        }
    }

    public function render_post_meta_box($post) {
        wp_nonce_field('elegant_toc_meta_box', 'elegant_toc_meta_box_nonce');
        $disabled = $this->main->is_post_disabled($post->ID);
        ?>
        <p>
            <label>
                <input type="checkbox" name="disable_toc" value="1" <?php checked($disabled, true); ?> />
                <?php esc_html_e('在此文章/页面中禁用目录', 'elegant-toc'); ?>
            </label>
        </p>
        <p class="description">
            <?php esc_html_e('勾选后，本文将不会自动显示 Elegant TOC 目录。', 'elegant-toc'); ?>
        </p>
        <?php
    }

    public function save_post_meta_box($post_id) {
        if (!isset($_POST['elegant_toc_meta_box_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['elegant_toc_meta_box_nonce'], 'elegant_toc_meta_box')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['disable_toc']) && $_POST['disable_toc'] === '1') {
            update_post_meta($post_id, 'disable_toc', '1');
        } else {
            delete_post_meta($post_id, 'disable_toc');
        }
    }
}
