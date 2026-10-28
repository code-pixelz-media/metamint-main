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
  const navContainer = document.querySelector(
    ".wp-block-navigation__container",
  );
  const modalremovediv = document.querySelector(
    ".wp-block-navigation__responsive-container",
  );
  const navButton = document.querySelector(
    ".wp-block-navigation__responsive-container-open",
  );
  if (navButton) {
    navButton.addEventListener("click", function () {
      this.classList.toggle("menuclicked");
      if (modalremovediv) {
        modalremovediv.classList.remove("has-modal-open");
        // html.classList.remove("has-modal-open");
        document.documentElement.classList.remove("has-modal-open");
      }
    });
  }
  const megaContentGroup = document.querySelector(".mega-content-group");
  const megaContents = megaContentGroup
    ? megaContentGroup.querySelectorAll(":scope > .mega-content, .mega-content")
    : [];

  if (!megaContentGroup || megaContents.length === 0) {
    return;
  }

  let hideTimeout;
  const metamint_mobile_breakpoint = 767;

  const metamint_hideAllMegaContent = () => {
    megaContentGroup.classList.remove("metamint-is-visible");
    megaContents.forEach((content) => {
      content.classList.remove("metamint-is-active");
    });
    document.querySelectorAll(".mega-link").forEach((link) => {
      link.classList.remove("is-active");
    });
  };

  // Match parent mega-menu{N} to dropdown mega-content{N}
  const getTargetContentForLink = (navItem) => {
    if (!navItem) return null;
    const classList = Array.from(navItem.classList);
    const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
    if (megaClass) {
      const number = megaClass.replace("mega-menu", "");
      const matched = megaContentGroup.querySelector(`.mega-content${number}`);
      if (matched) return matched;
    }
    // Fallback: match by index among .mega-link elements
    const allLinks = Array.from(document.querySelectorAll(".mega-link"));
    const idx = allLinks.indexOf(navItem);
    if (idx !== -1 && megaContents[idx]) {
      return megaContents[idx];
    }
    return null;
  };

  // Attach hover handlers directly to nav items for reliable switching
  const allNavListItems = document.querySelectorAll(
    ".wp-block-navigation__container > li, .metamint-main-nav .nav-content > li, .mega-link"
  );

  allNavListItems.forEach((navItem) => {
    navItem.addEventListener("mouseenter", () => {
      if (window.innerWidth <= metamint_mobile_breakpoint) {
        return;
      }
      if (hideTimeout) clearTimeout(hideTimeout);

      if (navItem.classList.contains("mega-link")) {
        const targetContent = getTargetContentForLink(navItem);
        if (targetContent) {
          metamint_hideAllMegaContent();
          navItem.classList.add("is-active");
          megaContentGroup.classList.add("metamint-is-visible");
          targetContent.classList.add("metamint-is-active");
        } else {
          metamint_hideAllMegaContent();
        }
      } else {
        // Non-mega menu item hovered (e.g. Home, Contact)
        metamint_hideAllMegaContent();
      }
    });
  });

  const navContainers = document.querySelectorAll(
    ".wp-block-navigation__container, .metamint-main-nav .nav-content, .header-wrapper"
  );
  navContainers.forEach((container) => {
    container.addEventListener("mouseleave", (e) => {
      if (window.innerWidth <= metamint_mobile_breakpoint) return;
      if (
        e.relatedTarget &&
        (megaContentGroup.contains(e.relatedTarget) ||
          megaContentGroup === e.relatedTarget)
      ) {
        return;
      }
      hideTimeout = setTimeout(metamint_hideAllMegaContent, 250);
    });
  });

  megaContentGroup.addEventListener("mouseenter", () => {
    if (window.innerWidth <= metamint_mobile_breakpoint) return;
    if (hideTimeout) clearTimeout(hideTimeout);
    megaContentGroup.classList.add("metamint-is-visible");
  });

  megaContentGroup.addEventListener("mouseleave", (e) => {
    if (window.innerWidth <= metamint_mobile_breakpoint) return;
    const toMegaLink =
      e.relatedTarget &&
      e.relatedTarget.closest &&
      e.relatedTarget.closest(".mega-link");
    if (toMegaLink) return;
    hideTimeout = setTimeout(metamint_hideAllMegaContent, 250);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const isMobile = () => window.innerWidth <= 767;
  const activeClass = "metamint-is-active";
  const arrowActiveClass = "active";
  const metamint_mobile_breakpoint = 767;

  function metamint_restore_desktop_dom() {
    if (window.innerWidth > metamint_mobile_breakpoint) {
      document.querySelectorAll(".mega-link").forEach((menuItem) => {
        const classList = Array.from(menuItem.classList);
        const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
        if (!megaClass) return;

        const number = megaClass.replace("mega-menu", "");
        const megaContent = menuItem.querySelector(`.mega-content${number}`);
        const megaContentGroup = document.querySelector(".mega-content-group");

        if (
          megaContent &&
          megaContentGroup &&
          megaContent.parentElement === menuItem
        ) {
          megaContentGroup.appendChild(megaContent);
        }

        const arrow = menuItem.querySelector(".mega-toggle-arrow");
        if (arrow) {
          arrow.remove();
        }
      });
    }
  }

  function moveMegaContentsByClass() {
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

  function initMegaMenuToggle() {
    if (!isMobile()) return;

    const menuItems = document.querySelectorAll(".mega-link");
    menuItems.forEach((item) => {
      const classList = Array.from(item.classList);
      const megaClass = classList.find((cls) => cls.startsWith("mega-menu"));
      if (!megaClass) return;

      const number = megaClass.replace("mega-menu", "");
      const content = item.querySelector(`.mega-content${number}`);

      if (!content) return;

      const anchor = item.querySelector(".wp-block-navigation-item__content");
      if (!anchor) return;

      let arrow = anchor.querySelector(".mega-toggle-arrow");

      if (!arrow) {
        arrow = document.createElement("button");
        arrow.classList.add("mega-toggle-arrow");
        arrow.setAttribute("aria-label", "Expand submenu");
        arrow.innerHTML =
          '<svg width="10" height="6" viewBox="0 0 10 6"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" fill="none" /></svg>';
        anchor.appendChild(arrow);
      }

      const oldArrow = arrow;
      arrow = oldArrow.cloneNode(true);
      oldArrow.parentNode.replaceChild(arrow, oldArrow);

      arrow.dataset.state = content.classList.contains(activeClass)
        ? "open"
        : "closed";
      if (content.classList.contains(activeClass)) {
        arrow.classList.add(arrowActiveClass);
      } else {
        arrow.classList.remove(arrowActiveClass);
      }

      arrow.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const isCurrentlyOpen = arrow.dataset.state === "open";

        document
          .querySelectorAll(`.mega-content.${activeClass}`)
          .forEach((el) => {
            el.classList.remove(activeClass);
          });
        document
          .querySelectorAll(`.mega-toggle-arrow.${arrowActiveClass}`)
          .forEach((el) => {
            el.classList.remove(arrowActiveClass);
            el.dataset.state = "closed";
          });

        if (!isCurrentlyOpen) {
          content.classList.add(activeClass);
          arrow.classList.add(arrowActiveClass);
          arrow.dataset.state = "open";
        }
      });
    });
  }

  setTimeout(() => {
    moveMegaContentsByClass();
    initMegaMenuToggle();
  }, 50);

  window.addEventListener("resize", function () {
    if (window.innerWidth <= metamint_mobile_breakpoint) {
      moveMegaContentsByClass();
      initMegaMenuToggle();
    } else {
      metamint_restore_desktop_dom();
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const openBtn = document.querySelector(
    ".wp-block-navigation__responsive-container-open",
  );
  const menuContainer = document.querySelector(
    ".wp-block-navigation__responsive-container",
  );

  const htmlElement = document.documentElement;

  const lenisInstance = window.myLenis;

  if (!openBtn || !menuContainer) return;

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
  openBtn.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopImmediatePropagation();
    const isOpen = menuContainer.classList.contains("is-menu-open");

    if (isOpen) {
      // Close menu
      menuContainer.classList.remove("is-menu-open");
      openBtn.setAttribute("aria-label", "Open menu");
      openBtn.setAttribute("aria-expanded", "false");
      openBtn.innerHTML = hamburgerSVG;
    } else {
      // Open menu
      menuContainer.classList.add("is-menu-open");
      openBtn.setAttribute("aria-label", "Close menu");
      openBtn.setAttribute("aria-expanded", "true");
      openBtn.innerHTML = closeSVG;
    }
  });
  const metamint_close_all_submenus = () => {
    document
      .querySelectorAll(".mega-content.metamint-is-active")
      .forEach((content) => {
        content.classList.remove("metamint-is-active");
      });

    document.querySelectorAll(".mega-toggle-arrow.active").forEach((arrow) => {
      arrow.classList.remove("active");
      arrow.dataset.state = "closed";
    });
  };

  const metamint_close_mobile_menu = () => {
    openBtn.setAttribute("aria-label", "Open menu");
    openBtn.setAttribute("aria-expanded", "false");
    openBtn.innerHTML = hamburgerSVG;

    metamint_close_all_submenus();

    if (lenisInstance) {
      setTimeout(() => {
        lenisInstance.start();
        lenisInstance.hasSmoothScroll = true;
      }, 100);
    }
  };

  openBtn.addEventListener("click", () => {
    const isOpen = menuContainer.classList.contains("is-menu-open");

    if (isOpen) {
      menuContainer.classList.remove("is-menu-open");

      metamint_close_mobile_menu();
    } else {
      if (lenisInstance) {
        lenisInstance.stop();
      }

      metamint_close_all_submenus();

      menuContainer.classList.add("is-menu-open");
      openBtn.setAttribute("aria-label", "Close menu");
      openBtn.setAttribute("aria-expanded", "true");
      openBtn.innerHTML = closeSVG;
    }
  });

  const handleMenuSync = () => {
    const isMenuClosed = !menuContainer.classList.contains("is-menu-open");

    if (isMenuClosed && openBtn.getAttribute("aria-expanded") === "true") {
      menuContainer.classList.remove("is-menu-open");
      metamint_close_mobile_menu();
    }
  };

  const menuObserver = new MutationObserver((mutationsList, observer) => {
    handleMenuSync();
  });

  const buttonObserver = new MutationObserver((mutationsList, observer) => {
    handleMenuSync();
  });

  menuObserver.observe(menuContainer, {
    attributes: true,
    attributeFilter: ["class"],
  });
  buttonObserver.observe(openBtn, {
    attributes: true,
    attributeFilter: ["aria-label", "aria-expanded"],
  });

  document.addEventListener(
    "click",
    (event) => {
      const isOpen = menuContainer.classList.contains("is-menu-open");
      if (!isOpen) return;

      const isClickInsideMenu = menuContainer.contains(event.target);
      const isClickOnButton = openBtn.contains(event.target);

      if (!isClickInsideMenu && !isClickOnButton) {
        menuContainer.classList.remove("is-menu-open");
      }
    },
    true,
  );
});

function removeHtmlModalClass() {
  const $openBtn = jQuery(".wp-block-navigation__responsive-container-open");

  const $html = jQuery("html");

  const htmlClassToRemove = "has-modal-open";

  if ($openBtn.length && $openBtn.attr("aria-expanded") === "false") {
    if ($html.hasClass(htmlClassToRemove)) {
      $html.removeClass(htmlClassToRemove);
      console.log(
        `[Interval Sync] Menu closed (aria-expanded="false"). Removed class "${htmlClassToRemove}" from <html>.`,
      );
    }
  } else if ($openBtn.length && $openBtn.attr("aria-expanded") === "true") {
    if (!$html.hasClass(htmlClassToRemove)) {
      $html.addClass(htmlClassToRemove);
      console.log(
        `[Interval Sync] Menu open (aria-expanded="true"). Added class "${htmlClassToRemove}" to <html>.`,
      );
    }
  }
}

jQuery(document).ready(function ($) {
  removeHtmlModalClass();

  const intervalId = setInterval(removeHtmlModalClass, 1000);

  console.log("Started 1-second interval check for aria-expanded state.");
});
