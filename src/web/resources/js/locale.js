/**
 * Fattura Facile - Localization & Persistence Manager
 * Ensures bidirectional synchronization between:
 *  1. localStorage ('locale')
 *  2. document.cookie ('locale')
 *  3. DOM <html lang="...">
 *  4. Livewire SPA navigation and Alpine.js reactivity
 */

export const SUPPORTED_LOCALES = ['it', 'en', 'de', 'fr'];
export const DEFAULT_LOCALE = 'en';
export const COOKIE_NAME = 'locale';

/**
 * Read cookie by name.
 */
export function getCookie(name = COOKIE_NAME) {
    const match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
    return match ? decodeURIComponent(match[3]) : null;
}

/**
 * Write persistent unencrypted cookie with 1-year lifetime.
 */
export function setCookie(name = COOKIE_NAME, value, days = 365) {
    const maxAge = days * 24 * 60 * 60;
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; SameSite=Lax${secure}`;
}

/**
 * Retrieve current active locale from best available source.
 */
export function getActiveLocale() {
    const cookie = getCookie(COOKIE_NAME);
    if (cookie && SUPPORTED_LOCALES.includes(cookie)) {
        return cookie;
    }

    try {
        const stored = localStorage.getItem(COOKIE_NAME);
        if (stored && SUPPORTED_LOCALES.includes(stored)) {
            return stored;
        }
    } catch (e) {}

    const docLang = document.documentElement.lang ? document.documentElement.lang.substring(0, 2).toLowerCase() : null;
    if (docLang && SUPPORTED_LOCALES.includes(docLang)) {
        return docLang;
    }

    return DEFAULT_LOCALE;
}

/**
 * Set active locale across localStorage, Cookie, and DOM, and dispatch event.
 */
export function setAppLocale(locale) {
    if (!SUPPORTED_LOCALES.includes(locale)) {
        return;
    }

    try {
        localStorage.setItem(COOKIE_NAME, locale);
    } catch (e) {}

    setCookie(COOKIE_NAME, locale);
    document.documentElement.setAttribute('lang', locale);

    window.dispatchEvent(new CustomEvent('locale-changed', {
        detail: { locale }
    }));
}

/**
 * Synchronize storage and cookie on initial load or SPA navigation.
 */
export function syncLocale() {
    const active = getActiveLocale();
    try {
        if (localStorage.getItem(COOKIE_NAME) !== active) {
            localStorage.setItem(COOKIE_NAME, active);
        }
    } catch (e) {}

    if (getCookie(COOKIE_NAME) !== active) {
        setCookie(COOKIE_NAME, active);
    }

    if (document.documentElement.getAttribute('lang') !== active) {
        document.documentElement.setAttribute('lang', active);
    }
}

/**
 * Initialize localization integration with Alpine and Livewire.
 */
export function initLocalization() {
    window.setAppLocale = setAppLocale;
    window.getActiveLocale = getActiveLocale;

    syncLocale();

    document.addEventListener('livewire:navigated', () => {
        syncLocale();
    });

    if (window.Alpine) {
        registerAlpineStore(window.Alpine);
    } else {
        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                registerAlpineStore(window.Alpine);
            }
        });
    }
}

function registerAlpineStore(Alpine) {
    if (Alpine.store && !Alpine.store('locale')) {
        Alpine.store('locale', {
            current: getActiveLocale(),
            set(locale) {
                setAppLocale(locale);
                this.current = locale;
            }
        });
    }
}

// Auto-initialize when loaded
if (typeof window !== 'undefined') {
    initLocalization();
}
