import * as bootstrap from "bootstrap";

window.bootstrap = bootstrap;

document.querySelectorAll("[data-mobile-menu-toggle]").forEach((button) => {
    button.addEventListener("click", () => window.toggleMobileMenu?.());
});

document.querySelector("[data-mobile-destinations-toggle]")?.addEventListener("click", () => {
    window.toggleMobileDestinations?.();
});

document.querySelector("[data-mobile-language-toggle]")?.addEventListener("click", () => {
    window.toggleMobileLanguage?.();
});

document.querySelectorAll(".choose-card").forEach((card) => {
    card.addEventListener("mouseenter", () => {
        card.style.borderColor = "var(--rich-gold)";
        card.style.transform = "translateY(-8px)";
        card.style.boxShadow = "var(--shadow-dramatic)";
    });
    card.addEventListener("mouseleave", () => {
        card.style.borderColor = "transparent";
        card.style.transform = "translateY(0)";
        card.style.boxShadow = "var(--shadow-medium)";
    });
});

// Make listing cards behave like their main link without breaking controls inside them.
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

        if (!link) {
            continue;
        }

        const href = link.getAttribute("href")?.trim();

        if (href && href !== "#" && !href.startsWith("javascript:")) {
            return link;
        }
    }

    return null;
};

document.querySelectorAll(navigationCardSelector).forEach((card) => {
    const mainLink = getCardLink(card);

    if (!mainLink) {
        return;
    }

    card.classList.add("is-navigation-card");

    if (!card.hasAttribute("role")) {
        card.setAttribute("role", "link");
    }

    if (!card.hasAttribute("tabindex")) {
        card.setAttribute("tabindex", "0");
    }

    const openCardLink = (event) => {
        if (mainLink.target === "_blank" || event.ctrlKey || event.metaKey || event.shiftKey) {
            window.open(mainLink.href, "_blank", "noopener");
            return;
        }

        window.location.assign(mainLink.href);
    };

    card.addEventListener("click", (event) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.target.closest(interactiveElementSelector)
        ) {
            return;
        }

        const selection = window.getSelection();
        if (selection && !selection.isCollapsed && selection.toString().trim()) {
            return;
        }

        openCardLink(event);
    });

    card.addEventListener("keydown", (event) => {
        if (event.target !== card || (event.key !== "Enter" && event.key !== " ")) {
            return;
        }

        event.preventDefault();
        openCardLink(event);
    });
});
