/**
 * Metamint Block Showcase Script
 * Handles synchronized slide transitions between desktop & mobile mockups
 */
(function () {
  "use strict";

  // Load official YouTube IFrame API if not already loaded
  if (!window.YT) {
    var ytScript = document.createElement("script");
    ytScript.src = "https://www.youtube.com/iframe_api";
    var firstScript = document.getElementsByTagName("script")[0];
    if (firstScript && firstScript.parentNode) {
      firstScript.parentNode.insertBefore(ytScript, firstScript);
    } else if (document.head) {
      document.head.appendChild(ytScript);
    }
  }

  function initBlockShowcase() {
    var showcases = document.querySelectorAll("[data-mm-showcase]");
    if (!showcases.length) return;

    showcases.forEach(function (container) {
      if (container.dataset.showcaseInit === "true") return;
      container.dataset.showcaseInit = "true";

      var desktopTrack = container.querySelector("[data-desktop-track]");
      var mobileTrack = container.querySelector("[data-mobile-track]");
      var desktopSlides = container.querySelectorAll(".mm-desktop-slide");
      var mobileSlides = container.querySelectorAll(".mm-mobile-slide");
      var dots = container.querySelectorAll("[data-slide-to]");
      var prevBtn = container.querySelector("[data-showcase-prev]");
      var nextBtn = container.querySelector("[data-showcase-next]");
      var stage = container.querySelector("[data-showcase-stage]");

      var total = desktopSlides.length
        ? desktopSlides.length
        : mobileSlides.length;

      var currentIndex = 0;
      var autoTimer = null;
      var isTransitioning = false;

      // Ensure YouTube iframes have the exact matching origin parameter from window.location.origin
      container.querySelectorAll("iframe").forEach(function (iframe) {
        var src = iframe.getAttribute("src");
        if (
          src &&
          (src.indexOf("youtube") !== -1 ||
            src.indexOf("youtube-nocookie") !== -1)
        ) {
          try {
            var curOrigin = window.location.origin;
            var u = new URL(src, window.location.href);
            if (u.searchParams.get("origin") !== curOrigin) {
              u.searchParams.set("origin", curOrigin);
              u.searchParams.set("widget_referrer", curOrigin);
              iframe.setAttribute("src", u.toString());
            }
          } catch (e) {}
        }
      });

      // Disables / hides closed captions & subtitles on YouTube iframes
      function disableSubtitles(iframe) {
        if (!iframe) return;
        if (iframe._ytPlayer) {
          try {
            if (typeof iframe._ytPlayer.unloadModule === "function") {
              iframe._ytPlayer.unloadModule("captions");
              iframe._ytPlayer.unloadModule("cc");
            }
            if (typeof iframe._ytPlayer.setOption === "function") {
              iframe._ytPlayer.setOption("captions", "track", {});
              iframe._ytPlayer.setOption("cc", "track", {});
              iframe._ytPlayer.setOption("captions", "reload", false);
            }
          } catch (e) {}
        }
        if (iframe.contentWindow) {
          try {
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "unloadModule",
                args: ["captions"],
              }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "unloadModule",
                args: ["cc"],
              }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "setOption",
                args: ["captions", "track", {}],
              }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "setOption",
                args: ["cc", "track", {}],
              }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "setOption",
                args: ["captions", "reload", false],
              }),
              "*",
            );
          } catch (e) {}
        }
      }

      // Enforces 720p (hd720) playback quality across multiple API commands
      function enforce720Quality(iframe) {
        if (!iframe) return;
        if (
          iframe._ytPlayer &&
          typeof iframe._ytPlayer.setPlaybackQuality === "function"
        ) {
          try {
            iframe._ytPlayer.setPlaybackQuality("hd720");
          } catch (e) {}
        }
        if (iframe.contentWindow) {
          try {
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "setPlaybackQuality",
                args: ["hd720"],
              }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({
                event: "command",
                func: "setPlaybackQualityRange",
                args: ["hd720", "hd720"],
              }),
              "*",
            );
          } catch (e) {}
        }
      }

      function slideHasVideo(idx) {
        var dSlide = desktopSlides[idx];
        var mSlide = mobileSlides[idx];
        var dMockup = dSlide ? dSlide.closest(".mm-mockup-desktop") : null;
        var mMockup = mSlide ? mSlide.closest(".mm-mockup-mobile") : null;
        var dVisible =
          !dMockup || window.getComputedStyle(dMockup).display !== "none";
        var mVisible =
          !mMockup || window.getComputedStyle(mMockup).display !== "none";

        var hasD =
          dVisible &&
          dSlide &&
          dSlide.querySelector(".mm-slide-video") !== null;
        var hasM =
          mVisible &&
          mSlide &&
          mSlide.querySelector(".mm-slide-video") !== null;
        return hasD || hasM;
      }

      function goToSlide(newIndex) {
        if (total <= 1 || newIndex === currentIndex || isTransitioning) return;
        isTransitioning = true;

        currentIndex = (newIndex + total) % total;
        var offsetPercent = currentIndex * 100;

        // Synchronously slide desktop and mobile tracks
        if (desktopTrack) {
          desktopTrack.style.transform =
            "translate3d(-" + offsetPercent + "%, 0, 0)";
        }
        if (mobileTrack) {
          mobileTrack.style.transform =
            "translate3d(-" + offsetPercent + "%, 0, 0)";
        }

        // Toggle active states on individual slides for depth zoom
        desktopSlides.forEach(function (slide, idx) {
          var isActive = idx === currentIndex;
          slide.classList.toggle("is-active", isActive);
          if (!isActive) {
            var sv = slide.querySelector(".mm-slide-video");
            if (sv) sv.classList.remove("is-playing");
          }
        });

        mobileSlides.forEach(function (slide, idx) {
          var isActive = idx === currentIndex;
          slide.classList.toggle("is-active", isActive);
          if (!isActive) {
            var sv = slide.querySelector(".mm-slide-video");
            if (sv) sv.classList.remove("is-playing");
          }
        });

        // Ensure active slide video is playing in Full HD and mute inactive slides
        [desktopSlides, mobileSlides].forEach(function (slidesList) {
          slidesList.forEach(function (slide, idx) {
            // If mockup container is hidden (e.g. mobile mockup on small screens), pause its video
            var mockup = slide.closest(".mm-mockup-mobile, .mm-mockup-desktop");
            var isHiddenMockup =
              mockup && window.getComputedStyle(mockup).display === "none";

            if (isHiddenMockup) {
              var hiddenIframe = slide.querySelector("iframe");
              if (hiddenIframe && hiddenIframe.contentWindow) {
                try {
                  hiddenIframe.contentWindow.postMessage(
                    JSON.stringify({
                      event: "command",
                      func: "pauseVideo",
                      args: "",
                    }),
                    "*",
                  );
                  hiddenIframe.contentWindow.postMessage(
                    JSON.stringify({
                      event: "command",
                      func: "mute",
                      args: "",
                    }),
                    "*",
                  );
                } catch (e) {}
              }
              var hiddenVideo = slide.querySelector("video");
              if (hiddenVideo) {
                try {
                  hiddenVideo.pause();
                } catch (e) {}
              }
              return;
            }

            var isActive = idx === currentIndex;
            if (isActive) {
              var iframe = slide.querySelector("iframe");
              var sv = slide.querySelector(".mm-slide-video");
              if (iframe) {
                enforce720Quality(iframe);
                if (iframe._ytPlayer) {
                  try {
                    iframe._ytPlayer.mute();
                    iframe._ytPlayer.playVideo();
                    if (typeof iframe._ytPlayer.setPlaybackQuality === "function") {
                      iframe._ytPlayer.setPlaybackQuality("hd720");
                    }
                    disableSubtitles(iframe);
                  } catch (e) {}
                }
                if (iframe.contentWindow) {
                  try {
                    iframe.contentWindow.postMessage(
                      JSON.stringify({
                        event: "command",
                        func: "mute",
                        args: "",
                      }),
                      "*",
                    );
                    iframe.contentWindow.postMessage(
                      JSON.stringify({
                        event: "command",
                        func: "playVideo",
                        args: "",
                      }),
                      "*",
                    );
                    enforce720Quality(iframe);
                    disableSubtitles(iframe);
                    setTimeout(function () {
                      enforce720Quality(iframe);
                      disableSubtitles(iframe);
                    }, 400);
                    setTimeout(function () {
                      enforce720Quality(iframe);
                      disableSubtitles(iframe);
                    }, 1200);
                  } catch (e) {}
                }
                if (sv) {
                  setTimeout(function () {
                    if (slide.classList.contains("is-active")) {
                      sv.classList.add("is-playing");
                    }
                  }, 750);
                }
              }
              var video = slide.querySelector("video");
              if (video) {
                try {
                  video.play();
                } catch (e) {}
              }
            } else {
              // Reset sound state on inactive slides
              var soundBtn = slide.querySelector("[data-sound-toggle]");
              if (soundBtn && soundBtn.classList.contains("is-unmuted")) {
                soundBtn.classList.remove("is-unmuted");
                soundBtn.classList.add("is-muted");
                soundBtn.setAttribute("aria-label", "Unmute video");
              }
              var inactiveIframe = slide.querySelector("iframe");
              if (inactiveIframe) {
                if (inactiveIframe._ytPlayer) {
                  try {
                    inactiveIframe._ytPlayer.pauseVideo();
                    inactiveIframe._ytPlayer.mute();
                  } catch (e) {}
                }
                if (inactiveIframe.contentWindow) {
                  try {
                    inactiveIframe.contentWindow.postMessage(
                      JSON.stringify({
                        event: "command",
                        func: "pauseVideo",
                        args: "",
                      }),
                      "*",
                    );
                    inactiveIframe.contentWindow.postMessage(
                      JSON.stringify({
                        event: "command",
                        func: "mute",
                        args: "",
                      }),
                      "*",
                    );
                  } catch (e) {}
                }
              }
              var inactiveVideo = slide.querySelector("video");
              if (inactiveVideo) {
                inactiveVideo.muted = true;
              }
            }
          });
        });

        // Update Dots
        dots.forEach(function (dot, idx) {
          dot.classList.toggle("is-active", idx === currentIndex);
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
        if (total <= 1) {
          return;
        }
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
      container.querySelectorAll("video").forEach(function (vid) {
        vid.addEventListener("ended", function () {
          var parentSlide = vid.closest(".mm-slide");
          var idx = parentSlide
            ? parseInt(parentSlide.getAttribute("data-slide-index"), 10)
            : -1;
          if (idx === currentIndex && total > 1) {
            next();
          }
        });
      });

      // Send handshake, start muted playback, and disable subtitles on YouTube iframes upon load
      container.querySelectorAll("iframe").forEach(function (iframe) {
        function triggerInit() {
          if (!iframe || !iframe.contentWindow) return;
          try {
            iframe.contentWindow.postMessage(
              JSON.stringify({ event: "listening", id: 1, channel: "widget" }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({ event: "command", func: "mute", args: "" }),
              "*",
            );
            iframe.contentWindow.postMessage(
              JSON.stringify({ event: "command", func: "playVideo", args: "" }),
              "*",
            );
            enforce720Quality(iframe);
            disableSubtitles(iframe);
          } catch (e) {}
        }
        iframe.addEventListener("load", function () {
          triggerInit();
          setTimeout(triggerInit, 300);
          setTimeout(triggerInit, 800);
          setupAllYTPlayers();
        });
        triggerInit();
        setTimeout(triggerInit, 400);
      });

      // Initialize official YouTube IFrame API players for robust HD quality & subtitles control
      function initYTPlayer(iframe) {
        if (
          !window.YT ||
          !window.YT.Player ||
          iframe.dataset.ytBound === "true"
        )
          return;
        iframe.dataset.ytBound = "true";

        var iframeId =
          iframe.id || "mm-yt-" + Math.random().toString(36).substr(2, 9);
        iframe.id = iframeId;

        try {
          iframe._ytPlayer = new window.YT.Player(iframeId, {
            events: {
              onReady: function (e) {
                try {
                  e.target.mute();
                  e.target.playVideo();
                  if (typeof e.target.setPlaybackQuality === "function") {
                    e.target.setPlaybackQuality("hd720");
                  }
                  disableSubtitles(iframe);
                } catch (err) {}
              },
              onPlaybackQualityChange: function (e) {
                if (e.data !== "hd720" && typeof e.target.setPlaybackQuality === "function") {
                  e.target.setPlaybackQuality("hd720");
                }
              },
              onApiChange: function (e) {
                disableSubtitles(iframe);
                enforce720Quality(iframe);
              },
              onStateChange: function (e) {
                var sv = iframe.closest(".mm-slide-video");
                var slide = iframe.closest(".mm-slide");

                if (e.data === 1 || e.data === 3) {
                  enforce720Quality(iframe);
                  disableSubtitles(iframe);

                  if (e.data === 1) {
                    if (sv) {
                      setTimeout(function () {
                        sv.classList.add("is-playing");
                      }, 200);
                    }
                    setTimeout(function () { disableSubtitles(iframe); enforce720Quality(iframe); }, 300);
                    setTimeout(function () { disableSubtitles(iframe); enforce720Quality(iframe); }, 750);
                    setTimeout(function () { disableSubtitles(iframe); enforce720Quality(iframe); }, 1500);
                    setTimeout(function () { disableSubtitles(iframe); enforce720Quality(iframe); }, 2500);
                  }
                } else if (e.data === 0) {
                  // ended: immediately show poster to hide YouTube's related video grid
                  if (sv) sv.classList.remove("is-playing");
                  try {
                    e.target.seekTo(0);
                    e.target.pauseVideo();
                  } catch (err) {}
                  if (slide && slide.classList.contains("is-active")) next();
                }
              },
            },
          });
        } catch (err) {}
      }

      function setupAllYTPlayers() {
        container.querySelectorAll("iframe").forEach(function (iframe) {
          initYTPlayer(iframe);
        });
      }

      function ensureYTReady(cb) {
        if (window.YT && window.YT.Player) {
          cb();
          return;
        }
        var prevReady = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function () {
          if (typeof prevReady === "function") prevReady();
          cb();
        };
        var count = 0;
        var checkYT = setInterval(function () {
          count++;
          if (window.YT && window.YT.Player) {
            clearInterval(checkYT);
            cb();
          } else if (count > 50) {
            clearInterval(checkYT);
          }
        }, 100);
      }

      ensureYTReady(setupAllYTPlayers);

      // Listen for YouTube video events (state changes, end-of-video advance, subtitles suppression)
      window.addEventListener("message", function (e) {
        if (!e.data) return;
        var msg = null;
        if (typeof e.data === "string") {
          try {
            msg = JSON.parse(e.data);
          } catch (err) {
            return;
          }
        } else if (typeof e.data === "object") {
          msg = e.data;
        }
        if (!msg) return;

        // Extract playerState from onStateChange or infoDelivery
        var playerState = null;
        if (msg.event === "onStateChange") {
          playerState = typeof msg.info === "number" ? msg.info : null;
        } else if (
          msg.event === "infoDelivery" &&
          msg.info &&
          msg.info.playerState !== undefined
        ) {
          playerState = msg.info.playerState;
        }

        // When YouTube confirms active playback (playerState === 1), smoothly reveal video and enforce 720p
        if (playerState === 1) {
          container.querySelectorAll("iframe").forEach(function (iframe) {
            enforce720Quality(iframe);
            disableSubtitles(iframe);
          });
          setTimeout(function () {
            container
              .querySelectorAll(".mm-slide.is-active .mm-slide-video")
              .forEach(function (sv) {
                sv.classList.add("is-playing");
              });
            container.querySelectorAll("iframe").forEach(function (iframe) {
              enforce720Quality(iframe);
              disableSubtitles(iframe);
            });
          }, 200);
        } else if (playerState === 3) {
          container.querySelectorAll("iframe").forEach(function (iframe) {
            enforce720Quality(iframe);
            disableSubtitles(iframe);
          });
        }

        // When player is ready or API changes, enforce muted playback and quality without revealing yet
        if (msg.event === "onReady" || msg.event === "onApiChange") {
          container.querySelectorAll("iframe").forEach(function (iframe) {
            try {
              iframe.contentWindow.postMessage(
                JSON.stringify({
                  event: "listening",
                  id: 1,
                  channel: "widget",
                }),
                "*",
              );
              iframe.contentWindow.postMessage(
                JSON.stringify({ event: "command", func: "mute", args: "" }),
                "*",
              );
              iframe.contentWindow.postMessage(
                JSON.stringify({
                  event: "command",
                  func: "playVideo",
                  args: "",
                }),
                "*",
              );
              enforce720Quality(iframe);
              disableSubtitles(iframe);
            } catch (e) {}
          });
        }

        // Advance slide on video end (playerState === 0)
        if (playerState === 0) {
          container.querySelectorAll(".mm-slide-video").forEach(function (sv) {
            sv.classList.remove("is-playing");
          });
          if (slideHasVideo(currentIndex)) {
            next();
          }
        }
      });

      // Event Listeners for Prev / Next Buttons
      if (nextBtn) {
        nextBtn.addEventListener("click", function () {
          next();
          startAutoPlay();
        });
      }

      if (prevBtn) {
        prevBtn.addEventListener("click", function () {
          prev();
          startAutoPlay();
        });
      }

      // Dot Indicator clicks
      dots.forEach(function (dot) {
        dot.addEventListener("click", function () {
          var targetIdx = parseInt(dot.getAttribute("data-slide-to"), 10);
          if (!isNaN(targetIdx)) {
            goToSlide(targetIdx);
            startAutoPlay();
          }
        });
      });

      // Mute / Unmute Toggle Button Handling
      container.querySelectorAll("[data-sound-toggle]").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
          e.stopPropagation();
          e.preventDefault();

          var slideMedia = btn.closest(".mm-slide-video");
          if (!slideMedia) return;

          var iframe = slideMedia.querySelector("iframe");
          var video = slideMedia.querySelector("video");
          var isCurrentlyUnmuted = btn.classList.contains("is-unmuted");

          if (isCurrentlyUnmuted) {
            // Switch to Muted state
            btn.classList.remove("is-unmuted");
            btn.classList.add("is-muted");
            btn.setAttribute("aria-label", "Unmute video");

            if (iframe && iframe.contentWindow) {
              try {
                iframe.contentWindow.postMessage(
                  JSON.stringify({ event: "command", func: "mute", args: "" }),
                  "*",
                );
              } catch (err) {}
            }
            if (video) {
              video.muted = true;
            }
          } else {
            // Switch to Unmuted state
            btn.classList.remove("is-muted");
            btn.classList.add("is-unmuted");
            btn.setAttribute("aria-label", "Mute video");

            if (iframe && iframe.contentWindow) {
              try {
                iframe.contentWindow.postMessage(
                  JSON.stringify({
                    event: "command",
                    func: "unMute",
                    args: "",
                  }),
                  "*",
                );
                iframe.contentWindow.postMessage(
                  JSON.stringify({
                    event: "command",
                    func: "setVolume",
                    args: [100],
                  }),
                  "*",
                );
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
        stage.addEventListener("mouseenter", stopAutoPlay);
        stage.addEventListener("mouseleave", startAutoPlay);

        // Touch gestures (swipe left/right)
        var touchStartX = 0;
        var touchEndX = 0;

        stage.addEventListener(
          "touchstart",
          function (e) {
            if (e.changedTouches && e.changedTouches[0]) {
              touchStartX = e.changedTouches[0].screenX;
            }
            stopAutoPlay();
          },
          { passive: true },
        );

        stage.addEventListener(
          "touchend",
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
          { passive: true },
        );
      }

      // Sync and pause videos in hidden mockups (e.g. mobile mockup on small screens)
      function syncHiddenVideos() {
        var mobileMockup = container.querySelector(".mm-mockup-mobile");
        if (
          mobileMockup &&
          window.getComputedStyle(mobileMockup).display === "none"
        ) {
          mobileMockup.querySelectorAll("iframe").forEach(function (iframe) {
            if (iframe && iframe.contentWindow) {
              try {
                iframe.contentWindow.postMessage(
                  JSON.stringify({
                    event: "command",
                    func: "pauseVideo",
                    args: "",
                  }),
                  "*",
                );
                iframe.contentWindow.postMessage(
                  JSON.stringify({ event: "command", func: "mute", args: "" }),
                  "*",
                );
              } catch (e) {}
            }
          });
          mobileMockup.querySelectorAll("video").forEach(function (vid) {
            try {
              vid.pause();
            } catch (e) {}
          });
        }
      }

      window.addEventListener("resize", syncHiddenVideos);
      syncHiddenVideos();

      // Kickstart videos on viewport visibility or first user interaction
      function kickstartActiveVideos() {
        container.querySelectorAll(".mm-slide.is-active iframe").forEach(function (iframe) {
          if (iframe.contentWindow) {
            try {
              iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "mute", args: "" }), "*");
              iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "playVideo", args: "" }), "*");
            } catch (e) {}
          }
          if (iframe._ytPlayer && typeof iframe._ytPlayer.playVideo === "function") {
            try {
              iframe._ytPlayer.mute();
              iframe._ytPlayer.playVideo();
            } catch (e) {}
          }
        });
      }

      if ("IntersectionObserver" in window) {
        var obs = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              kickstartActiveVideos();
            }
          });
        }, { threshold: 0.1 });
        obs.observe(container);
      }

      ["scroll", "touchstart", "click"].forEach(function (evt) {
        window.addEventListener(evt, kickstartActiveVideos, { once: true, passive: true });
      });

      // Safety fallback: ensure active video becomes visible after 1.8s
      setTimeout(function () {
        container
          .querySelectorAll(".mm-slide.is-active .mm-slide-video")
          .forEach(function (sv) {
            sv.classList.add("is-playing");
            var iframe = sv.querySelector("iframe");
            if (iframe) {
              enforce720Quality(iframe);
              disableSubtitles(iframe);
            }
          });
      }, 1800);

      // Start automatic sliding
      startAutoPlay();
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initBlockShowcase);
  } else {
    initBlockShowcase();
  }

  // ACF Gutenberg Block Preview Support
  if (window.acf) {
    window.acf.addAction(
      "render_block_preview/type=block-showcase",
      function () {
        var elements = document.querySelectorAll("[data-mm-showcase]");
        elements.forEach(function (el) {
          delete el.dataset.showcaseInit;
        });
        initBlockShowcase();
      },
    );
  }
})();
