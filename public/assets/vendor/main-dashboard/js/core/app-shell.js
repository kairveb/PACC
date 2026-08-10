(() => {
  const themeKey = "himsMainTheme";
  const applyTheme = (preference) => {
    const theme = preference === "system"
      ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")
      : preference;
    document.documentElement.dataset.theme = theme;
    localStorage.setItem(themeKey, preference);
    document.querySelectorAll("[data-theme-option]").forEach((button) => {
      button.setAttribute("aria-checked", String(button.dataset.themeOption === preference));
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const profileToggle = document.getElementById("profile-toggle");
    const profileMenu = document.getElementById("profile-menu");
    const sidebarToggle = document.querySelector(".menu-toggle");
    const backdrop = document.querySelector("[data-sidebar-backdrop]");
    const searchInput = document.getElementById("global-search");
    const searchResults = document.getElementById("search-results");
    const toastContainer = document.getElementById("toast-container");
    const accordionToggles = [...document.querySelectorAll(".nav-accordion__toggle")];
    const tooltipTargets = [...document.querySelectorAll("[data-nav-tooltip]")];
    let activeModal = null;
    let modalTrigger = null;
    let navTooltip = null;

    const focusableSelector = [
      "a[href]",
      "button:not([disabled])",
      "input:not([disabled])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      "[tabindex]:not([tabindex='-1'])",
    ].join(",");
    const closeModal = (modal, restoreFocus = true) => {
      if (!modal) return;
      modal.hidden = true;
      document.body.style.removeProperty("overflow");
      activeModal = null;
      if (restoreFocus) modalTrigger?.focus();
      modalTrigger = null;
    };
    const openModal = (id, trigger = null) => {
      const modal = document.getElementById(id);
      if (!modal?.classList.contains("hims-modal")) return false;
      if (activeModal) closeModal(activeModal, false);
      activeModal = modal;
      modalTrigger = trigger;
      modal.hidden = false;
      document.body.style.overflow = "hidden";
      window.requestAnimationFrame(() => modal.querySelector(focusableSelector)?.focus());
      return true;
    };
    const notify = ({ tone = "info", title = null, message = "", duration = 4200 } = {}) => {
      if (!toastContainer || !message) return null;
      if (tone === "error") tone = "danger";
      const allowedTones = new Set(["success", "warning", "danger", "info"]);
      const resolvedTone = allowedTones.has(tone) ? tone : "info";
      const toneIcons = { success: "ph-check-circle", warning: "ph-warning", danger: "ph-warning-circle", info: "ph-info" };
      const toast = document.createElement("div");
      toast.className = `hims-notification hims-notification--${resolvedTone} hims-notification--toast`;
      toast.setAttribute("role", resolvedTone === "danger" ? "alert" : "status");
      const icon = document.createElement("span");
      icon.className = "hims-notification__icon";
      icon.setAttribute("aria-hidden", "true");
      icon.innerHTML = `<i class="ph ${toneIcons[resolvedTone]}"></i>`;
      const content = document.createElement("div");
      content.className = "hims-notification__content";
      if (title) {
        const heading = document.createElement("strong");
        heading.textContent = title;
        content.appendChild(heading);
      }
      const copy = document.createElement("div");
      copy.textContent = message;
      content.appendChild(copy);
      const dismiss = document.createElement("button");
      dismiss.className = "hims-notification__dismiss";
      dismiss.type = "button";
      dismiss.dataset.notificationDismiss = "";
      dismiss.setAttribute("aria-label", "Dismiss notification");
      dismiss.innerHTML = '<i class="ph ph-x" aria-hidden="true"></i>';
      toast.append(icon, content, dismiss);
      toastContainer.replaceChildren(toast);
      if (duration > 0) window.setTimeout(() => toast.remove(), duration);
      return toast;
    };

    window.HimsComponents = Object.freeze({ openModal, closeModal, notify });

    const setAccordion = (toggle, expanded) => {
      const submenu = document.getElementById(toggle.getAttribute("aria-controls"));
      toggle.setAttribute("aria-expanded", String(expanded));
      toggle.closest(".nav-accordion")?.classList.toggle("is-expanded", expanded);
      if (submenu) submenu.hidden = !expanded;
    };
    const closeOtherAccordions = (current) => {
      accordionToggles.forEach((toggle) => {
        if (toggle !== current) setAccordion(toggle, false);
      });
    };
    const hideNavTooltip = () => {
      navTooltip?.remove();
      navTooltip = null;
    };
    const showNavTooltip = (target) => {
      if (!body.classList.contains("sidebar-collapsed") || window.innerWidth <= 991) return;
      hideNavTooltip();
      const rect = target.getBoundingClientRect();
      navTooltip = document.createElement("div");
      navTooltip.className = "nav-tooltip";
      navTooltip.setAttribute("role", "tooltip");
      navTooltip.textContent = target.dataset.navTooltip;
      document.body.appendChild(navTooltip);
      const tooltipRect = navTooltip.getBoundingClientRect();
      navTooltip.style.left = `${Math.round(rect.right + 10)}px`;
      navTooltip.style.top = `${Math.round(rect.top + (rect.height - tooltipRect.height) / 2)}px`;
    };

    const initiallyExpanded = accordionToggles.filter((toggle) => toggle.getAttribute("aria-expanded") === "true");
    initiallyExpanded.slice(1).forEach((toggle) => setAccordion(toggle, false));
    accordionToggles.forEach((toggle) => {
      toggle.addEventListener("click", () => {
        const wasExpanded = toggle.getAttribute("aria-expanded") === "true";
        if (body.classList.contains("sidebar-collapsed") && window.innerWidth > 991) {
          body.classList.remove("sidebar-collapsed");
          sidebarToggle?.setAttribute("aria-expanded", "true");
          sidebarToggle?.setAttribute("aria-label", "Collapse sidebar");
          hideNavTooltip();
        }
        closeOtherAccordions(toggle);
        setAccordion(toggle, !wasExpanded);
      });
    });
    tooltipTargets.forEach((target) => {
      target.addEventListener("mouseenter", () => showNavTooltip(target));
      target.addEventListener("mouseleave", hideNavTooltip);
      target.addEventListener("focus", () => showNavTooltip(target));
      target.addEventListener("blur", hideNavTooltip);
    });

    document.addEventListener("hims:module-feedback", (event) => {
      notify({ tone: "info", message: event.detail?.message });
    });
    document.addEventListener("hims:notify", (event) => notify(event.detail));
    document.addEventListener("click", (event) => {
      const openButton = event.target.closest("[data-modal-open]");
      if (openButton) {
        event.preventDefault();
        openModal(openButton.dataset.modalOpen, openButton);
        return;
      }
      const closeButton = event.target.closest("[data-modal-close]");
      if (closeButton) {
        closeModal(closeButton.closest(".hims-modal"));
        return;
      }
      const dismissButton = event.target.closest("[data-notification-dismiss]");
      if (dismissButton) {
        dismissButton.closest(".hims-notification")?.remove();
        return;
      }
      const clearButton = event.target.closest("[data-search-clear]");
      if (clearButton) {
        const input = clearButton.closest(".hims-search")?.querySelector('input[type="search"]');
        if (!input) return;
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: true }));
        input.focus();
        return;
      }
      if (activeModal && event.target === activeModal) closeModal(activeModal);
    });
    document.addEventListener("input", (event) => {
      if (!event.target.matches('.hims-search input[type="search"]')) return;
      const clearButton = event.target.closest(".hims-search")?.querySelector("[data-search-clear]");
      if (clearButton) clearButton.hidden = !event.target.value;
    });
    document.addEventListener("change", (event) => {
      if (!event.target.matches("[data-table-select-all]")) return;
      const table = event.target.closest("table");
      table?.querySelectorAll("tbody [data-row-select]").forEach((checkbox) => {
        checkbox.checked = event.target.checked;
        checkbox.closest("tr")?.classList.toggle("is-selected", checkbox.checked);
      });
    });
    document.addEventListener("change", (event) => {
      if (!event.target.matches("[data-row-select]")) return;
      event.target.closest("tr")?.classList.toggle("is-selected", event.target.checked);
      const table = event.target.closest("table");
      const selection = [...(table?.querySelectorAll("tbody [data-row-select]") || [])];
      const selectAll = table?.querySelector("[data-table-select-all]");
      if (selectAll) {
        selectAll.checked = selection.length > 0 && selection.every((checkbox) => checkbox.checked);
        selectAll.indeterminate = selection.some((checkbox) => checkbox.checked) && !selectAll.checked;
      }
    });

    const usesDrawer = () => window.innerWidth <= 991;
    const syncSidebarToggle = () => {
      if (!sidebarToggle) return;
      const expanded = usesDrawer()
        ? body.classList.contains("sidebar-open")
        : !body.classList.contains("sidebar-collapsed");
      sidebarToggle.setAttribute("aria-expanded", String(expanded));
      sidebarToggle.setAttribute(
        "aria-label",
        usesDrawer()
          ? (expanded ? "Close navigation menu" : "Open navigation menu")
          : (expanded ? "Collapse sidebar" : "Expand sidebar"),
      );
    };
    const closeMobileNav = () => {
      body.classList.remove("sidebar-open");
      syncSidebarToggle();
      hideNavTooltip();
    };

    sidebarToggle?.addEventListener("click", () => {
      if (usesDrawer()) {
        body.classList.toggle("sidebar-open");
      } else {
        body.classList.toggle("sidebar-collapsed");
      }
      syncSidebarToggle();
      hideNavTooltip();
    });
    backdrop?.addEventListener("click", closeMobileNav);
    document.querySelector(".sidebar-nav")?.addEventListener("click", (event) => {
      if (window.innerWidth <= 991 && event.target.closest('a[href]:not([aria-disabled="true"])')) closeMobileNav();
    });
    window.addEventListener("resize", () => {
      if (!usesDrawer()) body.classList.remove("sidebar-open");
      syncSidebarToggle();
      hideNavTooltip();
    });
    syncSidebarToggle();

    const getProfileMenuItems = () => [...(profileMenu?.querySelectorAll('[role="menuitem"], [role="menuitemradio"]') || [])];
    const closeProfileMenu = ({ restoreFocus = false } = {}) => {
      if (!profileMenu || !profileToggle) return;
      profileMenu.hidden = true;
      profileToggle.setAttribute("aria-expanded", "false");
      if (restoreFocus) profileToggle.focus();
    };
*** End Patch