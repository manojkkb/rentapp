import './bootstrap';
import './lib/flatpickr';

// Category image square crop (Croppie) — bundled here so it always loads with the vendor app
import './category-image-crop';
import './vendor/item-variant-form';
import './order-wizard-datetime';
import './order-booking-dates';
import './order-fulfillment-datetime';
import './order-wizard-step-one';
import './order-wizard-errors';
import './order-wizard-items';
import './order-wizard-summary';
import './order-wizard-payment';
import './vendor-store-locations';
import './vendor-rich-text';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

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

if (!window.__rentkiaLivewireStarted) {
    window.__rentkiaLivewireStarted = true;
    Livewire.start();
}

(function initVendorNavigateProgress() {
    const wrap = () => document.getElementById('vendor-nav-progress');
    const bar = () => document.getElementById('vendor-nav-progress-bar');

    document.addEventListener('livewire:navigate', () => {
        const w = wrap();
        const b = bar();
        if (!w || !b) return;
        w.classList.remove('opacity-0');
        b.style.width = '35%';
    });

    document.addEventListener('livewire:navigating', () => {
        const b = bar();
        if (b) b.style.width = '75%';
    });

    document.addEventListener('livewire:navigated', () => {
        const w = wrap();
        const b = bar();
        if (!w || !b) return;
        b.style.width = '100%';
        window.setTimeout(() => {
            w.classList.add('opacity-0');
            b.style.width = '0%';
        }, 180);
    });
})();
