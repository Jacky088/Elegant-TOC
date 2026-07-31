<div class="wrap elegant-toc-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="elegant-toc-admin-container">
        <form method="post" action="options.php">
            <?php
            settings_fields('elegant_toc_options');
            do_settings_sections('elegant-toc');
            ?>

            <div class="elegant-toc-section">
                <h2>基本设置</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">启用目录</th>
                        <td>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[enabled]" value="1" <?php checked($options['enabled'], true); ?> />
                                在文章中自动显示目录
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">最少标题数</th>
                        <td>
                            <input type="number" name="elegant_toc_options[min_headings]" value="<?php echo esc_attr($options['min_headings']); ?>" min="1" max="10" class="small-text" />
                            <p class="description">当文章标题数量少于此值时，不显示目录</p>
                        </td>
                    </tr>

                </table>
            </div>

            <div class="elegant-toc-section">
                <h2>标题层级</h2>
                <p class="description">选择要包含在目录中的标题层级</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">包含层级</th>
                        <td>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="h2" <?php checked(in_array('h2', $options['heading_levels'])); ?> />
                                H2 (主标题)
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="h3" <?php checked(in_array('h3', $options['heading_levels'])); ?> />
                                H3 (二级标题)
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="h4" <?php checked(in_array('h4', $options['heading_levels'])); ?> />
                                H4 (三级标题)
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="h5" <?php checked(in_array('h5', $options['heading_levels'])); ?> />
                                H5 (四级标题)
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="elegant_toc_options[heading_levels][]" value="h6" <?php checked(in_array('h6', $options['heading_levels'])); ?> />
                                H6 (五级标题)
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="elegant-toc-section">
                <h2>个性化配色</h2>
                <p class="description">选择目录的主色调，按钮、背景、边框等会同步更换</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">色彩主题</th>
                        <td>
                            <div class="elegant-toc-color-options">
                                <?php
                                $themes = array(
                                    'auto'   => array('label' => '跟随系统', 'color' => 'linear-gradient(135deg, #f3f4f6 50%, #1f2937 50%)', 'text' => '#374151'),
                                    'light'  => array('label' => '浅灰（默认）', 'color' => '#f3f4f6', 'text' => '#374151'),
                                    'blue'   => array('label' => '清新蓝', 'color' => '#dbeafe', 'text' => '#1e40af'),
                                    'green'  => array('label' => '自然绿', 'color' => '#dcfce7', 'text' => '#166534'),
                                    'purple' => array('label' => '优雅紫', 'color' => '#f3e8ff', 'text' => '#7e22ce'),
                                    'orange' => array('label' => '活力橙', 'color' => '#ffedd5', 'text' => '#9a3412'),
                                    'dark'   => array('label' => '暗夜黑', 'color' => '#1f2937', 'text' => '#f3f4f6'),
                                );
                                $current_theme = !empty($options['color_theme']) ? $options['color_theme'] : 'light';
                                foreach ($themes as $theme_key => $theme_data) :
                                    $is_checked = ($current_theme === $theme_key);
                                ?>
                                    <label class="elegant-toc-color-option<?php echo $is_checked ? ' is-checked' : ''; ?>" data-theme="<?php echo esc_attr($theme_key); ?>" style="background: <?php echo esc_attr($theme_data['color']); ?>; color: <?php echo esc_attr($theme_data['text']); ?>;">
                                        <input type="radio" name="elegant_toc_options[color_theme]" value="<?php echo esc_attr($theme_key); ?>" <?php checked($is_checked); ?> />
                                        <span class="elegant-toc-color-label"><?php echo esc_html($theme_data['label']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="elegant-toc-section">
                <h2>自定义样式</h2>
                <p class="description">添加自定义 CSS 代码来进一步定制目录外观</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">自定义 CSS</th>
                        <td>
                            <textarea name="elegant_toc_options[custom_css]" rows="8" class="large-text code" placeholder="/* 在这里输入自定义 CSS 代码 */"><?php echo esc_textarea($options['custom_css']); ?></textarea>
                            <p class="description">例如：#elegant-toc { --et-accent: #ff5722; }</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="elegant-toc-preview">
                <h2>预览效果</h2>
                <p class="description">这是目录的外观预览，选择配色后可实时查看效果</p>

                <div class="elegant-toc-preview-frame">
                    <nav class="elegant-toc" id="elegant-toc" style="position: relative; top: 0; left: 0; bottom: auto; max-width: 100%;"<?php echo ('auto' !== $current_theme) ? ' data-et-theme="' . esc_attr($current_theme) . '"' : ''; ?>>
                    <div class="elegant-toc-panel" style="display: block; opacity: 1; visibility: visible; pointer-events: auto; transform: none; position: relative;">
                        <div class="elegant-toc-topbar"></div>
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
                                <span class="elegant-toc-title">文章目录</span>
                            </div>
                            <button type="button" class="elegant-toc-toggle" aria-label="折叠目录" aria-expanded="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                </svg>
                            </button>
                        </div>
                        <input type="search" class="elegant-toc-search" placeholder="搜索目录..." aria-label="搜索目录" />
                        <ul class="elegant-toc-list">
                        <li class="elegant-toc-item elegant-toc-level-0">
                            <a href="#" class="elegant-toc-link active">
                                <span class="elegant-toc-bullet"></span>
                                <span class="elegant-toc-text">项目介绍</span>
                            </a>
                        </li>
                        <li class="elegant-toc-item elegant-toc-level-0">
                            <a href="#" class="elegant-toc-link">
                                <span class="elegant-toc-bullet"></span>
                                <span class="elegant-toc-text">功能特性</span>
                            </a>
                        </li>
                        <li class="elegant-toc-item elegant-toc-level-1">
                            <a href="#" class="elegant-toc-link">
                                <span class="elegant-toc-bullet"></span>
                                <span class="elegant-toc-text">自动生成目录</span>
                            </a>
                        </li>
                        <li class="elegant-toc-item elegant-toc-level-1">
                            <a href="#" class="elegant-toc-link">
                                <span class="elegant-toc-bullet"></span>
                                <span class="elegant-toc-text">平滑滚动效果</span>
                            </a>
                        </li>
                        <li class="elegant-toc-item elegant-toc-level-0">
                            <a href="#" class="elegant-toc-link">
                                <span class="elegant-toc-bullet"></span>
                                <span class="elegant-toc-text">安装说明</span>
                            </a>
                        </li>
                    </ul>
                    </div>
                </nav>
                </div>
            </div>

            <?php submit_button('保存设置', 'primary', 'elegant_toc_submit'); ?>
        </form>
    </div>
</div>

<script>
(function () {
    var preview = document.querySelector('.elegant-toc-preview .elegant-toc');
    var options = document.querySelectorAll('.elegant-toc-color-option input[type="radio"]');

    options.forEach(function (radio) {
        radio.addEventListener('change', function () {
            var theme = this.value;
            if (preview) {
                if ('auto' === theme) {
                    preview.removeAttribute('data-et-theme');
                } else {
                    preview.setAttribute('data-et-theme', theme);
                }
            }
            options.forEach(function (r) {
                var option = r.closest('.elegant-toc-color-option');
                if (option) {
                    option.classList.toggle('is-checked', r.checked);
                }
            });
        });
    });
})();
</script>
