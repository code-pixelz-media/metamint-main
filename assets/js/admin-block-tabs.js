(function ($) {
  'use strict';

  if (document.getElementById('acf-field-group-fields')) return;
  if (typeof acf === 'undefined' || typeof acf.doAction !== 'function') return;

  function triggerAppend($container) {
    if ($container.data('mm-acf-appended')) return;
    $container.data('mm-acf-appended', true);
    setTimeout(function () {
      acf.doAction('append', $container);
    }, 30);
  }

  function scanSidebar() {
    $('.acf-block-fields, .acf-fields.-top').each(function () {
      triggerAppend($(this));
    });
  }

  function attachObserver() {
    var sidebar =
      document.querySelector('.interface-interface-skeleton__sidebar') ||
      document.querySelector('.edit-post-sidebar') ||
      document.querySelector('#editor');

    if (!sidebar) {
      setTimeout(attachObserver, 400);
      return;
    }

    new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i++) {
        if (mutations[i].addedNodes.length) { scanSidebar(); break; }
      }
    }).observe(sidebar, { childList: true, subtree: true });

    scanSidebar();
  }

  $(document).ready(function () {
    setTimeout(attachObserver, 200);
  });

}(jQuery));
