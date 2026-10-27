/**
 * Metamint Hero Default Block Script
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
    window.acf.addAction('render_block_preview/type=metamint-hero-default', function () {
      var hero = document.querySelectorAll('.block-hero-banner:not(.hero-inner)');
      hero.forEach(function (h) {
        delete h.dataset.mmBlobsInit;
      });
      init();
    });
  }
})();
