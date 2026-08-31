function parsePayload(el) {
    const raw = el.dataset.gaPayload;

    if (!raw) {
        return {};
    }

    try {
        return JSON.parse(raw);
    } catch {
        return {};
    }
}

function trackEvent(eventName, params) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: eventName, ...params });
}

function initClickTracking() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-ga-event]');

        if (!trigger || trigger.tagName === 'DETAILS') {
            return;
        }

        trackEvent(trigger.dataset.gaEvent, parsePayload(trigger));
    });
}

function initToggleTracking() {
    document.querySelectorAll('details[data-ga-event]').forEach((details) => {
        details.addEventListener('toggle', () => {
            if (details.open) {
                trackEvent(details.dataset.gaEvent, parsePayload(details));
            }
        });
    });
}

export function initAnalytics() {
    initClickTracking();
    initToggleTracking();
}
