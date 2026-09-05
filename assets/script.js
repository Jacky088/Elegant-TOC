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
        // 默认静默；控制台执行 window.elegantTocDebug = true 可开启调试日志
        if (!window.elegantTocDebug) return;
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
    function persistCollapseState(toc) {
        try {
            window.localStorage.setItem(
                'elegant_toc_collapsed',
                toc.classList.contains('collapsed') ? '1' : '0'
            );
        } catch (e) {}
    }

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
            persistCollapseState(toc);
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
    }

    /* ---------- 平滑滚动 ---------- */
    // 偏移量缓存：getComputedStyle / getBoundingClientRect 会触发布局重排，
    // 滚动期间频繁调用开销大，故缓存结果，仅在 resize 时失效重算。
    var cachedOffset = null;

    function getOffset() {
        if (cachedOffset !== null) {
            return cachedOffset;
        }
        var offset = 100;

        // 开发者可通过 elegant_toc_scroll_offset 过滤器输出的 data-et-offset 强制指定基础偏移（px）
        var nav = document.getElementById('elegant-toc');
        var forced = nav ? parseInt(nav.getAttribute('data-et-offset'), 10) : 0;
        if (forced > 0) {
            offset = forced;
        }

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

        cachedOffset = offset;
        document.documentElement.style.setProperty('--et-scroll-offset', (offset / 16) + 'rem');
        return offset;
    }

    function invalidateOffset() {
        cachedOffset = null;
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

            // 尊重系统「减弱动态效果」设置
            var prefersReduced = window.matchMedia
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({
                top: top,
                behavior: prefersReduced ? 'auto' : 'smooth'
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
        var currentLink = null;

        function updateActive() {
            var active = null;
            var lastAbove = null;
            var viewportTop = offset;

            // 当前阅读标题 = 最后一个"顶边已越过视口顶线(offset)"的标题。
            // 以此为准（而非"整屏内第一个可见"），避免标题刚在屏幕底部露头就被提前高亮，
            // 从而保证"浏览到标题 N 就高亮 N、回到标题 M 就高亮 M"与页面位置严格一致。
            for (var i = 0; i < targets.length; i++) {
                var t = targets[i];
                var rect = t.el.getBoundingClientRect();
                if (rect.top <= viewportTop) {
                    lastAbove = t;
                }
            }

            active = lastAbove || targets[0];
            if (!active) return;

            // 仅在活动项变化时切换类，避免滚动中每帧对所有链接增删 class
            if (active.link !== currentLink) {
                if (currentLink) {
                    currentLink.classList.remove('active');
                    currentLink.classList.remove('et-top-highlight');
                }
                currentLink = active.link;
                currentLink.classList.add('active');
            }

            var isTopCue = active === targets[0] && window.pageYOffset <= offset + 20;
            if (isTopCue) {
                active.link.classList.add('et-top-highlight');
            } else {
                active.link.classList.remove('et-top-highlight');
            }

            var list = panel.querySelector('.elegant-toc-list');
            if (list) {
                var linkTop = active.link.offsetTop;
                var listHeight = list.clientHeight;
                var linkHeight = active.link.clientHeight;
                var scrollTop = list.scrollTop;
                var nearPageTop = window.pageYOffset <= offset + 20;

                if (active === targets[0] || nearPageTop) {
                    // 回到页面顶部时，目录面板自动回到首项
                    list.scrollTop = 0;
                } else if (linkTop < scrollTop) {
                    list.scrollTop = linkTop - 10;
                } else if (linkTop + linkHeight > scrollTop + listHeight) {
                    list.scrollTop = linkTop + linkHeight - listHeight + 10;
                }
            }
        }

        window.addEventListener('scroll', throttle(updateActive, 50), { passive: true });
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
            // 移动端面板使用百分比宽度（calc），无需锁定像素宽度；
            // 锁定会在动画结束后解锁，导致从像素回归百分比的瞬间跳变（闪动）。
            // 只有侧边栏（固定像素宽度）才需要锁定逻辑。
            if (!toc.classList.contains('elegant-toc--sidebar')) {
                unlockPanelWidth(panel);
            }
            panel.classList.add('et-open');
        }
        if (typeof toc._etMobilePanelScrollHandler === 'function' && !toc._etMobilePanelScrolling) {
            window.addEventListener('scroll', toc._etMobilePanelScrollHandler, { passive: true });
            toc._etMobilePanelScrolling = true;
        }
        var trigger = toc.querySelector('.elegant-toc-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }

    function closeMobilePanel(toc) {
        var panel = toc.querySelector('.elegant-toc-panel');
        if (panel) {
            // 移动端面板为百分比宽度，不锁定像素宽度（避免解锁时的瞬间跳变）；
            // 仅在侧边栏模式下才需要锁定逻辑。
            if (toc.classList.contains('elegant-toc--sidebar')) {
                lockPanelWidth(panel);
            }
            panel.classList.remove('et-open');
            panel.classList.add('et-closing');
            // visibility 会在 transitionend 回调中隐藏，避免内容闪烁
        }
        if (typeof toc._etMobilePanelScrollHandler === 'function' && toc._etMobilePanelScrolling) {
            window.removeEventListener('scroll', toc._etMobilePanelScrollHandler, { passive: true });
            toc._etMobilePanelScrolling = false;
        }
        // 关闭瞬间即可移除 mobile-open：宽度跳变已由 CSS 的 .et-closing 宽度规则兜底，
        // 不再依赖延迟移除。这样 et-closing 的 opacity:0 不会被 mobile-open 的
        // opacity:1 覆盖，关闭淡出动画才能立即、流畅地播放。
        toc.classList.remove('elegant-toc--mobile-open');
        var trigger = toc.querySelector('.elegant-toc-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function updateMobilePanelHeight(toc) {
        var contentContainer = toc.parentElement;
        if (!contentContainer) return;

        var contentRect = contentContainer.getBoundingClientRect();

        // 自动收起：移动端面板打开时，若文章底部已滚出视口顶部，则收起回按钮态。
        // 仅对移动端浮层面板生效（mobile-open 且非 sidebar），避免影响 PC 侧边栏的显隐逻辑。
        if (contentRect.bottom <= 0
            && toc.classList.contains('elegant-toc--mobile-open')
            && !toc.classList.contains('elegant-toc--sidebar')) {
            closeMobilePanel(toc);
            return;
        }

        var contentBottom = contentRect.bottom;
        var bottomSafeGap = 20;
        var panelBottomOffset = 88; // fixed mobile panel bottom distance

        // 尾部边界：移动版目录面板高度应以视口高度为下限，避免滑动到文章底部时过度收缩。
        var effectiveBottom = Math.max(contentBottom, window.innerHeight);
        var availableHeight = Math.max(0, effectiveBottom - panelBottomOffset - bottomSafeGap);
        var viewportLimit = Math.max(0, window.innerHeight * 0.7);
        var panelMaxHeight = Math.min(availableHeight, viewportLimit);
        // 仅写入 CSS 变量，由 .elegant-toc--mobile-open .elegant-toc-panel 的
        // max-height: min(var(--et-mobile-panel-max-height, 70vh), 70vh) 统一消费。
        // 不再额外写内联 maxHeight：内联优先级高于 CSS 规则会令变量失效，
        // 且关闭时内联值不会被清除，会留下脏状态。
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

        // Esc 关闭移动端面板并把焦点还给触发按钮，保证键盘用户可以退出浮层
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' && e.key !== 'Esc') return;
            if (!toc.classList.contains('elegant-toc--mobile-open')) return;
            closeMobilePanel(toc);
            var trigger = toc.querySelector('.elegant-toc-trigger');
            if (trigger && toc.contains(document.activeElement)) {
                trigger.focus();
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
        // 用 clientWidth 与 CSS @media 视口同口径（不含滚动条），
        // 避免 innerWidth 含滚动条导致 1009~1023px 区间 JS/CSS 模式错位
        if (document.documentElement.clientWidth < minViewport) {
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

        // 目录顶部与正文第一个内容元素顶端对齐，并随滚动同步。
        // 注意：偏移量必须与 updateSidebarVisibility 中的 headerOffset + 20 保持一致，
        // 否则激活侧边栏后第一次滚动会造成目录面板瞬间上/下跳 10px（跟随起点不一致）。
        var tocTop = Math.max(refRect.top, headerOffset + 20);
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

        // 先完成全部读取，再统一写入：写入后读面板高度会强制同步重排，
        // 滚动帧内每次触发都会卡顿（读到的面板高度滞后一帧，48px 阈值足以吸收误差）
        var headerOffset = getOffset();
        var refRect = refEl.getBoundingClientRect();
        var contentRect = contentContainer.getBoundingClientRect();
        var inView = contentRect.bottom > 0 && contentRect.top < window.innerHeight;

        var panel = toc.querySelector('.elegant-toc-panel');
        var panelHeight = (panel && inView) ? panel.getBoundingClientRect().height : 0;

        var tocTop = Math.max(refRect.top, headerOffset + 20);
        var contentBottom = contentRect.bottom;
        var sidebarMaxHeight = Math.max(0, (contentBottom - 20) - tocTop);

        var tooCloseToBottom = inView && (tocTop + panelHeight) > contentBottom - 48; // 距文章底部 48px 开始淡出

        // 统一写入
        toc.style.setProperty('--et-sidebar-top', tocTop + 'px');
        toc.style.setProperty('--et-sidebar-max-height', sidebarMaxHeight + 'px');

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
            // 滚动中仅做必要的实时更新；移动端面板高度在滚动中不变，
            // 放到滚动停止后（scrollEnd）再重算，避免每帧强制 reflow 造成卡顿。
            var scrollPending = false;
            var onScroll = function () {
                if (scrollPending) return;
                scrollPending = true;
                window.requestAnimationFrame(function () {
                    scrollPending = false;
                    // 侧边栏模式需要随滚动同步顶部/尾部边界
                    updateSidebarVisibility(toc, contentContainer);
                });
            };

            // 滚动停止后补算移动端面板高度（滑到文章尾部时的收缩需求）
            var onScrollEnd = debounce(function () {
                updateMobilePanelHeight(toc);
            }, 120);
            toc._etMobilePanelScrollHandler = onScrollEnd;
            toc._etMobilePanelScrolling = false;

            // 统一的 resize 调度：重判模式 → 重算位置 → 更新可见性/高度。
            // 合并原先 boot 内（仅更新可见性/高度）与文件底部（debounce 150 才重判模式）
            // 两个 handler，消除跨 1024px 断点时 80ms/150ms 两节奏打架造成的瞬时错位。
            var onResize = throttle(function () {
                invalidateOffset();
                syncBaseFontSize(toc);
                positionSidebar(toc);
                updateMobilePanelHeight(toc);
            }, 80);

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('scroll', onScrollEnd, { passive: true });
            window.addEventListener('resize', onResize, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    window.addEventListener('load', boot);
})();
