if (typeof Lenis !== "undefined") {
  const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    direction: "vertical",
    gestureDirection: "vertical",
    smoothTouch: false,
    wheelMultiplier: 1,
  });

  window.myLenis = lenis;

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }

  requestAnimationFrame(raf);
}

document.addEventListener("DOMContentLoaded", () => {
  const metamint_mobile_breakpoint = 991;
  const isMobile = () => window.innerWidth <= metamint_mobile_breakpoint;
  const activeClass = "metamint-is-active";
  const arrowActiveClass = "active";

  const hamburgerSVG = `
    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <rect x="4" y="7.5" width="16" height="1.5"></rect>
      <rect x="4" y="15" width="16" height="1.5"></rect>
    </svg>
  `;

  const closeSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
      <path d="m13.06 12 6.47-6.47-1.06-1.06L12 10.94 5.53 4.47 4.47 5.53 10.94 12l-6.47 6.47 1.06 1.06L12 13.06l6.47 6.47 1.06-1.06L13.06 12Z"></path>
    </svg>
  `;

  const megaContentGroup = document.querySelector(".mega-content-group");
  const megaContents = megaContentGroup
    ? megaContentGroup.querySelectorAll(":scope > .mega-content, .mega-content")
    : [];

  let hideTimeout;

  // ----------------------------------------------------
  // Helper: Close all open submenus (both Mega and standard WP)
  // ----------------------------------------------------
  const metamint_close_all_submenus = () => {
    // 1. Mega menu submenus
    document
      .querySelectorAll(".mega-content.metamint-is-active, .mega-content.is-active")
      .forEach((content) => {
        content.classList.remove("metamint-is-active");
        content.classList.remove("is-active");
      });

    document.querySelectorAll(".mega-toggle-arrow").forEach((arrow) => {
      arrow.classList.remove(arrowActiveClass);
      arrow.dataset.state = "closed";
    });

    document.querySelectorAll(".mega-link.is-active").forEach((link) => {
      link.classList.remove("is-active");
    });

    if (megaContentGroup) {
      megaContentGroup.classList.remove("metamint-is-visible");
    }

    // 2. Standard WordPress navigation submenus
    document
      .querySelectorAll(".wp-block-navigation-submenu.is-open, .wp-block-navigation-submenu[aria-expanded='true']")
      .forEach((item) => {
        item.classList.remove("is-open");
        item.removeAttribute("aria-expanded");
        const toggleBtn = item.querySelector(".wp-block-navigation-submenu__toggle, button");
        if (toggleBtn) {
          toggleBtn.setAttribute("aria-expanded", "false");
        }
      });
  };

  // ----------------------------------------------------
  // Desktop Mega Menu Logic (Hover)
  // ----------------------------------------------------
  const getTargetContentForLink = (navItem) => {
    if (!navItem) return null;
    const classList = Array.from(navItem.classList);
    const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
    if (megaClass) {
      const number = megaClass.replace("mega-menu", "");
      const matched = document.querySelector(`.mega-content${number}`);
      if (matched) return matched;
    }
    // Fallback: match by index
    const allLinks = Array.from(document.querySelectorAll(".mega-link"));
    const idx = allLinks.indexOf(navItem);
    if (idx !== -1 && megaContents[idx]) {
      return megaContents[idx];
    }
    return null;
  };

  if (megaContentGroup && megaContents.length > 0) {
    const allNavListItems = document.querySelectorAll(
      ".wp-block-navigation__container > li, .metamint-main-nav .nav-content > li, .mega-link"
    );

    allNavListItems.forEach((navItem) => {
      navItem.addEventListener("mouseenter", () => {
        if (isMobile()) return;
        if (hideTimeout) clearTimeout(hideTimeout);

        if (navItem.classList.contains("mega-link")) {
          const targetContent = getTargetContentForLink(navItem);
          if (targetContent) {
            metamint_close_all_submenus();
            navItem.classList.add("is-active");
            megaContentGroup.classList.add("metamint-is-visible");
            targetContent.classList.add("metamint-is-active");
          } else {
            metamint_close_all_submenus();
          }
        } else {
          metamint_close_all_submenus();
        }
      });
    });

    const navContainers = document.querySelectorAll(
      ".wp-block-navigation__container, .metamint-main-nav .nav-content, .header-wrapper"
    );
    navContainers.forEach((container) => {
      container.addEventListener("mouseleave", (e) => {
        if (isMobile()) return;
        if (
          e.relatedTarget &&
          (megaContentGroup.contains(e.relatedTarget) ||
            megaContentGroup === e.relatedTarget)
        ) {
          return;
        }
        hideTimeout = setTimeout(metamint_close_all_submenus, 250);
      });
    });

    megaContentGroup.addEventListener("mouseenter", () => {
      if (isMobile()) return;
      if (hideTimeout) clearTimeout(hideTimeout);
      megaContentGroup.classList.add("metamint-is-visible");
    });

    megaContentGroup.addEventListener("mouseleave", (e) => {
      if (isMobile()) return;
      const toMegaLink =
        e.relatedTarget &&
        e.relatedTarget.closest &&
        e.relatedTarget.closest(".mega-link");
      if (toMegaLink) return;
      hideTimeout = setTimeout(metamint_close_all_submenus, 250);
    });
  }

  // ----------------------------------------------------
  // Mobile Mega Menu DOM Transfer & Accordions
  // ----------------------------------------------------
  function metamint_restore_desktop_dom() {
    document.querySelectorAll(".mega-link").forEach((menuItem) => {
      const classList = Array.from(menuItem.classList);
      const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
      if (!megaClass) return;

      const number = megaClass.replace("mega-menu", "");
      const megaContent = menuItem.querySelector(`.mega-content${number}`);

      if (megaContent && megaContentGroup && megaContent.parentElement === menuItem) {
        megaContentGroup.appendChild(megaContent);
      }

      const arrow = menuItem.querySelector(".mega-toggle-arrow");
      if (arrow) {
        arrow.remove();
      }
    });
    metamint_close_all_submenus();
  }

  function moveMegaContentsToMobile() {
    if (!isMobile()) return;
    document.querySelectorAll(".mega-link").forEach((menuItem) => {
      const classList = Array.from(menuItem.classList);
      const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
      if (!megaClass) return;

      const number = megaClass.replace("mega-menu", "");
      const megaContent = document.querySelector(`.mega-content${number}`);

      if (megaContent && megaContent.parentElement !== menuItem) {
        menuItem.appendChild(megaContent);
      }
    });
  }

  function initMobileMegaMenuToggles() {
    if (!isMobile()) return;

    const menuItems = document.querySelectorAll(".mega-link");
    menuItems.forEach((item) => {
      const classList = Array.from(item.classList);
      const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
      if (!megaClass) return;

      const number = megaClass.replace("mega-menu", "");
      const content = item.querySelector(`.mega-content${number}`) || document.querySelector(`.mega-content${number}`);
      if (!content) return;

      const link = item.querySelector(".wp-block-navigation-item__content, a");

      // Add chevron button if not already present
      let arrow = item.querySelector(":scope > .mega-toggle-arrow");
      if (!arrow) {
        arrow = document.createElement("button");
        arrow.type = "button";
        arrow.classList.add("mega-toggle-arrow");
        arrow.setAttribute("aria-label", "Toggle submenu");
        arrow.innerHTML =
          '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1.5 4L6 8L10.5 4" stroke="currentColor" stroke-width="1.5"/></svg>';
        item.insertBefore(arrow, content);
      }

      const toggleAction = (e) => {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = content.classList.contains(activeClass);

        // Close other mega contents
        document.querySelectorAll(`.mega-content.${activeClass}`).forEach((el) => {
          if (el !== content) el.classList.remove(activeClass);
        });
        document.querySelectorAll(`.mega-toggle-arrow.${arrowActiveClass}`).forEach((el) => {
          if (el !== arrow) {
            el.classList.remove(arrowActiveClass);
            el.dataset.state = "closed";
          }
        });
        document.querySelectorAll(`.mega-link.${activeClass}`).forEach((el) => {
          if (el !== item) el.classList.remove(activeClass);
        });

        if (isOpen) {
          content.classList.remove(activeClass);
          arrow.classList.remove(arrowActiveClass);
          arrow.dataset.state = "closed";
          item.classList.remove(activeClass);
        } else {
          content.classList.add(activeClass);
          arrow.classList.add(arrowActiveClass);
          arrow.dataset.state = "open";
          item.classList.add(activeClass);
        }
      };

      // Clone arrow to remove stale event listeners
      const freshArrow = arrow.cloneNode(true);
      arrow.parentNode.replaceChild(freshArrow, arrow);
      freshArrow.addEventListener("click", toggleAction);

      // Allow clicking the link itself to toggle submenu if link has no page or href="#"
      if (link && (link.getAttribute("href") === "#" || !link.getAttribute("href"))) {
        link.addEventListener("click", (e) => {
          toggleAction(e);
        });
      }
    });
  }

  // ----------------------------------------------------
  // Standard WordPress Submenu Toggles in Mobile
  // ----------------------------------------------------
  function initStandardSubmenuToggles() {
    const submenus = document.querySelectorAll(
      ".mobile-menu .wp-block-navigation-submenu, .wp-block-navigation__responsive-container .wp-block-navigation-submenu"
    );

    submenus.forEach((submenu) => {
      const toggle = submenu.querySelector(
        ":scope > .wp-block-navigation-submenu__toggle, :scope > .wp-block-navigation-item__content"
      );
      const icon = submenu.querySelector(":scope > .wp-block-navigation__submenu-icon");

      const handleToggle = (e) => {
        if (!isMobile()) return;
        e.preventDefault();
        e.stopPropagation();

        const isCurrentlyOpen = submenu.classList.contains("is-open");

        if (isCurrentlyOpen) {
          submenu.classList.remove("is-open");
          submenu.removeAttribute("aria-expanded");
          if (toggle) toggle.setAttribute("aria-expanded", "false");
        } else {
          submenu.classList.add("is-open");
          submenu.setAttribute("aria-expanded", "true");
          if (toggle) toggle.setAttribute("aria-expanded", "true");
        }
      };

      if (toggle && !toggle.dataset.mmBound) {
        toggle.dataset.mmBound = "true";
        toggle.addEventListener("click", handleToggle);
      }
      if (icon && !icon.dataset.mmBound) {
        icon.dataset.mmBound = "true";
        icon.addEventListener("click", handleToggle);
      }
    });
  }

  // ----------------------------------------------------
  // Mobile Drawer Open / Close Synchronization
  // ----------------------------------------------------
  const closeAllMobileDrawers = () => {
    document
      .querySelectorAll(".wp-block-navigation__responsive-container")
      .forEach((container) => {
        container.classList.remove("is-menu-open");
        container.classList.remove("has-modal-open");
      });

    document.documentElement.classList.remove("has-modal-open");
    document.body.classList.remove("has-modal-open");

    document
      .querySelectorAll(".wp-block-navigation__responsive-container-open")
      .forEach((btn) => {
        btn.setAttribute("aria-label", "Open menu");
        btn.setAttribute("aria-expanded", "false");
        btn.classList.remove("menuclicked");
        btn.innerHTML = hamburgerSVG;
      });

    // Close all open submenus when mobile menu closes
    metamint_close_all_submenus();

    if (window.myLenis) {
      setTimeout(() => {
        window.myLenis.start();
        window.myLenis.hasSmoothScroll = true;
      }, 50);
    }
  };

  const openMobileDrawer = (container, btn) => {
    container.classList.add("is-menu-open");
    container.classList.add("has-modal-open");
    document.documentElement.classList.add("has-modal-open");

    if (btn) {
      btn.setAttribute("aria-label", "Close menu");
      btn.setAttribute("aria-expanded", "true");
      btn.classList.add("menuclicked");
      btn.innerHTML = closeSVG;
    }

    if (window.myLenis) {
      window.myLenis.stop();
    }
  };

  // Bind all hamburger open buttons
  const openButtons = document.querySelectorAll(
    ".wp-block-navigation__responsive-container-open"
  );

  openButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // Find the associated responsive container
      const nav = btn.closest(".wp-block-navigation") || btn.closest(".mm-nav-wrap");
      const container =
        (nav && nav.querySelector(".wp-block-navigation__responsive-container")) ||
        document.querySelector(".mobile-menu .wp-block-navigation__responsive-container") ||
        document.querySelector(".wp-block-navigation__responsive-container");

      if (!container) return;

      const isOpen = container.classList.contains("is-menu-open");
      if (isOpen) {
        closeAllMobileDrawers();
      } else {
        closeAllMobileDrawers();
        openMobileDrawer(container, btn);
      }
    });
  });

  // Bind all close buttons
  document.addEventListener("click", (e) => {
    const closeBtn = e.target.closest(".wp-block-navigation__responsive-container-close");
    if (closeBtn) {
      e.preventDefault();
      closeAllMobileDrawers();
      return;
    }

    // Click outside open menu container closes it
    const openContainers = document.querySelectorAll(
      ".wp-block-navigation__responsive-container.is-menu-open"
    );
    if (openContainers.length > 0) {
      const isInside = Array.from(openContainers).some((c) => c.contains(e.target));
      const isOnOpenBtn = Array.from(openButtons).some((b) => b.contains(e.target));
      if (!isInside && !isOnOpenBtn) {
        closeAllMobileDrawers();
      }
    }
  });

  // Escape key closes mobile menu
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeAllMobileDrawers();
    }
  });

  // Initial setup
  const setupMobileMenu = () => {
    if (isMobile()) {
      moveMegaContentsToMobile();
      initMobileMegaMenuToggles();
      initStandardSubmenuToggles();
    } else {
      metamint_restore_desktop_dom();
    }
  };

  setupMobileMenu();

  window.addEventListener("resize", () => {
    setupMobileMenu();
  });
});
