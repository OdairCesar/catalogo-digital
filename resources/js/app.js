import Swiper from 'swiper';
import { Grid, Pagination } from 'swiper/modules';
import { initAnalytics } from './analytics';
import { initCookieConsent } from './consent';

function initRevealOnScroll() {
    const revealEls = document.querySelectorAll('[data-reveal]');

    if (revealEls.length === 0) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -60px 0px' },
    );

    revealEls.forEach((el) => observer.observe(el));
}

function initHeroParallax() {
    const hero = document.querySelector('[data-hero-parallax]');

    if (!hero || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const onScroll = () => {
        const y = Math.min(window.scrollY, 900);
        hero.style.transform = `translate3d(0, ${(y * 0.14).toFixed(1)}px, 0)`;
    };

    window.addEventListener('scroll', onScroll, { passive: true });
}

function initSwipers() {
    document.querySelectorAll('[data-swiper]').forEach((el) => {
        const options = JSON.parse(el.dataset.swiperOptions || '{}');
        const pagination = el.querySelector('.swiper-pagination');

        new Swiper(el, {
            modules: [Grid, Pagination],
            watchOverflow: true,
            ...options,
            pagination: pagination ? { el: pagination, clickable: true } : false,
        });
    });
}

function initProductGallery() {
    const mainImage = document.querySelector('[data-product-image]');
    const counter = document.querySelector('[data-product-image-counter]');

    document.querySelectorAll('[data-product-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (mainImage && thumb.dataset.image) {
                mainImage.src = thumb.dataset.image;
            }

            if (counter) {
                counter.textContent = `${Number(thumb.dataset.index) + 1} / ${counter.dataset.total}`;
            }
        });
    });
}

function updateProductSelection({ priceLabel, stockLabel, imageUrl }) {
    const priceEl = document.querySelector('[data-product-price]');
    const stickyPriceEl = document.querySelector('[data-product-sticky-price]');
    const stockEl = document.querySelector('[data-product-stock]');
    const imageEl = document.querySelector('[data-product-image]');

    if (priceEl && priceLabel) {
        priceEl.textContent = priceLabel;
    }

    if (stickyPriceEl && priceLabel) {
        stickyPriceEl.textContent = priceLabel;
    }

    if (stockEl && stockLabel) {
        stockEl.textContent = stockLabel;
    }

    if (imageEl && imageUrl) {
        imageEl.src = imageUrl;
    }
}

function updateProductWhatsappLink(waBase, text) {
    if (!waBase) {
        return;
    }

    document.querySelectorAll('[data-product-whatsapp]').forEach((waLink) => {
        waLink.href = `${waBase}?text=${encodeURIComponent(text)}`;
    });
}

function initProductVariantPicker() {
    document.querySelectorAll('[data-product-variants]').forEach((container) => {
        const chips = Array.from(container.querySelectorAll('[data-variant]'));
        const waBase = container.dataset.waBase;
        const productTitle = container.dataset.productTitle;

        chips.forEach((chip) => {
            chip.addEventListener('click', () => {
                chips.forEach((c) => c.classList.remove('is-selected'));
                chip.classList.add('is-selected');

                updateProductSelection({
                    priceLabel: chip.dataset.priceLabel,
                    stockLabel: chip.dataset.stockLabel,
                    imageUrl: chip.dataset.image,
                });

                const label = chip.dataset.label;
                updateProductWhatsappLink(waBase, `Oi Cae! Quero saber mais sobre: ${productTitle}${label ? ' — ' + label : ''}`);
            });
        });
    });
}

function initProductTwoAxisPicker() {
    document.querySelectorAll('[data-product-two-axis]').forEach((container) => {
        const matrix = JSON.parse(container.dataset.matrix || '{}');
        const waBase = container.dataset.waBase;
        const productTitle = container.dataset.productTitle;
        const sizeButtons = Array.from(container.querySelectorAll('[data-size-option]'));
        const colorButtons = Array.from(container.querySelectorAll('[data-color-option]'));

        const selectedLabel = (buttons) => buttons.find((button) => button.classList.contains('is-selected'))?.dataset.label;

        const update = () => {
            const size = selectedLabel(sizeButtons);
            const color = selectedLabel(colorButtons);

            if (size && color) {
                document.querySelectorAll('[data-product-selection-summary]').forEach((el) => {
                    el.textContent = `${color} · ${size}`;
                });
            }

            const variant = size && color ? matrix[`${size}|${color}`] : null;

            if (variant) {
                updateProductSelection(variant);
            }

            if (size && color) {
                updateProductWhatsappLink(waBase, `Oi Cae! Quero o ${productTitle} ${color.toLowerCase()}, tamanho ${size}. Ainda tem?`);
            }
        };

        const bindGroup = (buttons) => {
            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    buttons.forEach((b) => b.classList.remove('is-selected'));
                    button.classList.add('is-selected');
                    update();
                });
            });
        };

        bindGroup(sizeButtons);
        bindGroup(colorButtons);
        update();
    });
}

function initContactForm() {
    const form = document.querySelector('[data-contact-form]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');

        if (!button) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Enviando...';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initRevealOnScroll();
    initHeroParallax();
    initSwipers();
    initProductGallery();
    initProductVariantPicker();
    initProductTwoAxisPicker();
    initContactForm();
    initAnalytics();
    initCookieConsent();
});
