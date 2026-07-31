<?php
/**
 * Frontend functionality
 *
 * @package ElegantTOC
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class
 */
class Elegant_TOC_Frontend {
    private $main;

    public function __construct($main) {
        $this->main = $main;
    }

    public function init() {
        add_filter('the_content', array($this, 'insert_toc'), 20);
    }

    /**
     * Insert TOC into content
     */
    public function insert_toc($content) {
        if (is_admin() || is_feed() || is_front_page() || !is_singular()) {
            return $content;
        }

        if ($this->main->toc_inserted) {
            return $content;
        }

        $post_id = get_the_ID();
        if ($this->main->is_post_disabled($post_id)) {
            return $content;
        }

        $options = $this->main->get_options();
        $auto_insert = !empty($options['enabled']);

        $post = get_post();
        $has_shortcode = $post && has_shortcode($post->post_content, 'elegant_toc');

        if (!$auto_insert && !$has_shortcode) {
            return $content;
        }

        $result   = $this->extract_and_annotate_headings($content, $options['heading_levels']);
        $headings = $result['headings'];
        $content  = $result['content'];

        if (count($headings) < $options['min_headings']) {
            return $content;
        }

        $this->main->toc_inserted = true;
        $this->main->enqueue_assets();

        $toc = $this->generate_toc($headings);

        if ($has_shortcode) {
            return $this->replace_shortcode_once($content, $toc);
        }

        return $toc . $content;
    }

    /**
     * Extract headings and add IDs
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

            if (strpos($attrs, 'elegant-toc-heading') === false) {
                $attrs .= ' class="elegant-toc-heading"';
            }

            $used_ids[$id] = true;
            $headings[] = array('level' => $level, 'text' => $text, 'id' => $id);

            return '<' . $tag . $attrs . '>' . $inner . '</' . $tag . '>';
        }, $content);

        return array('headings' => $headings, 'content' => $content);
    }

    /**
     * Generate unique ID
     */
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
     * Generate TOC HTML
     */
    private function generate_toc($headings) {
        $options = $this->main->get_options();
        $theme   = !empty($options['color_theme']) ? sanitize_text_field($options['color_theme']) : 'light';

        $toc  = '<!-- Elegant TOC v' . esc_html(Elegant_TOC::VERSION) . ' -->';
        $toc .= '<nav class="elegant-toc" id="elegant-toc"';
        $toc .= ' data-et-ver="' . esc_attr(Elegant_TOC::VERSION) . '"';
        if ('auto' !== $theme) {
            $toc .= ' data-et-theme="' . esc_attr($theme) . '"';
        }
        $toc .= ' aria-label="' . esc_attr__('文章目录', 'elegant-toc') . '">';

        $toc .= '<button type="button" class="elegant-toc-trigger" aria-label="' . esc_attr__('打开目录', 'elegant-toc') . '" title="' . esc_attr__('文章目录', 'elegant-toc') . '" data-tooltip="' . esc_attr__('文章目录', 'elegant-toc') . '" aria-expanded="false">';
        $toc .= '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;">';
        $toc .= '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
        $toc .= '</svg></button>';

        $toc .= '<div class="elegant-toc-panel">';
        $toc .= '<div class="elegant-toc-topbar"></div>';
        $toc .= '<div class="elegant-toc-header">';
        $toc .= '<div class="elegant-toc-header-left">';
        $toc .= '<span class="elegant-toc-icon" aria-hidden="true">';
        $toc .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:block;">';
        $toc .= '<line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line>';
        $toc .= '<line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line>';
        $toc .= '</svg></span>';
        $toc .= '<span class="elegant-toc-title">' . esc_html__('文章目录', 'elegant-toc') . '</span>';
        $toc .= '</div>';
        $toc .= '<button type="button" class="elegant-toc-toggle" aria-label="' . esc_attr__('折叠目录', 'elegant-toc') . '" aria-expanded="true">';
        $toc .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        $toc .= '</button>';
        $toc .= '<button type="button" class="elegant-toc-mobile-close" aria-label="' . esc_attr__('关闭目录', 'elegant-toc') . '">';
        $toc .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        $toc .= '</button></div>';

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

    /**
     * Replace shortcode placeholder with TOC
     */
    private function replace_shortcode_once($content, $toc) {
        $placeholder = '<div class="elegant-toc-placeholder" aria-hidden="true"></div>';
        $pos = strpos($content, $placeholder);
        if ($pos === false) {
            return $toc . $content;
        }
        return substr_replace($content, $toc, $pos, strlen($placeholder));
    }
}
