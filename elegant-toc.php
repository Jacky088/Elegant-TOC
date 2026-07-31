<?php
/**
 * Plugin Name: Elegant TOC
 * Plugin URI: https://github.com/Jacky088/Elegant-TOC
 * Description: 优雅的文章目录插件，自动生成美观的文章目录，支持平滑滚动和高亮显示
 * Version: 1.7.0
 * Author: 木木
 * Author URI: https://github.com/Jacky088/Elegant-TOC
 * License: GPL v2 or later
 * Text Domain: elegant-toc
 * Requires PHP: 7.4
 * Requires at least: 5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elegant_TOC {
    private static $instance = null;
    const VERSION = '1.7.0';

    /** 缓存的资源版本号（含 filemtime） */
    private $css_ver = '';
    private $js_ver  = '';

    /** insert_toc 是否已执行 */
    public $toc_inserted = false;

    /** 选项缓存 */
    private $options_cache = null;

    /** 插件文件路径 */
    private $plugin_file = '';

    /** 前端和后端实例 */
    private $frontend = null;
    private $admin = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_file = __FILE__;

        // 预计算资源版本号（避免多次 filemtime 调用）
        $css_path = plugin_dir_path(__FILE__) . 'assets/style.css';
        $js_path  = plugin_dir_path(__FILE__) . 'assets/script.js';
        $this->css_ver = self::VERSION . '.' . (file_exists($css_path) ? filemtime($css_path) : time());
        $this->js_ver  = self::VERSION . '.' . (file_exists($js_path) ? filemtime($js_path) : time());

        // 国际化
        add_action('init', array($this, 'load_textdomain'));

        // 短代码（前台与编辑器均需注册）
        add_shortcode('elegant_toc', array($this, 'render_shortcode'));

        // 加载模块
        $this->load_modules();

        if (is_admin()) {
            if ($this->admin) {
                $this->admin->init();
            }
            return;
        }

        // 前台
        if ($this->frontend) {
            $this->frontend->init();
        }
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_assets'), 99);
    }

    /**
     * 加载模块化类文件
     */
    private function load_modules() {
        $includes_dir = plugin_dir_path(__FILE__) . 'includes/';

        if (file_exists($includes_dir . 'class-frontend.php')) {
            require_once $includes_dir . 'class-frontend.php';
            $this->frontend = new Elegant_TOC_Frontend($this);
        }

        if (file_exists($includes_dir . 'class-admin.php')) {
            require_once $includes_dir . 'class-admin.php';
            $this->admin = new Elegant_TOC_Admin($this);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  国际化                                                              */
    /* ------------------------------------------------------------------ */

    public function load_textdomain() {
        load_plugin_textdomain('elegant-toc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /* ------------------------------------------------------------------ */
    /*  资源加载（统一入口，按需加载）                                         */
    /* ------------------------------------------------------------------ */

    /**
     * 在 wp_enqueue_scripts 阶段预判是否需要加载资源。
     * 因为此时 the_content 尚未执行，用 should_load_on_page() 预判。
     */
    public function maybe_enqueue_assets() {
        if (!$this->should_load_on_page()) {
            return;
        }
        $this->enqueue_assets();
    }

    /**
     * 统一的资源加载方法（CSS + JS）
     * 生产环境自动使用压缩版本
     */
    public function enqueue_assets() {
        if (wp_style_is('elegant-toc', 'enqueued')) {
            return;
        }

        $use_minified = !defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG;
        $css_file = $use_minified ? 'assets/style.min.css' : 'assets/style.css';
        $js_file = $use_minified ? 'assets/script.min.js' : 'assets/script.js';

        wp_enqueue_style('elegant-toc', plugins_url($css_file, __FILE__), array(), $this->css_ver);
        wp_enqueue_script('elegant-toc', plugins_url($js_file, __FILE__), array(), $this->js_ver, true);

        // 输出自定义 CSS
        $options = $this->get_options();
        if (!empty($options['custom_css'])) {
            wp_add_inline_style('elegant-toc', wp_strip_all_tags($options['custom_css']));
        }
    }

    /* ------------------------------------------------------------------ */
    /*  公共方法（供子类访问）                                               */
    /* ------------------------------------------------------------------ */

    public function get_plugin_file() {
        return $this->plugin_file;
    }

    public function get_css_version() {
        return $this->css_ver;
    }

    public function get_js_version() {
        return $this->js_ver;
    }

    public function clear_options_cache() {
        $this->options_cache = null;
    }

    public function get_supported_post_types() {
        return (array) apply_filters('elegant_toc_post_types', array('post', 'page'));
    }

    public function is_post_disabled($post_id) {
        return get_post_meta($post_id, 'disable_toc', true) === '1';
    }

    public function get_allowed_color_themes() {
        return array('auto', 'light', 'blue', 'green', 'purple', 'orange', 'dark');
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_elegant-toc') {
            return;
        }
        wp_enqueue_style('elegant-toc-admin', plugins_url('assets/admin.css', __FILE__), array(), $this->css_ver);
        wp_enqueue_style('elegant-toc', plugins_url('assets/style.css', __FILE__), array(), $this->css_ver);
    }

    public function get_options() {
        // 使用缓存避免多次数据库查询
        if ($this->options_cache !== null) {
            return $this->options_cache;
        }

        $options = get_option('elegant_toc_options');
        if (!is_array($options)) {
            $options = array();
        }

        $this->options_cache = array(
            'enabled'        => array_key_exists('enabled', $options) ? (bool) $options['enabled'] : true,
            'min_headings'   => isset($options['min_headings']) ? max(1, intval($options['min_headings'])) : 3,
            'heading_levels' => (!empty($options['heading_levels']) && is_array($options['heading_levels']))
                ? $options['heading_levels']
                : array('h2', 'h3', 'h4'),
            'color_theme'    => (!empty($options['color_theme']) && is_string($options['color_theme']))
                ? $options['color_theme']
                : 'light',
            'custom_css'     => (!empty($options['custom_css']) && is_string($options['custom_css']))
                ? $options['custom_css']
                : '',
        );

        return $this->options_cache;
    }

    private function should_load_on_page() {
        if (!is_singular() || is_front_page() || is_feed()) {
            return false;
        }

        $post = get_post();
        if (!$post) {
            return false;
        }

        // 显式包含短代码的文章
        if (has_shortcode($post->post_content, 'elegant_toc')) {
            return true;
        }

        $options = $this->get_options();
        if (empty($options['enabled'])) {
            return false;
        }

        // 单篇禁用
        if ($this->is_post_disabled($post->ID)) {
            return false;
        }

        // 仅自动应用到指定文章类型
        $post_types = $this->get_supported_post_types();
        if (!in_array($post->post_type, $post_types, true)) {
            return false;
        }

        return true;
    }

    public function get_supported_post_types() {
        return (array) apply_filters('elegant_toc_post_types', array('post', 'page'));
    }

    public function is_post_disabled($post_id) {
        return get_post_meta($post_id, 'disable_toc', true) === '1';
    }

    /* ------------------------------------------------------------------ */
    /*  目录插入                                                             */
    /* ------------------------------------------------------------------ */

    public function insert_toc($content) {
        if (is_admin() || is_feed() || is_front_page() || !is_singular()) {
            return $content;
        }

        if ($this->toc_inserted) {
            return $content;
        }

        $post_id = get_the_ID();
        if ($this->is_post_disabled($post_id)) {
            return $content;
        }

        $options = $this->get_options();
        $auto_insert = !empty($options['enabled']);

        $post = get_post();
        $has_shortcode = $post && has_shortcode($post->post_content, 'elegant_toc');

        // 如果既没有启用自动插入也没有使用短代码，则直接返回
        if (!$auto_insert && !$has_shortcode) {
            return $content;
        }

        $result   = $this->extract_and_annotate_headings($content, $options['heading_levels']);
        $headings = $result['headings'];
        $content  = $result['content'];

        if (count($headings) < $options['min_headings']) {
            return $content;
        }

        $this->toc_inserted = true;

        // 确保资源已加载（部分主题 the_content 时 scripts 已跑完）
        $this->enqueue_assets();

        $toc = $this->generate_toc($headings);

        // 如果内容中包含短代码，则替换短代码位置
        if ($has_shortcode) {
            return $this->replace_shortcode_once($content, $toc);
        }

        // 始终将目录置于文章内容顶部内联显示
        return $toc . $content;
    }

    /**
     * 短代码渲染（实际在 insert_toc 中替换为真实目录）
     * 直接调用时返回一个占位符，保证在 RSS/Feed 等场景也有合理输出。
     */
    public function render_shortcode($attrs = array(), $_content = '') {
        $attrs = shortcode_atts(array(
            'title' => __('文章目录', 'elegant-toc'),
        ), $attrs, 'elegant_toc');

        // 块状占位符，避免被 wpautop 包裹在 <p> 中
        return '<div class="elegant-toc-placeholder" aria-hidden="true"></div>';
    }

    /**
     * 将短代码占位符替换为真实目录（仅替换第一次出现）
     */
    private function replace_shortcode_once($content, $toc) {
        $placeholder = '<div class="elegant-toc-placeholder" aria-hidden="true"></div>';
        $pos = strpos($content, $placeholder);
        if ($pos === false) {
            // 兜底：若占位符被其他过滤器移除，则放到内容开头
            return $toc . $content;
        }
        return substr_replace($content, $toc, $pos, strlen($placeholder));
    }

    public function get_options() {
        // 使用缓存避免多次数据库查询
        if ($this->options_cache !== null) {
            return $this->options_cache;
        }

        $options = get_option('elegant_toc_options');
        if (!is_array($options)) {
            $options = array();
        }

        $this->options_cache = array(
            'enabled'        => array_key_exists('enabled', $options) ? (bool) $options['enabled'] : true,
            'min_headings'   => isset($options['min_headings']) ? max(1, intval($options['min_headings'])) : 3,
            'heading_levels' => (!empty($options['heading_levels']) && is_array($options['heading_levels']))
                ? $options['heading_levels']
                : array('h2', 'h3', 'h4'),
            'color_theme'    => (!empty($options['color_theme']) && is_string($options['color_theme']))
                ? $options['color_theme']
                : 'light',
            'custom_css'     => (!empty($options['custom_css']) && is_string($options['custom_css']))
                ? $options['custom_css']
                : '',
        );

        return $this->options_cache;
    }

    /**
     * 允许使用的色彩主题
     */
    public function get_allowed_color_themes() {
        return array('auto', 'light', 'blue', 'green', 'purple', 'orange', 'dark');
    }

    /**
     * 提取标题并写入唯一 id（与目录 href 一一对应）
     * 不再内联 scroll-margin-top，改由 CSS 类 .elegant-toc-heading 统一控制
     */
    private function extract_and_annotate_headings($content, $levels) {
        $headings = array();
        $used_ids = array();
        $pattern  = '/<(h[2-6])(\s[^>]*)?>(.*?)<\/\1>/is';

        $content = preg_replace_callback($pattern, function ($m) use ($levels, &$headings, &$used_ids) {
            $tag   = strtolower($m[1]);
            $attrs = isset($m[2]) ? $m[2] : '';
            $inner = $m[3];
            $text  = trim(wp_strip_all_tags($inner));

            if (!in_array($tag, $levels, true) || $text === '') {
                return $m[0];
            }

            $level = intval(substr($tag, 1));

            if (preg_match('/\sid=(["\'])([^"\']+)\1/i', $attrs, $im)) {
                $id = $im[2];
            } else {
                $id = $this->unique_id($text, $used_ids);
                $attrs = rtrim($attrs) . ' id="' . esc_attr($id) . '"';
            }

            // 添加 CSS 类用于 scroll-margin 控制（不再内联 style）
            if (strpos($attrs, 'elegant-toc-heading') === false) {
                $attrs .= ' class="elegant-toc-heading"';
            }

            $used_ids[$id] = true;
            $headings[] = array('level' => $level, 'text' => $text, 'id' => $id);

            return '<' . $tag . $attrs . '>' . $inner . '</' . $tag . '>';
        }, $content);

        return array('headings' => $headings, 'content' => $content);
    }

    private function unique_id($text, &$used) {
        $base = sanitize_title($text);
        if ($base === '') {
            $base = 'h-' . substr(md5($text), 0, 8);
        }
        $id = 'toc-' . $base;
        $n  = 2;
        while (isset($used[$id])) {
            $id = 'toc-' . $base . '-' . $n;
            $n++;
        }
        return $id;
    }

    /**
     * 生成目录 HTML — 极简结构，样式全部由 CSS 控制
     */
    private function generate_toc($headings) {
        $options = $this->get_options();
        $theme   = !empty($options['color_theme']) ? sanitize_text_field($options['color_theme']) : 'light';

        $toc  = '<!-- Elegant TOC v' . esc_html(self::VERSION) . ' -->';
        $toc .= '<nav class="elegant-toc" id="elegant-toc"';
        $toc .= ' data-et-ver="' . esc_attr(self::VERSION) . '"';
        if ('auto' !== $theme) {
            $toc .= ' data-et-theme="' . esc_attr($theme) . '"';
        }
        $toc .= ' aria-label="' . esc_attr__('文章目录', 'elegant-toc') . '">';

        // 移动端触发按钮（小屏时悬浮在左下角）
        $toc .= '<button type="button" class="elegant-toc-trigger" aria-label="' . esc_attr__('打开目录', 'elegant-toc') . '" title="' . esc_attr__('文章目录', 'elegant-toc') . '" data-tooltip="' . esc_attr__('文章目录', 'elegant-toc') . '" aria-expanded="false">';
        $toc .= '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;">';
        $toc .= '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
        $toc .= '</svg>';
        $toc .= '</button>';

        // 目录面板（桌面侧边栏 / 移动浮层共用）
        $toc .= '<div class="elegant-toc-panel">';

        $toc .= '<div class="elegant-toc-topbar"></div>';

        $toc .= '<div class="elegant-toc-header">';
        $toc .= '<div class="elegant-toc-header-left">';

        // 极简图标
        $toc .= '<span class="elegant-toc-icon" aria-hidden="true">';
        $toc .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:block;">';
        $toc .= '<line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line>';
        $toc .= '<line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line>';
        $toc .= '</svg></span>';

        $toc .= '<span class="elegant-toc-title">' . esc_html__('文章目录', 'elegant-toc') . '</span>';
        $toc .= '</div>';

        // 折叠按钮
        $toc .= '<button type="button" class="elegant-toc-toggle" aria-label="' . esc_attr__('折叠目录', 'elegant-toc') . '" aria-expanded="true">';
        $toc .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        $toc .= '</button>';

        // 移动端关闭按钮
        $toc .= '<button type="button" class="elegant-toc-mobile-close" aria-label="' . esc_attr__('关闭目录', 'elegant-toc') . '">';
        $toc .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        $toc .= '</button>';

        $toc .= '</div>';

        // 搜索框
        $toc .= '<input type="search" class="elegant-toc-search" placeholder="' . esc_attr__('搜索目录...', 'elegant-toc') . '" aria-label="' . esc_attr__('搜索目录', 'elegant-toc') . '" />';

        $toc .= '<ul class="elegant-toc-list">';

        $min_level = min(array_column($headings, 'level'));

        foreach ($headings as $h) {
            $indent = max(0, min(3, $h['level'] - $min_level));

            $toc .= '<li class="elegant-toc-item elegant-toc-level-' . $indent . '">';
            $toc .= '<a class="elegant-toc-link" href="#' . esc_attr($h['id']) . '"';
            $toc .= ' data-toc-target="' . esc_attr($h['id']) . '"';
            $toc .= ' aria-label="' . esc_attr__('跳转到：', 'elegant-toc') . esc_attr($h['text']) . '"';
            $toc .= ' title="' . esc_attr($h['text']) . '">';
            $toc .= '<span class="elegant-toc-bullet" aria-hidden="true"></span>';
            $toc .= '<span class="elegant-toc-text">' . esc_html($h['text']) . '</span>';
            $toc .= '</a></li>';
        }

        $toc .= '</ul></div></nav>';

        return $toc;
    }

}

Elegant_TOC::get_instance();
