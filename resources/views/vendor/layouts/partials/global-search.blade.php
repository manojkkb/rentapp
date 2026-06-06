@php
    $globalSearchUrl = route('vendor.search');
    $globalSearchMinHint = __('vendor.global_search_min_chars');
    $globalSearchNoResults = __('vendor.no_results');
    $globalSearchViewAll = __('vendor.view_all');
@endphp
<div
    x-data="vendorGlobalSearch({
        url: @js($globalSearchUrl),
        minHint: @js($globalSearchMinHint),
        noResults: @js($globalSearchNoResults),
        viewAll: @js($globalSearchViewAll),
    })"
    x-on:keydown.escape.window="closeSearch()"
    class="relative"
>
    {{-- Desktop search --}}
    <div class="hidden lg:block">
        <div class="relative">
            <input type="search"
                   x-ref="desktopInput"
                   x-model="query"
                   @input="onInput()"
                   @focus="open = query.length >= 2 || results.length > 0"
                   @keydown.down.prevent="focusNext()"
                   @keydown.up.prevent="focusPrev()"
                   @keydown.enter.prevent="goActive()"
                   placeholder="{{ __('vendor.search_placeholder') }}"
                   autocomplete="off"
                   class="h-9 w-56 pl-9 pr-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none"></i>
            <span x-show="loading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2">
                <i class="fas fa-spinner fa-spin text-emerald-500 text-sm"></i>
            </span>
        </div>

        <div x-show="open"
             x-cloak
             @click.outside="closeSearch()"
             x-transition
             class="absolute right-0 mt-2 w-[22rem] xl:w-[26rem] bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-[60]">
            @include('vendor.layouts.partials.global-search-results')
        </div>
    </div>

    {{-- Mobile search toggle --}}
    <button type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:hidden"
            @click="mobileOpen = true; $nextTick(() => $refs.mobileInput?.focus())"
            aria-label="{{ __('vendor.search') }}">
        <i class="fas fa-search text-base"></i>
    </button>

    {{-- Mobile search overlay --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[70] lg:hidden">
        <div class="absolute inset-0 bg-black/40" @click="closeSearch()"></div>
        <div class="relative bg-white border-b border-gray-200 shadow-md p-3 safe-area-top">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="search"
                           x-ref="mobileInput"
                           x-model="query"
                           @input="onInput()"
                           @keydown.down.prevent="focusNext()"
                           @keydown.up.prevent="focusPrev()"
                           @keydown.enter.prevent="goActive()"
                           placeholder="{{ __('vendor.search_placeholder') }}"
                           autocomplete="off"
                           class="w-full pl-10 pr-9 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <span x-show="loading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2">
                        <i class="fas fa-spinner fa-spin text-emerald-500 text-sm"></i>
                    </span>
                </div>
                <button type="button" @click="closeSearch()" class="shrink-0 p-2 text-gray-500 hover:text-gray-800">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="relative bg-white max-h-[calc(100dvh-4.5rem)] overflow-y-auto border-b border-gray-200 shadow-lg">
            @include('vendor.layouts.partials.global-search-results')
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vendorGlobalSearch', (config) => ({
        url: config.url,
        minHint: config.minHint,
        noResults: config.noResults,
        viewAll: config.viewAll,
        query: '',
        results: [],
        open: false,
        mobileOpen: false,
        loading: false,
        activeIndex: -1,
        debounceTimer: null,

        flatItems() {
            const items = [];
            this.results.forEach((group, groupIndex) => {
                group.items.forEach((item, itemIndex) => {
                    items.push({ groupIndex, itemIndex, item, group });
                });
            });
            return items;
        },

        onInput() {
            clearTimeout(this.debounceTimer);
            this.activeIndex = -1;

            if (this.query.trim().length < 2) {
                this.results = [];
                this.open = this.mobileOpen;
                this.loading = false;
                return;
            }

            this.open = true;
            this.loading = true;

            this.debounceTimer = setTimeout(() => this.fetchResults(), 300);
        },

        fetchResults() {
            const q = this.query.trim();
            if (q.length < 2) {
                this.loading = false;
                return;
            }

            fetch(`${this.url}?q=${encodeURIComponent(q)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.ok ? response.json() : { groups: [] })
                .then((data) => {
                    this.results = data.groups || [];
                    this.open = true;
                })
                .catch(() => {
                    this.results = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        closeSearch() {
            this.open = false;
            this.mobileOpen = false;
            this.activeIndex = -1;
        },

        focusNext() {
            const flat = this.flatItems();
            if (!flat.length) return;
            this.activeIndex = (this.activeIndex + 1) % flat.length;
        },

        focusPrev() {
            const flat = this.flatItems();
            if (!flat.length) return;
            this.activeIndex = (this.activeIndex - 1 + flat.length) % flat.length;
        },

        goActive() {
            const flat = this.flatItems();
            if (this.activeIndex >= 0 && flat[this.activeIndex]) {
                window.location.href = flat[this.activeIndex].item.url;
                this.closeSearch();
            }
        },

        isActive(groupIndex, itemIndex) {
            const flat = this.flatItems();
            if (this.activeIndex < 0 || !flat[this.activeIndex]) return false;
            return flat[this.activeIndex].groupIndex === groupIndex
                && flat[this.activeIndex].itemIndex === itemIndex;
        },
    }));
});
</script>
