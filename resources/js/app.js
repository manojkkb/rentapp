import './bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/** Vendor dashboard: mobile install banner (PWA not in standalone). */
Alpine.data('vendorDashboardPwaInstall', () => ({
    show: false,
    canPrompt: false,
    deferredPrompt: null,
    isIOS: false,
    _onBeforeInstall: null,
    _onResize: null,

    isInstalled() {
        const standalone = window.matchMedia('(display-mode: standalone)').matches
            || window.matchMedia('(display-mode: fullscreen)').matches
            || window.matchMedia('(display-mode: minimal-ui)').matches;
        const iosStandalone = window.navigator.standalone === true;
        return standalone || iosStandalone;
    },

    isMobileViewport() {
        return window.matchMedia('(max-width: 767px)').matches;
    },

    init() {
        if (!this.isMobileViewport() || this.isInstalled()) {
            return;
        }

        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        this.show = true;

        this._onBeforeInstall = (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.canPrompt = true;
        };
        window.addEventListener('beforeinstallprompt', this._onBeforeInstall);

        this._onResize = () => {
            if (!this.isMobileViewport() || this.isInstalled()) {
                this.show = false;
            } else if (!this.isInstalled()) {
                this.show = true;
            }
        };
        window.addEventListener('resize', this._onResize);
    },

    destroy() {
        if (this._onBeforeInstall) {
            window.removeEventListener('beforeinstallprompt', this._onBeforeInstall);
        }
        if (this._onResize) {
            window.removeEventListener('resize', this._onResize);
        }
    },

    async install() {
        if (!this.deferredPrompt) {
            return;
        }
        this.deferredPrompt.prompt();
        await this.deferredPrompt.userChoice;
        this.deferredPrompt = null;
        this.canPrompt = false;
        if (this.isInstalled()) {
            this.show = false;
        }
    },
}));

Alpine.start();
