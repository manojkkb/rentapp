@php
    $p = $theme['palette'] ?? [];
    $font = $theme['font'] ?? [];
@endphp
<style>
    :root {
        --store-primary: {{ $p['primary'] ?? $theme['accent'] }};
        --store-secondary: {{ $p['secondary'] ?? $theme['accent'] }};
        --store-accent: {{ $theme['accent'] }};
        --store-accent-light: {{ $theme['accent_light'] }};
        --store-accent-soft: {{ $theme['accent_soft'] }};
        --store-accent-dark: {{ $theme['accent_dark'] }};
        --store-accent-ring: {{ $theme['accent_ring'] }};
        --store-accent-rgb: {{ $theme['accent_rgb']['r'] }}, {{ $theme['accent_rgb']['g'] }}, {{ $theme['accent_rgb']['b'] }};
        --store-text: {{ $p['text'] ?? '#374151' }};
        --store-heading: {{ $p['heading'] ?? '#111827' }};
        --store-bg: {{ $p['body_bg'] ?? $theme['background'] }};
        --store-section-bg: {{ $p['section_bg'] ?? $theme['surface'] }};
        --store-surface: {{ $p['card_bg'] ?? $theme['surface'] }};
        --store-header: {{ $p['header_bg'] ?? $theme['header'] }};
        --store-footer: {{ $p['footer_bg'] ?? $theme['footer'] }};
        --store-button: {{ $p['btn_primary_bg'] ?? $theme['button'] }};
        --store-button-text: {{ $p['btn_primary_text'] ?? $theme['button_text'] }};
        --store-button-secondary: {{ $p['btn_secondary_bg'] ?? '#f3f4f6' }};
        --store-button-secondary-text: {{ $p['btn_secondary_text'] ?? '#374151' }};
        --store-button-hover: {{ $p['btn_hover_bg'] ?? $theme['button_hover'] }};
        --store-link: {{ $p['link'] ?? $theme['link'] }};
        --store-link-hover: {{ $p['link_hover'] ?? $theme['link_hover'] }};
        --store-nav-text: {{ $p['nav_text'] ?? '#374151' }};
        --store-nav-hover: {{ $p['nav_hover'] ?? '#111827' }};
        --store-nav-active: {{ $p['nav_active'] ?? $theme['accent'] }};
        --store-mobile-menu-bg: {{ $p['mobile_menu_bg'] ?? '#ffffff' }};
        --store-input-bg: {{ $p['input_bg'] ?? '#ffffff' }};
        --store-input-border: {{ $p['input_border'] ?? '#e5e7eb' }};
        --store-input-focus: {{ $p['input_focus_border'] ?? $theme['accent'] }};
        --store-placeholder: {{ $p['placeholder'] ?? '#9ca3af' }};
        --store-success: {{ $p['success'] ?? '#059669' }};
        --store-warning: {{ $p['warning'] ?? '#d97706' }};
        --store-error: {{ $p['error'] ?? '#dc2626' }};
        --store-info: {{ $p['info'] ?? '#2563eb' }};
        --store-font-family: {!! $font['css_stack'] ?? "'Inter', ui-sans-serif, system-ui, sans-serif" !!};
    }
    body.store-page-bg,
    .store-page-bg { font-family: var(--store-font-family); }
    body.store-page-bg { background-color: var(--store-bg); color: var(--store-text); }
    .store-page-bg h1, .store-page-bg h2, .store-page-bg h3, .store-page-bg h4,
    .store-page-bg .store-theme-boutique-title { font-family: var(--store-font-family); color: var(--store-heading); }
    .store-page-bg .text-gray-900 { color: var(--store-heading); }
    .store-page-bg .text-gray-800, .store-page-bg .text-gray-700, .store-page-bg .text-gray-600 { color: var(--store-text); }
    .store-page-bg .text-gray-500, .store-page-bg .text-gray-400 { color: var(--store-placeholder); }
    .store-page-bg .bg-white:not([class*="bg-white/"]) { background-color: var(--store-surface); }
    .store-page-bg .bg-gray-50 { background-color: var(--store-section-bg); }
    .store-page-bg .border-gray-100, .store-page-bg .border-gray-200 { border-color: var(--store-input-border); }
    .store-booking-strip-active {
        background: linear-gradient(135deg, color-mix(in srgb, var(--store-accent) 20%, var(--store-bg)) 0%, color-mix(in srgb, var(--store-accent) 10%, var(--store-section-bg)) 100%);
        border-bottom: 2px solid var(--store-accent);
        color: var(--store-heading);
    }
    .store-booking-strip-active .store-booking-strip-label { color: var(--store-accent-dark); }
    .store-booking-strip-active .store-booking-strip-meta { color: var(--store-text); }
    .store-booking-strip-active .store-booking-strip-icon {
        background: color-mix(in srgb, var(--store-accent) 22%, var(--store-surface));
        color: var(--store-accent);
    }
    .store-booking-strip-active .store-booking-strip-badge {
        background: var(--store-accent);
        color: var(--store-button-text);
        border: 1px solid color-mix(in srgb, var(--store-accent) 70%, transparent);
    }
    .store-booking-strip-active .store-booking-strip-edit {
        background: var(--store-surface);
        color: var(--store-accent-dark);
        border: 1px solid color-mix(in srgb, var(--store-accent) 30%, var(--store-input-border));
    }
    .store-booking-strip-active .store-booking-strip-edit:hover {
        background: var(--store-accent-soft);
        border-color: var(--store-accent);
    }
    .store-booking-strip-prompt {
        background: linear-gradient(135deg, color-mix(in srgb, var(--store-warning) 16%, var(--store-bg)) 0%, color-mix(in srgb, var(--store-warning) 8%, var(--store-section-bg)) 100%);
        border-bottom: 2px solid color-mix(in srgb, var(--store-warning) 45%, var(--store-input-border));
        color: var(--store-heading);
    }
    .store-booking-strip-prompt-icon {
        background: color-mix(in srgb, var(--store-warning) 24%, var(--store-surface));
        color: var(--store-warning);
    }
    .store-theme-bold .store-booking-strip-active { border-bottom-width: 4px; }
    .store-theme-boutique .store-theme-boutique-title { font-family: var(--store-font-family); }
    .store-theme-neon-card { border-color: color-mix(in srgb, var(--store-accent) 35%, var(--store-input-border)); }
    .store-theme-neon-scroll { scrollbar-width: thin; }
    .store-theme-ocean-wave {
        background: var(--store-bg);
        clip-path: ellipse(75% 100% at 50% 100%);
    }
    .store-theme-ocean-card { border-top: 4px solid var(--store-accent); }
    .store-surface-bg { background-color: var(--store-surface); }
    .store-header-bg { background: {{ ($theme['mode'] ?? '') === 'gradient' ? $theme['hero_gradient'] : 'var(--store-header)' }}; }
    .store-footer-bg { background-color: var(--store-footer); }
    .store-accent-bg { background-color: var(--store-accent); }
    .store-accent-bg-soft { background-color: var(--store-accent-soft); }
    .store-accent-text { color: var(--store-accent); }
    .store-accent-text-dark { color: var(--store-accent-dark); }
    .store-accent-border { border-color: var(--store-accent); }
    .store-link { color: var(--store-link); }
    .store-link:hover { color: var(--store-link-hover); }
    .store-nav-link { color: var(--store-nav-text); }
    .store-nav-link:hover { color: var(--store-nav-hover); }
    .store-nav-link.active { color: var(--store-nav-active); }
    .store-btn-primary { background: var(--store-button); color: var(--store-button-text); }
    .store-btn-primary:hover { background: var(--store-button-hover); }
    .store-btn-secondary { background: var(--store-button-secondary); color: var(--store-button-secondary-text); }
    .store-btn-secondary:hover { filter: brightness(0.95); }
    .store-chip-active { background: var(--store-button); color: var(--store-button-text); border-color: var(--store-button); }
    .store-nav-link { position: relative; }
    .store-nav-link.active::after {
        content: ''; position: absolute; left: 0.75rem; right: 0.75rem; bottom: 0.25rem;
        height: 2px; border-radius: 1px; background: var(--store-nav-active);
    }
    .store-mobile-menu-bg { background-color: var(--store-mobile-menu-bg); }
    .store-hero-gradient { background: {{ $theme['hero_gradient'] }}; }
    .store-site-container { width: 100%; max-width: 72rem; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
    @media (min-width: 640px) { .store-site-container { padding-left: 1.5rem; padding-right: 1.5rem; } }
    .store-input {
        width: 100%; border-radius: 0.5rem; border: 1px solid var(--store-input-border);
        background-color: var(--store-input-bg); color: var(--store-text);
        padding: 0.625rem 0.75rem; font-size: 0.875rem; line-height: 1.25rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .store-input::placeholder { color: var(--store-placeholder); }
    .store-input:focus { border-color: var(--store-input-focus); outline: none; box-shadow: 0 0 0 3px var(--store-accent-ring); }
    .store-alert-success { border-color: var(--store-success); background: color-mix(in srgb, var(--store-success) 12%, white); color: var(--store-success); }
    .store-alert-warning { border-color: var(--store-warning); background: color-mix(in srgb, var(--store-warning) 12%, white); color: var(--store-warning); }
    .store-alert-error { border-color: var(--store-error); background: color-mix(in srgb, var(--store-error) 12%, white); color: var(--store-error); }
    .store-alert-info { border-color: var(--store-info); background: color-mix(in srgb, var(--store-info) 12%, white); color: var(--store-info); }
    .store-hide-scrollbar::-webkit-scrollbar { display: none; }
    .store-hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    [x-cloak] { display: none !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--store-accent) !important; border-color: var(--store-accent) !important; }
    .flatpickr-day.booking-in-range { background: var(--store-accent-soft) !important; border-color: transparent !important; }
    .flatpickr-day.booking-range-start, .flatpickr-day.booking-range-end { background: var(--store-accent) !important; color: #fff !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--store-placeholder); pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; min-height: 40px; cursor: pointer; }
    .time-input-wrapper { position: relative; }
    .time-input-wrapper .time-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--store-placeholder); pointer-events: none; font-size: 14px; }
    .booking-time-select { padding-right: 32px; min-height: 40px; appearance: none; background-image: none; }
    .booking-time-select.is-placeholder { color: var(--store-placeholder); }
</style>
