/**
 * Hero Inner Block Script
 * Initializes fluid blob animations and mouse movement tracking
 */
(function () {
  'use strict';

  function init() {
    if (typeof window.initHeroBlobs === 'function') {
      window.initHeroBlobs();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  if (window.acf) {
    window.acf.addAction('render_block_preview/type=hero-inner', function () {
      var hero = document.querySelectorAll('.hero-inner');
      hero.forEach(function (h) {
        delete h.dataset.mmBlobsInit;
      });
      init();
    });
  }
})();
