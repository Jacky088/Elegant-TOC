<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_theme = !empty($options['color_theme']) ? $options['color_theme'] : 'light';
?>
<div class="wrap elegant-toc-admin">
    <!-- 顶部标题与文档链接 -->
    <div class="et-admin-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <a href="https://github.com/Jacky088/Elegant-TOC" target="_blank" rel="noopener noreferrer" class="et-doc-link">
            <?php esc_html_e('查看文档', 'elegant-toc'); ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
        </a>
    </div>

    <form method="post" action="options.php" id="elegant-toc-settings-form">
        <?php settings_fields('elegant_toc_options'); ?>

        <div class="et-admin-layout">
            <!-- 左侧分区导航（不含关于插件） -->
            <aside class="et-admin-sidebar">
                <ul class="et-nav-list">
                    <li class="et-nav-item">
                        <button type="button" class="et-nav-btn is-active" data-target="section-basic">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <span><?php esc_html_e('基本设置', 'elegant-toc'); ?></span>
                        </button>
                    </li>
                    <li class="et-nav-item">
                        <button type="button" class="et-nav-btn" data-target="section-headings">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                            <span><?php esc_html_e('标题层级', 'elegant-toc'); ?></span>
                        </button>
                    </li>
                    <li class="et-nav-item">
                        <button type="button" class="et-nav-btn" data-target="section-appearance">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                            </svg>
                            <span><?php esc_html_e('外观样式', 'elegant-toc'); ?></span>
                        </button>
                    </li>
                    <li class="et-nav-item">
                        <button type="button" class="et-nav-btn" data-target="section-preview">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span><?php esc_html_e('预览效果', 'elegant-toc'); ?></span>
                        </button>
                    </li>
                </ul>
            </aside>

            <!-- 右侧卡片内容区 -->
            <main class="et-admin-main">
                <!-- 1. 基本设置卡片 -->
                <div class="et-card" id="section-basic">
                    <div class="et-card-header">
                        <div class="et-card-title-group">
                            <h2 class="et-card-title"><?php esc_html_e('基本设置', 'elegant-toc'); ?></h2>
                            <p class="et-card-desc"><?php esc_html_e('控制目录的显示范围和触发条件', 'elegant-toc'); ?></p>
                        </div>
                    </div>

                    <!-- 启用目录 -->
                    <div class="et-form-row">
                        <div class="et-form-label"><?php esc_html_e('启用目录', 'elegant-toc'); ?></div>
                        <div class="et-form-field">
                            <label class="et-switch-wrap" for="et-enabled-switch">
                                <input type="checkbox" name="elegant_toc_options[enabled]" value="1" id="et-enabled-switch" class="et-switch-input" <?php checked($options['enabled'], true); ?> />
                                <span class="et-switch-slider"></span>
                                <span class="et-switch-text"><?php esc_html_e('在文章中自动显示目录', 'elegant-toc'); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- 显示位置 -->
                    <div class="et-form-row">
                        <div class="et-form-label"><?php esc_html_e('显示位置', 'elegant-toc'); ?></div>
                        <div class="et-form-field">
                            <p class="et-field-sublabel"><?php esc_html_e('选择在哪些内容类型中显示目录', 'elegant-toc'); ?></p>
                            <div class="et-checkbox-list">
                                <?php
                                $post_types = get_post_types(array('public' => true), 'objects');
                                $selected_types = !empty($options['post_types']) ? $options['post_types'] : array('post', 'page');

                                foreach ($post_types as $post_type) :
                                    if ($post_type->name === 'attachment') {
                                        continue;
                                    }
                                    $is_checked = in_array($post_type->name, $selected_types, true);
                                ?>
                                    <label class="et-checkbox-label">
                                        <input type="checkbox" name="elegant_toc_options[post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked($is_checked); ?> />
                                        <span><?php echo esc_html($post_type->labels->name); ?></span>
                                        <span class="et-post-type-slug">(<?php echo esc_html($post_type->name); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="et-tip-box">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <span><?php esc_html_e('通常建议勾选 “文章” 和 “页面”', 'elegant-toc'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 最少标题数 -->
                    <div class="et-form-row">
                        <div class="et-form-label"><?php esc_html_e('最少标题数', 'elegant-toc'); ?></div>
                        <div class="et-form-field" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <input type="number" name="elegant_toc_options[min_headings]" id="et-min-headings" value="<?php echo esc_attr($options['min_headings']); ?>" min="1" max="10" class="et-input et-input-number" />
                            <span class="et-field-desc" style="margin-top: 0;"><?php esc_html_e('当文章标题数量少于此值时，不显示目录', 'elegant-toc'); ?></span>
                        </div>
                    </div>

                    <!-- 顶部偏移量 (Offset) -->
                    <div class="et-form-row">
                        <div class="et-form-label"><?php esc_html_e('顶部偏移量 (Offset)', 'elegant-toc'); ?></div>
                        <div class="et-form-field">
                            <div class="et-input-group">
                                <input type="number" name="elegant_toc_options[scroll_offset]" id="et-scroll-offset" value="<?php echo esc_attr(!empty($options['scroll_offset']) ? $options['scroll_offset'] : ''); ?>" min="0" max="500" placeholder="<?php esc_attr_e('自动探测（推荐）', 'elegant-toc'); ?>" class="et-input" />
                                <span class="et-input-addon">px</span>
                            </div>
                            <p class="et-field-desc">
                                <?php esc_html_e('若网站有顶部吸顶固定导航栏（Fixed Header），可填入吸顶栏高度（推荐 80 ~ 120px）。留空或填 0 则由插件自动探测。', 'elegant-toc'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. 标题层级卡片 -->
                <div class="et-card et-card--compact" id="section-headings">
                    <div class="et-headings-row">
                        <div class="et-card-title-group">
                            <h2 class="et-card-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                                <?php esc_html_e('标题层级', 'elegant-toc'); ?>
                            </h2>
                            <p class="et-card-desc"><?php esc_html_e('选择要包含在目录中的标题层级', 'elegant-toc'); ?></p>
                        </div>

                        <div class="et-heading-pills">
                            <?php
                            $all_levels = array(
                                'h2' => 'H2',
                                'h3' => 'H3',
                                'h4' => 'H4',
                                'h5' => 'H5',
                                'h6' => 'H6',
                            );
                            foreach ($all_levels as $level_val => $level_title) :
                                $checked = in_array($level_val, $options['heading_levels'], true);
                            ?>
                                <label class="et-heading-pill">
                                    <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="<?php echo esc_attr($level_val); ?>" <?php checked($checked); ?> />
                                    <span><?php echo esc_html($level_title); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. 外观样式卡片 -->
                <div class="et-card" id="section-appearance">
                    <div class="et-card-header">
                        <div class="et-card-title-group">
                            <h2 class="et-card-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                    <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                    <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                    <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                                </svg>
                                <?php esc_html_e('外观样式', 'elegant-toc'); ?>
                            </h2>
                            <p class="et-card-desc"><?php esc_html_e('自定义目录的配色风格，按钮、背景、边框等将同步更换', 'elegant-toc'); ?></p>
                        </div>
                    </div>

                    <div class="elegant-toc-color-options">
                        <?php
                        $themes = array(
                            'auto'   => array('label' => __('跟随系统', 'elegant-toc'), 'color' => 'linear-gradient(135deg, #f3f4f6 50%, #1f2937 50%)', 'text' => '#374151', 'border' => '#e5e7eb'),
                            'light'  => array('label' => __('浅灰 (默认)', 'elegant-toc'), 'color' => '#f3f4f6', 'text' => '#374151', 'border' => '#e5e7eb'),
                            'blue'   => array('label' => __('清新蓝', 'elegant-toc'), 'color' => '#dbeafe', 'text' => '#1e40af', 'border' => '#bfdbfe'),
                            'green'  => array('label' => __('自然绿', 'elegant-toc'), 'color' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'),
                            'purple' => array('label' => __('优雅紫', 'elegant-toc'), 'color' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'),
                            'orange' => array('label' => __('活力橙', 'elegant-toc'), 'color' => '#ffedd5', 'text' => '#9a3412', 'border' => '#fed7aa'),
                            'dark'   => array('label' => __('暗夜黑', 'elegant-toc'), 'color' => '#1f2937', 'text' => '#f3f4f6', 'border' => '#374151'),
                        );

                        foreach ($themes as $theme_key => $theme_data) :
                            $is_checked = ($current_theme === $theme_key);
                        ?>
                            <label class="elegant-toc-color-option<?php echo $is_checked ? ' is-checked' : ''; ?>" data-theme="<?php echo esc_attr($theme_key); ?>" style="background: <?php echo esc_attr($theme_data['color']); ?>; color: <?php echo esc_attr($theme_data['text']); ?>; border-color: <?php echo $is_checked ? '#2563eb' : esc_attr($theme_data['border']); ?>;">
                                <input type="radio" name="elegant_toc_options[color_theme]" value="<?php echo esc_attr($theme_key); ?>" <?php checked($is_checked); ?> />
                                <span class="elegant-toc-color-label"><?php echo esc_html($theme_data['label']); ?></span>
                                <span class="et-check-badge">
                                    <svg viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M13.485 3.515a1 1 0 0 1 0 1.414l-7 7a1 1 0 0 1-1.414 0l-3.5-3.5a1 1 0 1 1 1.414-1.414L6.2 10.3l6.293-6.285a1 1 0 0 1 1.414 0z" />
                                    </svg>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. 预览效果卡片 -->
                <div class="et-card" id="section-preview">
                    <div class="et-card-header">
                        <div class="et-card-title-group">
                            <h2 class="et-card-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php esc_html_e('预览效果', 'elegant-toc'); ?>
                            </h2>
                            <p class="et-card-desc"><?php esc_html_e('实时预览目录在页面中的显示效果', 'elegant-toc'); ?></p>
                        </div>
                    </div>

                    <div class="elegant-toc-preview-frame">
                        <nav class="elegant-toc" id="elegant-toc" style="position: relative; top: 0; left: 0; bottom: auto; max-width: 100%;"<?php echo ('auto' !== $current_theme) ? ' data-et-theme="' . esc_attr($current_theme) . '"' : ''; ?>>
                            <div class="elegant-toc-panel" style="display: block; opacity: 1; visibility: visible; pointer-events: auto; transform: none; position: relative;">
                                <div class="elegant-toc-header">
                                    <div class="elegant-toc-header-left">
                                        <span class="elegant-toc-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                            </svg>
                                        </span>
                                        <span class="elegant-toc-title"><?php esc_html_e('文章目录', 'elegant-toc'); ?></span>
                                    </div>
                                    <button type="button" class="elegant-toc-toggle" aria-label="<?php esc_attr_e('折叠目录', 'elegant-toc'); ?>" aria-expanded="true">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="18 15 12 9 6 15"></polyline>
                                        </svg>
                                    </button>
                                </div>
                                <ul class="elegant-toc-list">
                                    <li class="elegant-toc-item elegant-toc-level-0">
                                        <a href="javascript:void(0);" class="elegant-toc-link active">
                                            <span class="elegant-toc-bullet"></span>
                                            <span class="elegant-toc-text">项目介绍</span>
                                        </a>
                                    </li>
                                    <li class="elegant-toc-item elegant-toc-level-0">
                                        <a href="javascript:void(0);" class="elegant-toc-link">
                                            <span class="elegant-toc-bullet"></span>
                                            <span class="elegant-toc-text">功能特性</span>
                                        </a>
                                    </li>
                                    <li class="elegant-toc-item elegant-toc-level-1">
                                        <a href="javascript:void(0);" class="elegant-toc-link">
                                            <span class="elegant-toc-bullet"></span>
                                            <span class="elegant-toc-text">自动生成目录</span>
                                        </a>
                                    </li>
                                    <li class="elegant-toc-item elegant-toc-level-1">
                                        <a href="javascript:void(0);" class="elegant-toc-link">
                                            <span class="elegant-toc-bullet"></span>
                                            <span class="elegant-toc-text">平滑滚动效果</span>
                                        </a>
                                    </li>
                                    <li class="elegant-toc-item elegant-toc-level-0">
                                        <a href="javascript:void(0);" class="elegant-toc-link">
                                            <span class="elegant-toc-bullet"></span>
                                            <span class="elegant-toc-text">安装说明</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>

                <!-- 统一底部操作区 -->
                <div class="et-bottom-bar">
                    <div class="et-bottom-left">
                        <button type="button" class="et-btn et-btn-ghost" id="et-reset-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            <span><?php esc_html_e('恢复默认设置', 'elegant-toc'); ?></span>
                        </button>
                    </div>

                    <div class="et-bottom-right">
                        <button type="button" class="et-btn et-btn-secondary" id="et-cancel-btn">
                            <?php esc_html_e('取消', 'elegant-toc'); ?>
                        </button>
                        <button type="submit" name="submit" id="submit" class="et-btn et-btn-primary">
                            <?php esc_html_e('保存设置', 'elegant-toc'); ?>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </form>
</div>

<script>
(function () {
    var preview = document.querySelector('.elegant-toc-preview-frame .elegant-toc');
    var themeOptions = document.querySelectorAll('.elegant-toc-color-option input[type="radio"]');
    var navButtons = document.querySelectorAll('.et-nav-btn');
    var form = document.getElementById('elegant-toc-settings-form');
    var resetBtn = document.getElementById('et-reset-btn');
    var cancelBtn = document.getElementById('et-cancel-btn');

    // 1. 主题选择与实时预览联动
    themeOptions.forEach(function (radio) {
        radio.addEventListener('change', function () {
            var theme = this.value;
            if (preview) {
                if ('auto' === theme) {
                    preview.removeAttribute('data-et-theme');
                } else {
                    preview.setAttribute('data-et-theme', theme);
                }
            }
            themeOptions.forEach(function (r) {
                var option = r.closest('.elegant-toc-color-option');
                if (option) {
                    option.classList.toggle('is-checked', r.checked);
                    if (r.checked) {
                        option.style.borderColor = '#2563eb';
                    } else {
                        var originalBorder = option.getAttribute('data-border') || '#e5e7eb';
                        option.style.borderColor = originalBorder;
                    }
                }
            });
        });
    });

    // 2. 左侧分区导航点击平滑滚动
    navButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('data-target');
            var targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            navButtons.forEach(function (b) { b.classList.remove('is-active'); });
            this.classList.add('is-active');
        });
    });

    // 3. 滚动监测（Scrollspy）自动高亮左侧导航
    if ('IntersectionObserver' in window) {
        var cards = document.querySelectorAll('.et-card');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.id;
                    navButtons.forEach(function (b) {
                        b.classList.toggle('is-active', b.getAttribute('data-target') === id);
                    });
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px' });

        cards.forEach(function (card) { observer.observe(card); });
    }

    // 4. 取消按钮：重置表单为加载时的初始值
    if (cancelBtn && form) {
        cancelBtn.addEventListener('click', function () {
            form.reset();
            // 重新触发当前选中的 theme radio 还原预览
            var checkedRadio = form.querySelector('.elegant-toc-color-option input[type="radio"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            }
        });
    }

    // 5. 恢复默认设置按钮
    if (resetBtn && form) {
        resetBtn.addEventListener('click', function () {
            if (!confirm('<?php echo esc_js(__('确定要将所有设置恢复为默认值吗？点击保存后生效。', 'elegant-toc')); ?>')) {
                return;
            }

            // 启用开关
            var enableSwitch = document.getElementById('et-enabled-switch');
            if (enableSwitch) enableSwitch.checked = true;

            // 显示位置（恢复勾选 post 和 page）
            var postTypeChecks = form.querySelectorAll('input[name="elegant_toc_options[post_types][]"]');
            postTypeChecks.forEach(function (cb) {
                cb.checked = (cb.value === 'post' || cb.value === 'page');
            });

            // 最少标题数
            var minHeadings = document.getElementById('et-min-headings');
            if (minHeadings) minHeadings.value = 3;

            // 顶部偏移量
            var offsetInput = document.getElementById('et-scroll-offset');
            if (offsetInput) offsetInput.value = '';

            // 标题层级（恢复勾选 H2, H3, H4）
            var headingChecks = form.querySelectorAll('input[name="elegant_toc_options[heading_levels][]"]');
            headingChecks.forEach(function (cb) {
                cb.checked = (cb.value === 'h2' || cb.value === 'h3' || cb.value === 'h4');
            });

            // 配色主题（浅灰默认）
            var lightRadio = form.querySelector('.elegant-toc-color-option input[value="light"]');
            if (lightRadio) {
                lightRadio.checked = true;
                lightRadio.dispatchEvent(new Event('change'));
            }
        });
    }
})();
</script>
