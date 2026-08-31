const STORAGE_KEY = 'cookie_consent';
const COOKIE_MAX_AGE_SECONDS = 365 * 24 * 60 * 60;

function setConsentCookie(value) {
    document.cookie = `${STORAGE_KEY}=${value}; path=/; max-age=${COOKIE_MAX_AGE_SECONDS}; SameSite=Lax`;
}

function loadGoogleTagManagerScript(containerId) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtm.js?id=${containerId}`;
    document.head.appendChild(script);
}

export function initCookieConsent() {
    const containerId = window.GTM_CONTAINER_ID;

    if (!containerId || window.localStorage.getItem(STORAGE_KEY)) {
        return;
    }

    const banner = document.querySelector('[data-cookie-consent]');

    if (!banner) {
        return;
    }

    banner.classList.remove('hidden');

    banner.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
        window.localStorage.setItem(STORAGE_KEY, 'accepted');
        setConsentCookie('accepted');
        banner.classList.add('hidden');
        loadGoogleTagManagerScript(containerId);
    });

    banner.querySelector('[data-cookie-reject]')?.addEventListener('click', () => {
        window.localStorage.setItem(STORAGE_KEY, 'rejected');
        setConsentCookie('rejected');
        banner.classList.add('hidden');
    });
}
