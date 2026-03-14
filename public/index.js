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

    function setupFiltering(directory) {
        var input = directory.querySelector('[data-filter-input]');
        var count = directory.querySelector('[data-results-count]');
        var cards = Array.from(directory.querySelectorAll('[data-post-card]'));

        if (!input || !count || cards.length === 0) {
            return;
        }

        function applyFilter() {
            var query = input.value.toLowerCase().trim();
            var visibleCount = 0;

            cards.forEach(function (card) {
                var title = card.getAttribute('data-title') || '';
                var tags = card.getAttribute('data-tags') || '';
                var matches = query === '' || title.indexOf(query) !== -1 || tags.indexOf(query) !== -1;

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

        input.addEventListener('input', applyFilter);
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

    document.addEventListener('DOMContentLoaded', function () {
        setupExpanding();
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
            setupFiltering(directory);
        });

        window.addEventListener('resize', refreshExpandedCardHeight);
    });
})();
