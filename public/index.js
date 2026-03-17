(function () {
    var expandedCard = null;

    function getExpandable(card) {
        return card.querySelector('[data-expandable]');
    }

    function getToggle(card) {
        return card.querySelector('[data-expand-toggle]');
    }

    function collapseCard(card) {
        var expandable = getExpandable(card);
        var toggle = getToggle(card);
        if (!expandable || !toggle) {
            return;
        }

        card.classList.remove('is-expanded');
        toggle.setAttribute('aria-expanded', 'false');
        expandable.style.maxHeight = '0px';
    }

    function expandCard(card) {
        var expandable = getExpandable(card);
        var toggle = getToggle(card);
        if (!expandable || !toggle) {
            return;
        }

        card.classList.add('is-expanded');
        toggle.setAttribute('aria-expanded', 'true');
        expandable.style.maxHeight = expandable.scrollHeight + 'px';
    }

    function toggleCard(card) {
        if (expandedCard === card) {
            collapseCard(card);
            expandedCard = null;
            return;
        }

        if (expandedCard) {
            collapseCard(expandedCard);
        }

        expandCard(card);
        expandedCard = card;
    }

    function setupExpanding() {
        var cards = document.querySelectorAll('[data-post-card]');
        cards.forEach(function (card) {
            var toggle = getToggle(card);
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', function () {
                toggleCard(card);
            });
        });
    }

    function setupTagFilter(directory) {
        var filterBar = directory.querySelector('[data-tag-filter-bar]');
        var count = directory.querySelector('[data-results-count]');
        var cards = Array.from(directory.querySelectorAll('[data-post-card]'));

        if (!filterBar || !count || cards.length === 0) {
            return;
        }

        var activeTag = 'all';

        function applyFilter() {
            var visibleCount = 0;

            cards.forEach(function (card) {
                var tags = (card.getAttribute('data-tags') || '').split(' ').filter(Boolean);
                var matches = activeTag === 'all' || tags.indexOf(activeTag) !== -1;

                card.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                } else if (expandedCard === card) {
                    collapseCard(card);
                    expandedCard = null;
                }
            });

            count.textContent = visibleCount;
        }

        filterBar.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-tag-filter]');
            if (!btn) {
                return;
            }

            activeTag = btn.getAttribute('data-tag-filter');

            filterBar.querySelectorAll('[data-tag-filter]').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
            });

            applyFilter();
        });
    }

    function refreshExpandedCardHeight() {
        if (!expandedCard) {
            return;
        }

        var expandable = getExpandable(expandedCard);
        if (!expandable) {
            return;
        }

        expandable.style.maxHeight = expandable.scrollHeight + 'px';
    }

    function setupSubscribeModal() {
        var btn = document.querySelector('[data-subscribe-btn]');
        var modal = document.querySelector('[data-subscribe-modal]');
        var backdrop = document.querySelector('[data-subscribe-backdrop]');
        var close = document.querySelector('[data-subscribe-close]');

        if (!btn || !modal) {
            return;
        }

        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            var input = modal.querySelector('.subscribe-form__input');
            if (input) {
                input.focus();
            }
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            btn.focus();
        }

        btn.addEventListener('click', openModal);

        if (close) {
            close.addEventListener('click', closeModal);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeModal);
        }

        return closeModal;
    }

    function setupReadingProgress() {
        var bar = document.querySelector('[data-reading-progress-bar]');
        var post = document.querySelector('[data-post]');

        if (!bar || !post) {
            return;
        }

        function updateProgress() {
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var postTop = post.offsetTop;
            var postBottom = postTop + post.offsetHeight;
            var viewportHeight = window.innerHeight;
            var start = postTop;
            var end = postBottom - viewportHeight;
            var progress = end > start ? Math.min(100, Math.max(0, ((scrollTop - start) / (end - start)) * 100)) : 0;
            bar.style.width = progress + '%';
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    function setupThemeToggle() {
        var btn = document.querySelector('[data-theme-toggle]');
        if (!btn) {
            return;
        }

        function getStoredTheme() {
            try {
                return localStorage.getItem('theme') || 'dark';
            } catch (e) {
                return 'dark';
            }
        }

        function storeTheme(theme) {
            try {
                localStorage.setItem('theme', theme);
            } catch (e) {}
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            if (theme === 'dark') {
                btn.textContent = 'light';
                btn.setAttribute('aria-label', 'switch to light mode');
            } else {
                btn.textContent = 'dark';
                btn.setAttribute('aria-label', 'switch to dark mode');
            }
        }

        applyTheme(getStoredTheme());

        btn.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            storeTheme(next);
            applyTheme(next);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupExpanding();
        setupThemeToggle();
        setupReadingProgress();
        var closeModal = setupSubscribeModal();

        if (closeModal) {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        }

        var directories = document.querySelectorAll('[data-post-directory]');
        directories.forEach(function (directory) {
            setupTagFilter(directory);
        });

        window.addEventListener('resize', refreshExpandedCardHeight);
    });
})();
