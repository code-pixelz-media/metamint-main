/**
 * Single Blog Content - Table of Contents interaction
 */

document.addEventListener('DOMContentLoaded', function () {
  const tocLinks = document.querySelectorAll('.mm-toc-link');
  if (!tocLinks.length) return;

  // Smooth scroll to heading when TOC link is clicked
  tocLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId && targetId.startsWith('#')) {
        const targetEl = document.querySelector(targetId);
        if (targetEl) {
          e.preventDefault();
          targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if (history.pushState) {
            history.pushState(null, null, targetId);
          } else {
            location.hash = targetId;
          }
        }
      }
    });
  });

  // IntersectionObserver to highlight active TOC heading on scroll
  const headings = document.querySelectorAll('.entry-content h2[id], .entry-content h3[id]');
  if (!headings.length || !('IntersectionObserver' in window)) return;

  const observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          tocLinks.forEach(function (link) {
            if (link.getAttribute('href') === '#' + id) {
              link.classList.add('is-active');
            } else {
              link.classList.remove('is-active');
            }
          });
        }
      });
    },
    { rootMargin: '-80px 0px -65% 0px' }
  );

  headings.forEach(function (heading) {
    observer.observe(heading);
  });
});
