# Elegant TOC - 优雅的 WordPress 文章目录插件

一个现代、轻量、自适应的 WordPress 文章目录插件，自动生成美观的目录，支持平滑滚动、智能高亮和丰富的个性化配置。

![Version](https://img.shields.io/badge/version-1.7.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)

## 页面预览
![elegant-toc 预览](./screenshot-preview-pc-elegant-tco.jpg)

## ✨ 功能特性

### 核心功能
- **自动生成目录** - 根据文章中的 H2-H6 标题自动生成层级目录
- **平滑滚动** - 点击目录项平滑滚动到对应章节
- **智能高亮** - 根据阅读位置自动高亮当前章节
- **状态记忆** - 记住目录折叠/展开状态
- **单篇禁用** - 支持在特定文章/页面中禁用目录
- **短代码支持** - 使用 `[elegant_toc]` 在任意位置插入目录
- **搜索过滤** - 实时搜索目录项，快速定位章节
- **快捷键操作** - 按 `T` 键快速打开/关闭目录，`Esc` 键关闭移动端面板

### 自适应布局
- **桌面端**（浏览器宽度 ≥ 1024px 且正文左侧空间足够）：目录以左侧悬浮侧边栏形式显示，紧贴正文左侧，离开正文区域后自动隐藏
- **移动端 / 空间不足**：左下角显示目录触发按钮，点击后以底部浮层面板展开，带有缩放打开/关闭动画

### 个性化与主题
- **7 种色彩主题**：浅灰（默认）、清新蓝、自然绿、优雅紫、活力橙、暗夜黑、跟随系统
- **深色模式**：支持系统深色模式自动切换，同时兼容 `body.dark-mode`、`body[data-theme="dark"]`、`html.dark` 等常见主题类名
- **字号同步**：目录标题和条目字号自动与文章正文字号保持一致
- **自定义样式**：支持添加自定义 CSS 代码，深度定制外观

## 📦 安装方法

### 方式一：WordPress 后台安装
1. 下载最新版本的 zip 文件
2. 在 WordPress 后台「插件」→「安装插件」→「上传插件」
3. 选择下载的 zip 文件并安装
4. 激活插件

### 方式二：手动安装
1. 将整个插件文件夹上传到 `/wp-content/plugins/elegant-toc/` 目录
2. 在 WordPress 后台「插件」页面激活「Elegant TOC」
3. 点击插件列表中的「设置」或前往「设置」→「Elegant TOC」进行配置

## ⚙️ 配置选项

在「设置」→「Elegant TOC」中可配置：

- **启用目录** - 控制是否在文章中自动显示目录
- **最少标题数** - 设置显示目录所需的最少标题数量（默认 3 个）
- **标题层级** - 选择参与生成目录的标题级别（H2-H6）
- **色彩主题** - 选择目录的整体配色方案
- **自定义 CSS** - 添加自定义样式代码

## 🖥️ 使用方式

### 自动显示
插件激活并启用后，会自动在满足条件的文章/页面中显示目录。

### 短代码
在文章/页面内容中插入短代码，即可在指定位置渲染目录：

```
[elegant_toc]
```

### 单篇禁用

**方法 1**：在文章编辑器右侧的「Elegant TOC」元数据框中勾选「在此文章/页面中禁用目录」。

**方法 2**：通过自定义字段禁用：
- 字段名：`disable_toc`
- 字段值：`1`

### 快捷键
- **T 键**：打开/关闭目录（桌面端折叠/展开，移动端打开/关闭）
- **Esc 键**：关闭移动端面板

## 🔧 开发者

### 自定义生效文章类型

通过 `elegant_toc_post_types` 过滤器可自定义哪些文章类型自动显示目录：

```php
add_filter('elegant_toc_post_types', function ($post_types) {
    $post_types[] = 'custom_post_type';
    return $post_types;
});
```

### 开发环境设置

```bash
# 克隆仓库
git clone https://github.com/Jacky088/Elegant-TOC.git

# 安装依赖
composer install

# 运行测试
composer test

# 代码规范检查
composer phpcs

# 代码规范修复
composer phpcbf
```

## 🗂️ 文件结构

```
elegant-toc/
├── elegant-toc.php          # 主插件文件
├── README.md                # 项目说明文档
├── CHANGELOG.md             # 更新日志
├── composer.json            # Composer 配置
├── phpunit.xml              # PHPUnit 配置
├── .gitignore               # Git 忽略规则
├── assets/
│   ├── style.css            # 前端样式（开发版）
│   ├── style.min.css        # 前端样式（压缩版）
│   ├── script.js            # 前端脚本（开发版）
│   ├── script.min.js        # 前端脚本（压缩版）
│   └── admin.css            # 后台设置页样式
├── includes/
│   ├── class-frontend.php   # 前端功能类
│   └── class-admin.php      # 后台管理类
├── admin/
│   └── settings-page.php    # 后台设置页面模板
├── languages/
│   └── elegant-toc.pot      # 翻译模板
└── tests/
    └── test-elegant-toc.php # 单元测试
```

## 🛠️ 技术特性

- 纯原生 JavaScript，无前端依赖
- CSS 自定义属性（CSS Variables）实现主题切换
- 节流与防抖优化滚动/resize 性能
- 条件加载资源（仅在需要的文章页面加载）
- 生产环境自动使用压缩版资源（30%+ 文件大小减少）
- 选项缓存机制，减少数据库查询
- 模块化代码结构，易于维护和扩展
- 完善的错误处理和安全防护
- 无障碍支持：ARIA 属性、焦点样式、键盘可访问

## 🔄 更新日志

### 1.7.0 (2026-07-31)
**性能优化**
- 新增资源文件压缩版本，生产环境自动使用，减少 30%+ 加载时间
- 添加选项缓存机制，减少数据库查询
- 优化 JavaScript 性能和错误处理

**功能增强**
- 新增目录搜索功能，支持实时过滤
- 新增自定义 CSS 选项，深度定制外观
- 新增快捷键支持：T 键打开/关闭，Esc 键关闭移动端面板
- 新增加载状态指示

**代码质量**
- 模块化重构，分离前端和后台逻辑
- 添加完整的单元测试框架
- 增强安全性：输入验证、权限检查、CSS 过滤
- 添加 Composer 支持和代码规范检查
- 完善国际化，生成翻译文件模板

**开发者体验**
- 新增 `elegant_toc_post_types` 过滤器文档
- 提供完整的单元测试示例
- 添加开发环境配置说明

### 1.6.1 (2026-07-30)
- 新增桌面端左侧悬浮侧边栏布局
- 新增移动端底部浮层面板与缩放动画
- 新增 7 种色彩主题与深色模式支持
- 新增后台设置页实时预览
- 新增目录字号与文章正文同步
- 新增短代码 `[elegant_toc]`
- 新增单篇文章禁用目录功能
- 新增 `elegant_toc_post_types` 过滤器
- 优化设置页 UI 与交互体验

## 📄 许可证

本插件使用 GPL v2 或更高版本许可证。

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 🔗 项目主页

https://github.com/Jacky088/Elegant-TOC

---

为 WordPress 博客带来更优雅的阅读体验 ✨
