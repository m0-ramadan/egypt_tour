// Bootstrap's JavaScript is only needed by the booking modal on package pages.
// Keep it out of the home-page bundle and fetch it on the pages that use it.
window.bootstrap = window.bootstrap || {};
if (document.getElementById("mobileBookingModal")) {
    import("bootstrap/js/dist/modal").then(({ default: Modal }) => {
        window.bootstrap.Modal = Modal;
    });
}

/* ==========================================================================
   1. NAVIGATION & MOBILE MENU
   ========================================================================== */
export function toggleMobileMenu() {
    const mobileMenu = document.getElementById("modernMobileMenu");
    const hamburger = document.getElementById("hamburger");
    if (!mobileMenu || !hamburger) return;

    mobileMenu.classList.toggle("active");
    hamburger.classList.toggle("active");
    const isOpen = mobileMenu.classList.contains("active");

    document.querySelectorAll("[data-mobile-menu-toggle]").forEach((button) => {
        button.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    if (isOpen) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
        closeAllSubmenus();
    }
}

export function toggleMobileDestinations() {
    const submenu = document.getElementById("mobileDestinationsSubmenu");
    const icon = document.querySelector(".mobile-destinations-toggle i.chevron");
    const toggle = document.querySelector("[data-mobile-destinations-toggle]");
    if (!submenu || !toggle) return;

    submenu.classList.toggle("active");
    icon?.classList.toggle("rotated");
    toggle.setAttribute("aria-expanded", submenu.classList.contains("active") ? "true" : "false");

    const languageSubmenu = document.getElementById("mobileLanguageSubmenu");
    const languageIcon = document.querySelector(".mobile-language-toggle i.chevron");
    if (languageSubmenu?.classList.contains("active")) {
        languageSubmenu.classList.remove("active");
        languageIcon?.classList.remove("rotated");
        document.querySelector("[data-mobile-language-toggle]")?.setAttribute("aria-expanded", "false");
    }
}

export function toggleMobileLanguage() {
    const submenu = document.getElementById("mobileLanguageSubmenu");
    const icon = document.querySelector(".mobile-language-toggle i.chevron");
    const toggle = document.querySelector("[data-mobile-language-toggle]");
    if (!submenu || !toggle) return;

    submenu.classList.toggle("active");
    icon?.classList.toggle("rotated");
    toggle.setAttribute("aria-expanded", submenu.classList.contains("active") ? "true" : "false");

    const destinationsSubmenu = document.getElementById("mobileDestinationsSubmenu");
    const destinationsIcon = document.querySelector(".mobile-destinations-toggle i.chevron");
    if (destinationsSubmenu?.classList.contains("active")) {
        destinationsSubmenu.classList.remove("active");
        destinationsIcon?.classList.remove("rotated");
        document.querySelector("[data-mobile-destinations-toggle]")?.setAttribute("aria-expanded", "false");
    }
}

export function closeAllSubmenus() {
    const submenus = document.querySelectorAll(".mobile-destinations-submenu, .mobile-language-submenu");
    const icons = document.querySelectorAll(
        ".mobile-destinations-toggle i.chevron, .mobile-language-toggle i.chevron"
    );

    submenus.forEach((submenu) => submenu.classList.remove("active"));
    icons.forEach((icon) => icon.classList.remove("rotated"));
    document
        .querySelectorAll("[data-mobile-destinations-toggle], [data-mobile-language-toggle]")
        .forEach((toggle) => toggle.setAttribute("aria-expanded", "false"));
}

window.toggleMobileMenu = toggleMobileMenu;
window.toggleMobileDestinations = toggleMobileDestinations;
window.toggleMobileLanguage = toggleMobileLanguage;
window.closeAllSubmenus = closeAllSubmenus;

const initNavigation = () => {
    // Mobile menu toggle listeners
    document.querySelectorAll("[data-mobile-menu-toggle]").forEach((button) => {
        button.addEventListener("click", toggleMobileMenu);
    });

    document.querySelector("[data-mobile-destinations-toggle]")?.addEventListener("click", toggleMobileDestinations);
    document.querySelector("[data-mobile-language-toggle]")?.addEventListener("click", toggleMobileLanguage);

    // Close on ESC key
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            const mobileMenu = document.getElementById("modernMobileMenu");
            if (mobileMenu?.classList.contains("active")) {
                toggleMobileMenu();
            }
        }
    });

    // Navbar scroll effect
    const navbar = document.querySelector(".navbar");
    if (navbar) {
        let navbarScrollFrame = null;
        window.addEventListener(
            "scroll",
            () => {
                if (navbarScrollFrame !== null) return;
                navbarScrollFrame = window.requestAnimationFrame(() => {
                    navbar.classList.toggle("scrolled", window.scrollY > 50);
                    navbarScrollFrame = null;
                });
            },
            { passive: true }
        );
    }

    // Hover dropdowns for desktop
    document.querySelectorAll(".navbar .destinations-dropdown").forEach((dropdown) => {
        const toggle = dropdown.querySelector("[data-navbar-dropdown-toggle]");
        const menu = dropdown.querySelector(".dropdown-menu");
        if (!toggle || !menu) return;

        dropdown.addEventListener("mouseenter", () => {
            dropdown.classList.add("show");
            menu.classList.add("show");
            toggle.setAttribute("aria-expanded", "true");
        });

        dropdown.addEventListener("mouseleave", () => {
            dropdown.classList.remove("show");
            menu.classList.remove("show");
            toggle.setAttribute("aria-expanded", "false");
        });
    });

    // Desktop navbar dropdown click fallback
    document.querySelectorAll(".navbar [data-navbar-dropdown-toggle]").forEach((toggle) => {
        toggle.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const dropdown = this.closest(".dropdown");
            const menu = dropdown ? dropdown.querySelector(".dropdown-menu") : null;
            if (!dropdown || !menu) return;

            document.querySelectorAll(".navbar .dropdown").forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove("show");
                    const otherMenu = item.querySelector(".dropdown-menu");
                    const otherToggle = item.querySelector("[data-navbar-dropdown-toggle]");
                    if (otherMenu) otherMenu.classList.remove("show");
                    if (otherToggle) otherToggle.setAttribute("aria-expanded", "false");
                }
            });

            dropdown.classList.toggle("show");
            menu.classList.toggle("show");
            const isOpen = menu.classList.contains("show");
            this.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
    });

    document.addEventListener("click", (event) => {
        if (event.target.closest(".navbar .dropdown")) return;
        document.querySelectorAll(".navbar .dropdown").forEach((dropdown) => {
            dropdown.classList.remove("show");
            const menu = dropdown.querySelector(".dropdown-menu");
            const toggle = dropdown.querySelector("[data-navbar-dropdown-toggle]");
            if (menu) menu.classList.remove("show");
            if (toggle) toggle.setAttribute("aria-expanded", "false");
        });
    });

    // Smooth scroll for hash links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            const href = this.getAttribute("href");
            if (!href || href === "#" || href === "#!") return;
            const target = document.querySelector(href);
            if (!target) return;

            e.preventDefault();
            const offsetTop = target.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({
                top: offsetTop,
                behavior: "smooth",
            });
        });
    });
};

/* ==========================================================================
   2. THEME SWITCHER
   ========================================================================== */
const initTheme = () => {
    const themeStorageKey = "website-theme";
    const themeColorMeta = document.querySelector("[data-theme-color-meta]");

    function updateThemeButtons(theme) {
        document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
            const icon = button.querySelector("i");
            const isDark = theme === "dark";
            const nextLabel = isDark ? button.dataset.lightLabel : button.dataset.darkLabel;

            button.setAttribute("aria-label", nextLabel || (isDark ? "Light Mode" : "Dark Mode"));
            button.setAttribute("title", nextLabel || (isDark ? "Light Mode" : "Dark Mode"));
            button.setAttribute("aria-pressed", isDark ? "true" : "false");

            if (icon) {
                icon.className = "la " + (isDark ? "la-sun" : "la-moon");
            }
        });
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
        document.documentElement.style.colorScheme = theme;
        if (themeColorMeta) {
            themeColorMeta.setAttribute("content", theme === "dark" ? "#111111" : "#1c1c1c");
        }
        updateThemeButtons(theme);
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";
        const nextTheme = currentTheme === "dark" ? "light" : "dark";
        try {
            localStorage.setItem(themeStorageKey, nextTheme);
        } catch (e) {}
        applyTheme(nextTheme);
    }

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        button.addEventListener("click", toggleTheme);
    });

    const savedTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";
    updateThemeButtons(savedTheme);

    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    if (typeof mediaQuery.addEventListener === "function") {
        mediaQuery.addEventListener("change", (event) => {
            try {
                if (localStorage.getItem(themeStorageKey)) return;
            } catch (e) {}
            applyTheme(event.matches ? "dark" : "light");
        });
    }
};

/* ==========================================================================
   3. RIPPLE EFFECT ON BUTTONS (EVENT DELEGATION)
   ========================================================================== */
const initRipples = () => {
    document.addEventListener("click", (e) => {
        const button = e.target.closest(".mobile-action-btn, .btn-tailor, .mobile-enquiry-btn2, .action-btn, .gold-btn");
        if (!button) return;

        const circle = document.createElement("span");
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        const rect = button.getBoundingClientRect();

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${e.clientX - rect.left - radius}px`;
        circle.style.top = `${e.clientY - rect.top - radius}px`;
        circle.classList.add("ripple");

        const ripple = button.querySelector(".ripple");
        if (ripple) ripple.remove();

        button.appendChild(circle);
    });
};

/* ==========================================================================
   4. NAVIGATION CARD DELEGATION
   ========================================================================== */
const navigationCardSelector = [
    ".deal-card",
    ".destination-card",
    ".article-card",
    ".journey-card",
    ".modern-blog-card",
    ".related-article-card",
    ".attraction-card",
    ".attraction-card-item",
    ".cruise-card",
    ".cruise-type-card",
    ".tour-card",
    ".offer-card",
    ".result-card",
    ".cat-card",
    ".related-card",
].join(",");

const primaryLinkSelectors = [
    "[data-card-primary-link][href]",
    ".journey-title a[href]",
    ".deal-title a[href]",
    ".destination-title a[href]",
    ".article-title a[href]",
    ".blog-card-title a[href]",
    ".related-title a[href]",
    ".attraction-title a[href]",
    ".attraction-card-title a[href]",
    ".cruise-title a[href]",
    ".tour-title a[href]",
    ".offer-title a[href]",
    ".card-title-link[href]",
    ".cruise-btn[href]",
    ".cat-btn[href]",
    ".related-card-body a[href]",
    "a[href]",
];

const interactiveElementSelector = [
    "a",
    "button",
    "input",
    "select",
    "textarea",
    "label",
    "form",
    "summary",
    "[role='button']",
    "[data-bs-toggle]",
].join(",");

const getCardLink = (card) => {
    for (const selector of primaryLinkSelectors) {
        const link = card.querySelector(selector);
        if (!link) continue;
        const href = link.getAttribute("href")?.trim();
        if (href && href !== "#" && !href.startsWith("javascript:")) {
            return link;
        }
    }
    return null;
};

const openCardLink = (mainLink, event) => {
    if (mainLink.target === "_blank" || event.ctrlKey || event.metaKey || event.shiftKey) {
        window.open(mainLink.href, "_blank", "noopener");
        return;
    }
    window.location.assign(mainLink.href);
};

const initCardDelegation = () => {
    document.addEventListener("click", (event) => {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.target.closest(interactiveElementSelector)) return;

        const card = event.target.closest(navigationCardSelector);
        if (!card) return;

        const selection = window.getSelection();
        if (selection && !selection.isCollapsed && selection.toString().trim()) return;

        const mainLink = getCardLink(card);
        if (mainLink) {
            openCardLink(mainLink, event);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        const card = event.target.closest(navigationCardSelector);
        if (!card || event.target !== card) return;

        const mainLink = getCardLink(card);
        if (mainLink) {
            event.preventDefault();
            openCardLink(mainLink, event);
        }
    });
};

/* ==========================================================================
   5. REVEAL-UP ON-SCROLL ANIMATIONS
   ========================================================================== */
const initReveal = () => {
    const revealItems = document.querySelectorAll(".reveal-up");
    if (revealItems.length === 0) return;

    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.05,
                rootMargin: "0px 0px 50px 0px",
            }
        );

        revealItems.forEach((item, index) => {
            item.style.transitionDelay = (index % 4) * 60 + "ms";
            observer.observe(item);
        });
    } else {
        revealItems.forEach((item) => {
            item.classList.add("is-visible");
        });
    }
};

/* ==========================================================================
   6. TESTIMONIALS SLIDER
   ========================================================================== */
const initTestimonialsSlider = () => {
    const slider = document.getElementById("testimonialsSlider");
    if (!slider) return;

    const prevBtn = document.querySelector(".testimonials-nav-btn.prev-btn");
    const nextBtn = document.querySelector(".testimonials-nav-btn.next-btn");
    const dotsContainer = document.getElementById("testimonialsDots");

    const cards = slider.querySelectorAll(".testimonial-card");
    if (cards.length === 0) return;

    function updateDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = "";
        const cardWidth = cards[0].offsetWidth + 24;
        const visibleCount = Math.round(slider.offsetWidth / cardWidth) || 1;
        const totalPages = Math.ceil(cards.length / visibleCount);

        if (totalPages <= 1) {
            dotsContainer.style.display = "none";
            return;
        }
        dotsContainer.style.display = "flex";

        const currentPage = Math.round(slider.scrollLeft / (cardWidth * visibleCount));

        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.classList.add("testimonials-dot");
            if (i === currentPage) dot.classList.add("active");
            dot.setAttribute("aria-label", "Go to page " + (i + 1));
            dot.addEventListener("click", () => {
                slider.scrollTo({
                    left: i * cardWidth * visibleCount,
                    behavior: "smooth",
                });
            });
            dotsContainer.appendChild(dot);
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            const cardWidth = cards[0].offsetWidth + 24;
            const isRtl = document.documentElement.getAttribute("dir") === "rtl";
            slider.scrollBy({
                left: isRtl ? cardWidth : -cardWidth,
                behavior: "smooth",
            });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            const cardWidth = cards[0].offsetWidth + 24;
            const isRtl = document.documentElement.getAttribute("dir") === "rtl";
            slider.scrollBy({
                left: isRtl ? -cardWidth : cardWidth,
                behavior: "smooth",
            });
        });
    }

    // Mouse drag scrolling
    let isDown = false;
    let startX = 0;
    let scrollLeftStart = 0;
    let dragged = false;

    slider.addEventListener("mousedown", (e) => {
        isDown = true;
        dragged = false;
        startX = e.pageX - slider.offsetLeft;
        scrollLeftStart = slider.scrollLeft;
        slider.style.cursor = "grabbing";
        slider.style.userSelect = "none";
        e.preventDefault();
    });

    window.addEventListener("mouseup", () => {
        if (!isDown) return;
        isDown = false;
        slider.style.cursor = "grab";
        slider.style.userSelect = "";
    });

    slider.addEventListener("mouseleave", () => {
        if (isDown) {
            isDown = false;
            slider.style.cursor = "grab";
            slider.style.userSelect = "";
        }
    });

    slider.addEventListener("mousemove", (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.5;
        if (Math.abs(walk) > 5) dragged = true;
        slider.scrollLeft = scrollLeftStart - walk;
    });

    slider.addEventListener(
        "click",
        (e) => {
            if (dragged) {
                e.stopPropagation();
                e.preventDefault();
                dragged = false;
            }
        },
        true
    );

    slider.style.cursor = "grab";

    let scrollTimer;
    slider.addEventListener("scroll", () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(updateDots, 100);
    });

    window.addEventListener("resize", updateDots);
    updateDots();
};

/* ==========================================================================
   INITIALIZATION
   ========================================================================== */
const initAll = () => {
    initNavigation();
    initTheme();
    initRipples();
    initCardDelegation();
    initReveal();
    initTestimonialsSlider();
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
} else {
    initAll();
}
