/**
 * Metamint Block Showcase Script
 * Handles synchronized slide transitions between desktop & mobile mockups
 */
(function () {
  'use strict';

  function initBlockShowcase() {
    var showcases = document.querySelectorAll('[data-mm-showcase]');
    if (!showcases.length) return;

    showcases.forEach(function (container) {
      if (container.dataset.showcaseInit === 'true') return;
      container.dataset.showcaseInit = 'true';

      var desktopTrack = container.querySelector('[data-desktop-track]');
      var mobileTrack = container.querySelector('[data-mobile-track]');
      var desktopSlides = container.querySelectorAll('.mm-desktop-slide');
      var mobileSlides = container.querySelectorAll('.mm-mobile-slide');
      var dots = container.querySelectorAll('[data-slide-to]');
      var prevBtn = container.querySelector('[data-showcase-prev]');
      var nextBtn = container.querySelector('[data-showcase-next]');
      var stage = container.querySelector('[data-showcase-stage]');

      var total = Math.min(desktopSlides.length, mobileSlides.length);
      if (total <= 1) return;

      var currentIndex = 0;
      var autoTimer = null;
      var isTransitioning = false;

      function slideHasVideo(idx) {
        var dSlide = desktopSlides[idx];
        var mSlide = mobileSlides[idx];
        var hasD = dSlide && dSlide.querySelector('.mm-slide-video') !== null;
        var hasM = mSlide && mSlide.querySelector('.mm-slide-video') !== null;
        return hasD || hasM;
      }

      function goToSlide(newIndex) {
        if (newIndex === currentIndex || isTransitioning) return;
        isTransitioning = true;

        currentIndex = (newIndex + total) % total;
        var offsetPercent = currentIndex * 100;

        // Synchronously slide desktop and mobile tracks
        if (desktopTrack) {
          desktopTrack.style.transform = 'translate3d(-' + offsetPercent + '%, 0, 0)';
        }
        if (mobileTrack) {
          mobileTrack.style.transform = 'translate3d(-' + offsetPercent + '%, 0, 0)';
        }

        // Toggle active states on individual slides for depth zoom
        desktopSlides.forEach(function (slide, idx) {
          slide.classList.toggle('is-active', idx === currentIndex);
        });

        mobileSlides.forEach(function (slide, idx) {
          slide.classList.toggle('is-active', idx === currentIndex);
        });

        // Ensure active slide video is playing in Full HD and mute inactive slides
        [desktopSlides, mobileSlides].forEach(function (slidesList) {
          slidesList.forEach(function (slide, idx) {
            var isActive = (idx === currentIndex);
            if (isActive) {
              var iframe = slide.querySelector('iframe');
              if (iframe && iframe.contentWindow) {
                try {
                  iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: '' }), '*');
                  iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setPlaybackQuality', args: ['hd1080'] }), '*');
                } catch (e) {}
              }
              var video = slide.querySelector('video');
              if (video) {
                try {
                  video.play();
                } catch (e) {}
              }
            } else {
              // Reset sound state on inactive slides
              var soundBtn = slide.querySelector('[data-sound-toggle]');
              if (soundBtn && soundBtn.classList.contains('is-unmuted')) {
                soundBtn.classList.remove('is-unmuted');
                soundBtn.classList.add('is-muted');
                soundBtn.setAttribute('aria-label', 'Unmute video');
              }
              var inactiveIframe = slide.querySelector('iframe');
              if (inactiveIframe && inactiveIframe.contentWindow) {
                try {
                  inactiveIframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'mute', args: '' }), '*');
                } catch (e) {}
              }
              var inactiveVideo = slide.querySelector('video');
              if (inactiveVideo) {
                inactiveVideo.muted = true;
              }
            }
          });
        });

        // Update Dots
        dots.forEach(function (dot, idx) {
          dot.classList.toggle('is-active', idx === currentIndex);
        });

        // Manage auto-slide: do NOT auto-slide while a video is playing on the active slide
        if (slideHasVideo(currentIndex)) {
          stopAutoPlay();
        } else {
          startAutoPlay();
        }

        setTimeout(function () {
          isTransitioning = false;
        }, 750);
      }

      function next() {
        goToSlide(currentIndex + 1);
      }

      function prev() {
        goToSlide(currentIndex - 1);
      }

      function startAutoPlay() {
        stopAutoPlay();
        // Do not auto-slide if the active slide has a video playing
        if (slideHasVideo(currentIndex)) {
          return;
        }
        autoTimer = setInterval(next, 5000);
      }

      function stopAutoPlay() {
        if (autoTimer) {
          clearInterval(autoTimer);
          autoTimer = null;
        }
      }

      // Listen for HTML5 video completion to advance slide
      container.querySelectorAll('video').forEach(function (vid) {
        vid.addEventListener('ended', function () {
          var parentSlide = vid.closest('.mm-slide');
          var idx = parentSlide ? parseInt(parentSlide.getAttribute('data-slide-index'), 10) : -1;
          if (idx === currentIndex) {
            next();
          }
        });
      });

      // Listen for YouTube video completion to advance slide
      window.addEventListener('message', function (e) {
        if (!e.data) return;
        var msg = null;
        if (typeof e.data === 'string') {
          try {
            msg = JSON.parse(e.data);
          } catch (err) {
            return;
          }
        } else if (typeof e.data === 'object') {
          msg = e.data;
        }
        if (!msg) return;

        if (msg.event === 'onStateChange' && msg.info === 0) {
          if (slideHasVideo(currentIndex)) {
            next();
          }
        }
      });

      // Event Listeners for Prev / Next Buttons
      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          next();
          startAutoPlay();
        });
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          prev();
          startAutoPlay();
        });
      }

      // Dot Indicator clicks
      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          var targetIdx = parseInt(dot.getAttribute('data-slide-to'), 10);
          if (!isNaN(targetIdx)) {
            goToSlide(targetIdx);
            startAutoPlay();
          }
        });
      });

      // Mute / Unmute Toggle Button Handling
      container.querySelectorAll('[data-sound-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.stopPropagation();
          e.preventDefault();

          var slideMedia = btn.closest('.mm-slide-video');
          if (!slideMedia) return;

          var iframe = slideMedia.querySelector('iframe');
          var video = slideMedia.querySelector('video');
          var isCurrentlyUnmuted = btn.classList.contains('is-unmuted');

          if (isCurrentlyUnmuted) {
            // Switch to Muted state
            btn.classList.remove('is-unmuted');
            btn.classList.add('is-muted');
            btn.setAttribute('aria-label', 'Unmute video');

            if (iframe && iframe.contentWindow) {
              try {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'mute', args: '' }), '*');
              } catch (err) {}
            }
            if (video) {
              video.muted = true;
            }
          } else {
            // Switch to Unmuted state
            btn.classList.remove('is-muted');
            btn.classList.add('is-unmuted');
            btn.setAttribute('aria-label', 'Mute video');

            if (iframe && iframe.contentWindow) {
              try {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: '' }), '*');
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), '*');
              } catch (err) {}
            }
            if (video) {
              video.muted = false;
              video.volume = 1;
            }
          }
        });
      });

      // Pause Autoplay on Hover
      if (stage) {
        stage.addEventListener('mouseenter', stopAutoPlay);
        stage.addEventListener('mouseleave', startAutoPlay);

        // Touch gestures (swipe left/right)
        var touchStartX = 0;
        var touchEndX = 0;

        stage.addEventListener(
          'touchstart',
          function (e) {
            if (e.changedTouches && e.changedTouches[0]) {
              touchStartX = e.changedTouches[0].screenX;
            }
            stopAutoPlay();
          },
          { passive: true }
        );

        stage.addEventListener(
          'touchend',
          function (e) {
            if (e.changedTouches && e.changedTouches[0]) {
              touchEndX = e.changedTouches[0].screenX;
              var diff = touchStartX - touchEndX;
              if (Math.abs(diff) > 40) {
                if (diff > 0) {
                  next();
                } else {
                  prev();
                }
              }
            }
            startAutoPlay();
          },
          { passive: true }
        );
      }

      // Start automatic sliding
      startAutoPlay();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBlockShowcase);
  } else {
    initBlockShowcase();
  }

  // ACF Gutenberg Block Preview Support
  if (window.acf) {
    window.acf.addAction('render_block_preview/type=block-showcase', function () {
      var elements = document.querySelectorAll('[data-mm-showcase]');
      elements.forEach(function (el) {
        delete el.dataset.showcaseInit;
      });
      initBlockShowcase();
    });
  }
})();
