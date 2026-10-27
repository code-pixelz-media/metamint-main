/**
 * Comparison Block - Sticky sidebar navigation & active scroll tracking
 */

(function () {
  function initComparisonBlocks() {
    const comparisonSections = document.querySelectorAll('.mm-comparison-section');

    comparisonSections.forEach(function (section) {
      if (section.dataset.comparisonInitialized === 'true') return;
      section.dataset.comparisonInitialized = 'true';

      const navLinks = section.querySelectorAll('.mm-comparison-nav-link');
      const items = section.querySelectorAll('.mm-comparison-item[id]');

      if (!navLinks.length || !items.length) return;

      // Set initial active state on first link if none active
      let hasActive = false;
      navLinks.forEach(function (link) {
        if (link.classList.contains('is-active')) hasActive = true;
      });
      if (!hasActive && navLinks.length > 0) {
        navLinks[0].classList.add('is-active');
      }

      // Smooth scroll on navigation link click
      navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
          const targetId = this.getAttribute('href');
          if (targetId && targetId.startsWith('#')) {
            const targetEl = section.querySelector(targetId);
            if (targetEl) {
              e.preventDefault();
              targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

              navLinks.forEach(function (l) {
                l.classList.remove('is-active');
              });
              link.classList.add('is-active');

              if (window.history && window.history.pushState) {
                window.history.pushState(null, null, targetId);
              }
            }
          }
        });
      });

      // IntersectionObserver for tracking active section while scrolling
      if ('IntersectionObserver' in window) {
        const observerOptions = {
          root: null,
          rootMargin: '-100px 0px -60% 0px',
          threshold: 0
        };

        const observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              const currentId = '#' + entry.target.getAttribute('id');
              navLinks.forEach(function (link) {
                if (link.getAttribute('href') === currentId) {
                  link.classList.add('is-active');
                } else {
                  link.classList.remove('is-active');
                }
              });
            }
          });
        }, observerOptions);

        items.forEach(function (item) {
          observer.observe(item);
        });
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initComparisonBlocks);
  } else {
    initComparisonBlocks();
  }

  // Support ACF block re-renders in Gutenberg editor
  if (window.acf) {
    window.acf.addAction('render_block_preview/type=comparison-block', function () {
      const comparisonSections = document.querySelectorAll('.mm-comparison-section');
      comparisonSections.forEach(function (s) {
        delete s.dataset.comparisonInitialized;
      });
      initComparisonBlocks();
    });
  }
})();
