(function () {
    'use strict';

    /* ---------- 辅助函数 ---------- */
    function throttle(fn, limit) {
        var last = 0;
        return function () {
            var now = Date.now();
            if (now - last >= limit) {
                last = now;
                fn.apply(this, arguments);
            }
        };
    }

    function debounce(fn, wait) {
        var t = null;
        return function () {
            var ctx = this, args = arguments;
            if (t) clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    function logDebug(message) {
        if (window.console && window.console.log) {
            console.log('[Elegant TOC]', message);
        }
    }

    /* ---------- Panel width lock helpers (prevent width jump during animation) ---------- */
    function lockPanelWidth(panel) {
        if (!panel) return;
        try {
            var w = panel.getBoundingClientRect().width;
            var px = Math.round(w) + 'px';
            panel.style.width = px;
            panel.style.minWidth = px;
            panel.style.maxWidth = px;
            // 临时隐藏溢出（避免出现或消失的滚动条改变宽度）
            panel.style.overflow = 'hidden';
        } catch (e) {}
    }

    function unlockPanelWidth(panel) {
        if (!panel) return;
        try {
            panel.style.width = '';
            panel.style.minWidth = '';
            panel.style.maxWidth = '';
            panel.style.overflow = '';
        } catch (e) {}
    }

    /* ---------- 字号同步：让目录字号跟随文章正文 ---------- */
    function syncBaseFontSize(toc) {
        var contentContainer = toc.parentElement;
        if (!contentContainer) return;

        try {
            var baseSize = window.getComputedStyle(contentContainer).fontSize;
            if (baseSize && baseSize !== '0px') {
                toc.style.setProperty('--et-base-font-size', baseSize);
                logDebug('sync base font-size: ' + baseSize);
            }
        } catch (e) {
            logDebug('failed to sync base font-size');
        }
    }

    /* ---------- 折叠/展开 ---------- */
    function initToggle(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (!panel) return;
        var toggle = panel.querySelector('.elegant-toc-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', function () {
            var isCollapsed = toc.classList.contains('collapsed');
            if (isCollapsed) {
                toc.classList.remove('collapsed');
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                toc.classList.add('collapsed');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function initCollapseState(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (!panel) return;
        var toggle = panel.querySelector('.elegant-toc-toggle');
        if (!toggle) return;

        try {
            var stored = window.localStorage.getItem('elegant_toc_collapsed');
            if (stored === '1') {
                toc.classList.add('collapsed');
                toggle.setAttribute('aria-expanded', 'false');
            }
        } catch (e) {}

        // 监听折叠状态变化并持久化
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                if (m.attributeName === 'class') {
                    try {
                        window.localStorage.setItem(
                            'elegant_toc_collapsed',
                            toc.classList.contains('collapsed') ? '1' : '0'
                        );
                    } catch (e) {}
                }
            });
        }).observe(toc, { attributes: true, attributeFilter: ['class'] });
    }

    /* ---------- 平滑滚动 ---------- */
    function getOffset() {
        var offset = 100;
        var adminbar = document.getElementById('wpadminbar');
        var header = document.querySelector(
            '.site-header, .site-header-inner, header[role="banner"], .sticky-header, #masthead, .main-header, .header-fixed, .site-navigation, .main-navigation'
        );

        if (adminbar) {
            offset = Math.max(offset, adminbar.offsetHeight + 20);
        }
        if (header) {
            var style = window.getComputedStyle(header);
            var isStickyOrFixed = (style.position === 'fixed' || style.position === 'sticky');
            if (isStickyOrFixed) {
                offset = Math.max(offset, header.getBoundingClientRect().height + 20);
            }
        }

        document.documentElement.style.setProperty('--et-scroll-offset', (offset / 16) + 'rem');
        return offset;
    }

    function initSmoothScroll(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (!panel) return;

        panel.addEventListener('click', function (e) {
            var link = e.target.closest('.elegant-toc-link');
            if (!link) return;

            var href = link.getAttribute('href');
            if (!href || href.charAt(0) !== '#') return;

            var id = href.slice(1);
            var target = document.getElementById(id);
            if (!target) return;

            e.preventDefault();
            var offset = getOffset();
            var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });

            // 高亮闪烁目标
            target.classList.add('elegant-toc-target-flash');
            setTimeout(function () {
                target.classList.remove('elegant-toc-target-flash');
            }, 1000);
        });
    }

    /* ---------- 滚动高亮 ---------- */
    function initActiveHighlight(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (!panel) return;
        var links = panel.querySelectorAll('.elegant-toc-link');
        if (!links.length) return;

        var targets = [];
        links.forEach(function (link) {
            var id = link.getAttribute('href').slice(1);
            var el = document.getElementById(id);
            if (el) targets.push({ link: link, el: el });
        });
        if (!targets.length) return;

        var offset = getOffset();

        function updateActive() {
            var scrollPos = window.pageYOffset + offset + 50;
            var active = null;

            for (var i = 0; i < targets.length; i++) {
                var t = targets[i];
                var top = t.el.getBoundingClientRect().top + window.pageYOffset;
                if (top <= scrollPos) {
                    active = t;
                } else {
                    break;
                }
            }

            links.forEach(function (link) { link.classList.remove('active'); });
            if (active) {
                active.link.classList.add('active');
                var list = panel.querySelector('.elegant-toc-list');
                if (list) {
                    var linkTop = active.link.offsetTop;
                    var listHeight = list.clientHeight;
                    var linkHeight = active.link.clientHeight;
                    var scrollTop = list.scrollTop;
                    if (linkTop < scrollTop) {
                        list.scrollTop = linkTop - 10;
                    } else if (linkTop + linkHeight > scrollTop + listHeight) {
                        list.scrollTop = linkTop + linkHeight - listHeight + 10;
                    }
                }
            }
        }

        window.addEventListener('scroll', throttle(updateActive, 80), { passive: true });
        window.addEventListener('resize', debounce(updateActive, 150));
        updateActive();
    }

    /* ---------- 移动端面板 ---------- */
    function openMobilePanel(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        toc.classList.add('elegant-toc--mobile-open');
        if (panel) {
            panel.style.visibility = 'visible';
            panel.classList.remove('et-closing');
            // 解除旧的宽度限制，重新计算打开时的宽度并锁定，防止关闭/打开动画中宽度跳变
            unlockPanelWidth(panel);
            void panel.offsetWidth;
            lockPanelWidth(panel);
            panel.classList.add('et-open');
        }
        var trigger = toc.querySelector('.elegant-toc-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }

    function closeMobilePanel(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (panel) {
            // 在开始关闭动画前锁定当前宽度，避免滚动条或内容变化导致宽度跳变
            lockPanelWidth(panel);
            panel.classList.remove('et-open');
            panel.classList.add('et-closing');
            // visibility 会在 transitionend 回调中隐藏，避免内容闪烁；解锁在 transitionend 中处理
        }
        toc.classList.remove('elegant-toc--mobile-open');
        var trigger = toc.querySelector('.elegant-toc-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function updateMobilePanelHeight(toc) {
        var contentContainer = toc.parentElement;
        if (!contentContainer) return;

        var contentRect = contentContainer.getBoundingClientRect();
        var contentBottom = contentRect.bottom;
        var bottomSafeGap = 20;
        var panelBottomOffset = 88; // fixed mobile panel bottom distance

        // 尾部边界：移动版目录面板高度只依据文章容器底部，同时限制最大高度防止铺满整个页面
        var availableHeight = Math.max(0, contentBottom - panelBottomOffset - bottomSafeGap);
        var viewportLimit = Math.max(0, window.innerHeight * 0.7);
        var panelMaxHeight = Math.min(availableHeight, viewportLimit);
        toc.style.setProperty('--et-mobile-panel-max-height', panelMaxHeight + 'px');
    }

    function initMobilePanel(toc) {
        var trigger = toc.querySelector('.elegant-toc-trigger');
        var closeBtn = toc.querySelector('.elegant-toc-mobile-close');
        var panel = toc.querySelector('.elegant-toc-panel');

        if (trigger) {
            trigger.addEventListener('click', function () {
                updateMobilePanelHeight(toc);
                openMobilePanel(toc);
            });
            // 确保悬浮提示属性存在（修复提示文字消失问题）
            if (!trigger.getAttribute('data-tooltip')) {
                var tt = trigger.getAttribute('title') || trigger.getAttribute('aria-label') || '';
                if (tt) trigger.setAttribute('data-tooltip', tt);
            }
            // 焦点可见时也显示 tooltip，提升可访问性
            trigger.addEventListener('focus', function () { trigger.classList.add('et-tooltip-visible'); });
            trigger.addEventListener('blur', function () { trigger.classList.remove('et-tooltip-visible'); });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeMobilePanel(toc);
            });
        }

        // 点击面板外部关闭
        document.addEventListener('click', function (e) {
            if (!toc.classList.contains('elegant-toc--mobile-open')) return;
            if (!toc.contains(e.target)) {
                closeMobilePanel(toc);
            }
        });

        // 监听 panel transitionend，清理动画类并隐藏
        if (panel) {
            panel.addEventListener('transitionend', function (e) {
                if (e.propertyName !== 'opacity' && e.propertyName !== 'transform') return;
                // 关闭完成：隐藏并解锁宽度
                if (panel.classList.contains('et-closing')) {
                    panel.classList.remove('et-closing');
                    panel.style.visibility = 'hidden';
                    unlockPanelWidth(panel);
                } else if (panel.classList.contains('et-open')) {
                    // 打开完成：解锁宽度以允许响应式变化
                    unlockPanelWidth(panel);
                }
            });
        }
    }

    /* ---------- 桌面端侧边栏定位 ---------- */
    function positionSidebar(toc) {
        var contentContainer = toc.parentElement;
        if (!contentContainer) return;

        var minViewport = 1024;
        var tocWidth = 240;
        var gap = 36;
        var minLeftSpace = 10;

        // 小屏直接走移动端触发按钮模式
        if (window.innerWidth < minViewport) {
            logDebug('viewport too small: ' + window.innerWidth + ' < ' + minViewport);
            resetSidebar(toc);
            return;
        }

        // 以正文第一个实际内容元素为基准，判断正文左侧是否有足够空白
        var refEl = toc.nextElementSibling || contentContainer;
        if (!refEl) return;
        var refRect = refEl.getBoundingClientRect();
        var contentRect = contentContainer.getBoundingClientRect();
        var availableLeft = refRect.left;

        // 只要正文内容左侧能放下目录本身 + 间隙，就启用侧边栏
        if (availableLeft < tocWidth + gap + minLeftSpace) {
            logDebug('left space insufficient: ' + availableLeft + ' < ' + (tocWidth + gap + minLeftSpace));
            resetSidebar(toc);
            return;
        }

        getOffset(); // 确保 --et-scroll-offset 已设置
        logDebug('activating sidebar, ref left: ' + availableLeft);

        var headerOffset = getOffset();

        // 目录顶部与正文第一个内容元素顶端对齐，并随滚动同步
        var tocTop = Math.max(refRect.top, headerOffset + 30);
        var tocLeft = Math.max(minLeftSpace, refRect.left - tocWidth - gap);

        // 尾部边界：目录面板高度只依据文章容器底部，不以整个浏览器页面为准
        var contentBottom = contentRect.bottom;
        var panelBottomLimit = contentBottom - 20;
        var sidebarMaxHeight = Math.max(0, panelBottomLimit - tocTop);

        // 进入桌面模式前关闭移动端面板，防止关闭按钮残留到 PC 端
        closeMobilePanel(toc);

        toc.classList.add('elegant-toc--sidebar');
        toc.style.setProperty('--et-sidebar-top', tocTop + 'px');
        toc.style.setProperty('--et-sidebar-left', tocLeft + 'px');
        toc.style.setProperty('--et-sidebar-max-height', sidebarMaxHeight + 'px');

        // 保证侧边栏面板可见并使用统一的动画类
        var panel = toc.querySelector('.elegant-toc-panel');
        if (panel) {
            panel.style.visibility = 'visible';
            panel.classList.remove('et-closing');
            // 计算并锁定打开时的宽度，避免在动画过程中出现宽度跳变
            unlockPanelWidth(panel);
            void panel.offsetWidth;
            lockPanelWidth(panel);
            panel.classList.add('et-open');
        }

        updateSidebarVisibility(toc, contentContainer);
    }

    function resetSidebar(toc) {
        toc.classList.remove('elegant-toc--sidebar', 'elegant-toc--hidden');
        toc.style.removeProperty('--et-sidebar-top');
        toc.style.removeProperty('--et-sidebar-left');
        // 回到移动端模式时关闭已打开的面板，避免状态污染
        closeMobilePanel(toc);
    }

    function updateSidebarVisibility(toc, contentContainer) {
        if (!toc.classList.contains('elegant-toc--sidebar')) return;
        var refEl = toc.nextElementSibling || contentContainer;
        if (!refEl) return;
        var refRect = refEl.getBoundingClientRect();
        var contentRect = contentContainer.getBoundingClientRect();
        var inView = contentRect.bottom > 0 && contentRect.top < window.innerHeight;

        // 滚动过程中同步目录顶部与正文可见区域顶端，保持"紧贴正文"
        var headerOffset = getOffset();
        var tocTop = Math.max(refRect.top, headerOffset + 20);
        toc.style.setProperty('--et-sidebar-top', tocTop + 'px');

        // 尾部边界更新：保持目录面板只在文章容器范围内可见
        var contentBottom = contentRect.bottom;
        var panelBottomLimit = contentBottom - 20;
        var sidebarMaxHeight = Math.max(0, panelBottomLimit - tocTop);
        toc.style.setProperty('--et-sidebar-max-height', sidebarMaxHeight + 'px');

        var panel = toc.querySelector('.elegant-toc-panel');
        var panelHeight = panel ? panel.getBoundingClientRect().height : 0;
        var panelBottom = tocTop + panelHeight;
        var prematureHideThreshold = 48; // 距离文章底部多少像素开始淡出
        var tooCloseToBottom = panelBottom > contentBottom - prematureHideThreshold;

        if (!inView || tooCloseToBottom) {
            toc.classList.add('elegant-toc--hidden');
        } else {
            toc.classList.remove('elegant-toc--hidden');
        }
    }

    /* ---------- 启动 ---------- */
    function boot() {
        var toc = document.getElementById('elegant-toc');
        if (!toc || toc.getAttribute('data-et-booted') === '1') return;
        toc.setAttribute('data-et-booted', '1');

        initMobilePanel(toc);
        initToggle(toc);
        initCollapseState(toc);
        initSmoothScroll(toc);
        initActiveHighlight(toc);
        syncBaseFontSize(toc);
        positionSidebar(toc);
        updateMobilePanelHeight(toc);

        var contentContainer = toc.parentElement;
        if (contentContainer) {
            var scrollPending = false;
            var onScroll = function () {
                if (scrollPending) return;
                scrollPending = true;
                window.requestAnimationFrame(function () {
                    scrollPending = false;
                    updateSidebarVisibility(toc, contentContainer);
                });
            };

            var onResize = throttle(function () {
                updateSidebarVisibility(toc, contentContainer);
            }, 80);

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onResize, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    window.addEventListener('load', boot);
    window.addEventListener('resize', debounce(function () {
        var toc = document.getElementById('elegant-toc');
        if (toc) {
            syncBaseFontSize(toc);
            positionSidebar(toc);
            updateMobilePanelHeight(toc);
        }
    }, 150));
})();
