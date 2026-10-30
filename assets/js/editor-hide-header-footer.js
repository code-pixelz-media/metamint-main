/**
 * Hide Header and Footer template parts ONLY while editing a Page.
 * Keep them visible and editable when editing Templates or Patterns.
 */
(function () {
  if (typeof window === 'undefined' || !window.wp) return;

  const { domReady, data } = window.wp;
  if (!domReady || !data) return;

  domReady(function () {
    let lastState = null;

    function updateEditorClasses() {
      const selectEditor = data.select('core/editor');
      const selectEditPost = data.select('core/edit-post');
      const selectEditSite = data.select('core/edit-site');

      const postType = selectEditor?.getCurrentPostType ? selectEditor.getCurrentPostType() : null;

      // Check if user is in "Edit Template" mode inside the page editor
      const isEditingTemplate = Boolean(
        (selectEditPost?.isEditingTemplate && selectEditPost.isEditingTemplate()) ||
        (selectEditor?.isEditingTemplate && selectEditor.isEditingTemplate())
      );

      // In Site Editor, get the active edited post type (e.g. wp_template, wp_block, page)
      const sitePostType = selectEditSite?.getEditedPostType ? selectEditSite.getEditedPostType() : null;

      // Only hide when editing a Page and NOT editing a template or pattern
      const isTemplateOrPattern = sitePostType === 'wp_template' ||
                                  sitePostType === 'wp_block' ||
                                  sitePostType === 'wp_template_part' ||
                                  postType === 'wp_template' ||
                                  postType === 'wp_block' ||
                                  postType === 'wp_template_part';

      const shouldHide = (postType === 'page' && !isEditingTemplate && !isTemplateOrPattern);

      if (lastState === shouldHide) return;
      lastState = shouldHide;

      // Toggle on parent document
      if (document.body) {
        document.body.classList.toggle('metamint-hide-header-footer', shouldHide);
        document.body.classList.toggle('is-editing-template', isEditingTemplate);
      }

      // Toggle on editor canvas iframe if present
      syncIframe(shouldHide, isEditingTemplate);
    }

    function syncIframe(shouldHide, isEditingTemplate) {
      const iframe = document.querySelector('iframe[name="editor-canvas"]');
      if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
        iframe.contentDocument.body.classList.toggle('metamint-hide-header-footer', shouldHide);
        iframe.contentDocument.body.classList.toggle('is-editing-template', isEditingTemplate);
      }
    }

    // Subscribe to Gutenberg store state updates
    data.subscribe(updateEditorClasses);
    updateEditorClasses();

    // Check periodically for iframe re-renders or late mounts
    setInterval(function () {
      if (lastState !== null) {
        const selectEditPost = data.select('core/edit-post');
        const selectEditor = data.select('core/editor');
        const isEditingTemplate = Boolean(
          (selectEditPost?.isEditingTemplate && selectEditPost.isEditingTemplate()) ||
          (selectEditor?.isEditingTemplate && selectEditor.isEditingTemplate())
        );
        syncIframe(lastState, isEditingTemplate);
      }
    }, 600);
  });
})();

