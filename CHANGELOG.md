# Changelog

All notable changes to Elegant TOC will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.7.0] - 2026-07-31

### Added
- **Performance**: Minified CSS/JS assets with automatic production switching (30%+ size reduction)
- **Performance**: Options caching mechanism to reduce database queries
- **Feature**: Real-time TOC search/filter functionality
- **Feature**: Custom CSS option for deep customization
- **Feature**: Keyboard shortcuts (T key to toggle, Esc to close mobile panel)
- **Feature**: Loading state indicator
- **Security**: Enhanced input validation and permission checks
- **Security**: CSS sanitization to prevent XSS attacks
- **Developer**: Modular code structure (class-frontend.php, class-admin.php)
- **Developer**: Complete unit test framework with PHPUnit
- **Developer**: Composer support with autoloading
- **Developer**: Translation template (.pot file)
- **Developer**: Code quality tools (PHPCS, PHPCBF)

### Changed
- **Architecture**: Refactored main class into modular components
- **Code Quality**: Improved error handling throughout JavaScript
- **Code Quality**: Added comprehensive inline documentation
- **Compatibility**: Minimum PHP version raised to 7.4
- **Compatibility**: Added proper WordPress version requirements

### Fixed
- JavaScript error handling in all major functions
- Potential security vulnerabilities in CSS input
- Memory optimization with options caching

### Security
- Enhanced nonce verification in admin forms
- Capability checks in all admin functions
- CSS sanitization against malicious code injection
- Removed potential XSS vectors

## [1.6.1] - 2026-07-30

### Added
- Desktop sidebar layout with content-aware positioning
- Mobile bottom panel with scale animation
- 7 color themes (auto, light, blue, green, purple, orange, dark)
- System dark mode support
- Real-time theme preview in settings page
- Font size synchronization with article content
- `[elegant_toc]` shortcode support
- Per-post TOC disable option via meta box
- `elegant_toc_post_types` filter hook
- Custom field support for disabling TOC (`disable_toc = 1`)

### Changed
- Completely redesigned UI with modern aesthetics
- Improved responsive behavior
- Enhanced accessibility features
- Optimized settings page layout

### Fixed
- Smooth scrolling offset calculation
- Active item highlight accuracy
- Mobile panel z-index issues

## [1.6.0] - 2026-07-15

### Added
- Adaptive layout for different screen sizes
- Collapse/expand state persistence
- Smooth scroll animation
- Active heading highlight

### Changed
- Improved CSS architecture with CSS variables
- Better performance with throttle and debounce
- Enhanced accessibility with ARIA attributes

## [1.5.0] - 2026-06-01

### Added
- Initial public release
- Basic TOC generation from H2-H6 headings
- Click-to-scroll functionality
- Simple styling options

---

## Upgrade Notes

### Upgrading to 1.7.0
- **No breaking changes** - This is a feature and quality release
- **Automatic asset switching** - Minified files load automatically in production
- **New features** - Search, custom CSS, and keyboard shortcuts are ready to use
- **Performance boost** - Options caching improves page load time
- **Composer (optional)** - Run `composer install` if using development tools

### Upgrading to 1.6.1
- Clear your browser cache to see the new design
- Check theme compatibility if using a custom theme
- Review color theme options in settings

---

For more information, visit: https://github.com/Jacky088/Elegant-TOC
