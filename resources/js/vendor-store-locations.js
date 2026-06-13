/**
 * Online Store — location add/edit modal with map pin & geolocation.
 */

import './lib/leaflet';

const DEFAULT_CENTER = { lat: 20.5937, lng: 78.9629, zoom: 5 };

function parseCoord(value, fallback) {
    const n = parseFloat(value);
    return Number.isFinite(n) ? n : fallback;
}

function registerVendorStoreLocations() {
    window.Alpine.data('vendorStoreLocations', (config) => ({
        showModal: false,
        mode: 'add',
        formAction: config.storeUrl,
        geoLoading: false,
        geoError: '',
        map: null,
        marker: null,
        form: {
            name: '',
            address_line1: '',
            address_line2: '',
            city: '',
            state: '',
            postal_code: '',
            country: config.defaultCountry || 'India',
            phone: config.defaultPhone || '',
            latitude: '',
            longitude: '',
            is_default: false,
            is_active: true,
        },

        openAdd() {
            this.mode = 'add';
            this.formAction = config.storeUrl;
            this.geoError = '';
            this.resetForm();
            this.form.is_default = config.locationCount === 0;
            this.showModal = true;
            this.$nextTick(() => {
                this.initMap();
                setTimeout(() => this.map?.invalidateSize(), 200);
            });
        },

        openEdit(location) {
            this.mode = 'edit';
            this.formAction = location.update_url;
            this.geoError = '';
            this.form = {
                name: location.name || '',
                address_line1: location.address_line1 || '',
                address_line2: location.address_line2 || '',
                city: location.city || '',
                state: location.state || '',
                postal_code: location.postal_code || '',
                country: location.country || config.defaultCountry || 'India',
                phone: location.phone || '',
                latitude: location.latitude != null ? String(location.latitude) : '',
                longitude: location.longitude != null ? String(location.longitude) : '',
                is_default: !!location.is_default,
                is_active: location.is_active !== false,
            };
            this.showModal = true;
            this.$nextTick(() => {
                this.initMap();
                setTimeout(() => this.map?.invalidateSize(), 200);
            });
        },

        closeModal() {
            this.destroyMap();
            this.showModal = false;
            this.geoError = '';
        },

        resetForm() {
            const v = config.vendorAddress || {};
            this.form = {
                name: '',
                address_line1: v.address_line1 || '',
                address_line2: v.address_line2 || '',
                city: v.city || '',
                state: v.state || '',
                postal_code: v.postal_code || '',
                country: v.country || config.defaultCountry || 'India',
                phone: config.defaultPhone || '',
                latitude: v.latitude != null ? String(v.latitude) : '',
                longitude: v.longitude != null ? String(v.longitude) : '',
                is_default: false,
                is_active: true,
            };
        },

        hasCoords() {
            return this.form.latitude !== '' && this.form.longitude !== '';
        },

        setCoords(lat, lng) {
            this.form.latitude = Number(lat).toFixed(7);
            this.form.longitude = Number(lng).toFixed(7);
            this.syncMarkerFromForm();
        },

        syncMarkerFromForm() {
            if (!this.map || !this.marker || !this.hasCoords()) {
                return;
            }
            const lat = parseCoord(this.form.latitude, null);
            const lng = parseCoord(this.form.longitude, null);
            if (lat === null || lng === null) {
                return;
            }
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], Math.max(this.map.getZoom(), 14));
        },

        onCoordInput() {
            this.syncMarkerFromForm();
        },

        useCurrentLocation() {
            this.geoError = '';
            if (!navigator.geolocation) {
                this.geoError = config.labels.geoUnsupported;
                return;
            }
            this.geoLoading = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.setCoords(pos.coords.latitude, pos.coords.longitude);
                    this.geoLoading = false;
                },
                () => {
                    this.geoError = config.labels.geoDenied;
                    this.geoLoading = false;
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
            );
        },

        initMap() {
            if (typeof window.L === 'undefined' || !this.$refs.mapEl) {
                return;
            }
            this.destroyMap();

            const hasPin = this.hasCoords();
            const lat = hasPin ? parseCoord(this.form.latitude, DEFAULT_CENTER.lat) : DEFAULT_CENTER.lat;
            const lng = hasPin ? parseCoord(this.form.longitude, DEFAULT_CENTER.lng) : DEFAULT_CENTER.lng;
            const zoom = hasPin ? 15 : DEFAULT_CENTER.zoom;

            this.map = window.L.map(this.$refs.mapEl, { scrollWheelZoom: true }).setView([lat, lng], zoom);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(this.map);

            this.marker = window.L.marker([lat, lng], { draggable: true }).addTo(this.map);

            this.marker.on('dragend', () => {
                const { lat: mLat, lng: mLng } = this.marker.getLatLng();
                this.setCoords(mLat, mLng);
            });

            this.map.on('click', (e) => {
                this.setCoords(e.latlng.lat, e.latlng.lng);
            });
        },

        destroyMap() {
            if (this.map) {
                this.map.remove();
                this.map = null;
                this.marker = null;
            }
        },

        mapsLink(lat, lng) {
            return `https://www.google.com/maps?q=${lat},${lng}`;
        },
    }));
}

if (window.Alpine) {
    registerVendorStoreLocations();
} else {
    document.addEventListener('alpine:init', registerVendorStoreLocations);
}
