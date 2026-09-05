=== Elegant TOC ===
Contributors: jacky088
Tags: table of contents, toc, outline, 目录
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

优雅的 WordPress 文章目录插件：自动生成美观目录，支持平滑滚动、智能高亮、移动端浮层与多种配色主题。

== Description ==

Elegant TOC 根据文章中的 H2-H6 标题自动生成目录：

* 桌面端左侧悬浮侧边栏，移动端左下角触发按钮 + 底部浮层面板
* 平滑滚动与阅读位置智能高亮
* 7 种配色主题，支持系统深色模式自动切换
* 短代码 `[elegant_toc]`，支持 `title` 属性自定义标题
* 单篇禁用：编辑器侧边栏勾选，或自定义字段 `_elegant_toc_disabled`（旧字段 `disable_toc` 仍兼容）
* 按需加载 CSS/JS，纯原生 JavaScript，无前端框架依赖
* 无障碍支持：ARIA 属性、键盘可访问、Esc 关闭移动端面板、尊重系统减弱动效设置

开发者可通过 `elegant_toc_post_types` 过滤器自定义生效文章类型，
通过 `elegant_toc_scroll_offset` 过滤器强制指定锚点滚动偏移（px）。

== Installation ==

1. 上传整个插件文件夹到 `/wp-content/plugins/elegant-toc/` 目录
2. 在「插件」页面激活 Elegant TOC
3. 前往「设置」→「Elegant TOC」进行配置

== Frequently Asked Questions ==

= 如何在某一篇文章禁用目录？ =

在文章编辑器右侧的「Elegant TOC」元数据框中勾选「在此文章/页面中禁用目录」，
或添加自定义字段 `_elegant_toc_disabled`，值为 `1`。

= 为什么静态首页不显示目录？ =

插件默认跳过站点首页（含静态首页），即使内容中包含短代码；目录仅在文章/页面等单篇内容中生成。

== Changelog ==

= 1.9.1 =
* 修复：目录列表滚动定位偏移（offsetTop 相对面板而非列表，长目录回滚时高亮项可能滚出可视区）
* 修复：admin.css 版本号误用 style.css 的文件时间戳，导致后台样式缓存不刷新
* 修复：PHP 8 下 the_content 在主循环之外被调用时的空值告警；修订版本不再写入自定义字段
* 优化：移除强加给整站的 html scroll-behavior: smooth；平滑滚动尊重系统「减弱动态效果」设置
* 优化：滚动高亮仅切换新旧链接类名；侧边栏可见性更新改为先读后写，消除滚动帧强制重排
* 优化：折叠状态改为点击时直接写入 localStorage；资源版本号按需计算；小屏降低毛玻璃开销
* 优化：生产环境默认关闭控制台调试日志（设 window.elegantTocDebug = true 开启）
* 无障碍：移动端面板支持 Esc 关闭并归还焦点；触发按钮增加 aria-controls；目录链接 aria-label 使用可翻译模板
* 加固：标题 id 冲突替换改用 preg_replace_callback；$_POST 读取接入 wp_unslash
* 维护：禁用字段迁移为 _elegant_toc_disabled（兼容旧字段 disable_toc）；清理死代码与冗余样式；补齐插件头信息；新增 LICENSE、readme.txt 与目录 index.php 防护

= 1.9.0 =
* 深色模式适配、毛玻璃增强与多项性能优化

== Upgrade Notice ==

= 1.9.1 =
修复长目录滚动定位与后台样式缓存问题，增强无障碍支持与移动端性能。
