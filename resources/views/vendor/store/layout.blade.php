@extends('vendor.layouts.app')

@section('title', ($sectionTitle ?? __('vendor.online_store')).' — '.__('vendor.online_store'))
@section('page-title', $sectionTitle ?? __('vendor.online_store'))
@section('main_bottom_class', 'pb-4 md:pb-6')

@section('styles')
    <style>
        .leaflet-container { z-index: 0; font-family: inherit; }
        [x-cloak] { display: none !important; }
        .store-section-panel { animation: storeSectionIn 0.22s ease-out; }
        @keyframes storeSectionIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .store-mobile-nav-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .store-mobile-nav-scroll::-webkit-scrollbar { display: none; }
        @media (max-width: 1023px) {
            .store-settings-shell { border-radius: 0.75rem; }
            .store-settings-shell input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="hidden"]):not([type="range"]),
            .store-settings-shell select,
            .store-settings-shell textarea {
                min-height: 2.75rem;
                font-size: 1rem;
            }
            .store-settings-shell .rich-text-editor-wrap .ql-editor {
                font-size: 1rem;
            }
        }
    </style>
    @stack('store-styles')
@endsection

@section('content')
    <div class="mx-auto max-w-6xl space-y-3 sm:space-y-4">
        @if(session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm font-medium text-amber-900 sm:px-4 sm:py-3">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-medium text-emerald-900 sm:px-4 sm:py-3">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800 sm:px-4 sm:py-3">
                <p class="font-semibold">{{ __('vendor.fix_errors') }}</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="store-settings-shell overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-3 py-3 sm:px-6 sm:py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.online_store') }}</p>
                        <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ $sectionTitle }}</h1>
                        <p class="mt-1 text-sm text-gray-600 lg:hidden">{{ __('vendor.store_subtitle') }}</p>
                        <p class="mt-0.5 hidden text-sm text-gray-600 lg:block">{{ __('vendor.store_subtitle') }}</p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:shrink-0 sm:items-end">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($storeIsLive ?? false)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                    <i class="fas fa-circle text-[6px]" aria-hidden="true"></i>
                                    {{ __('vendor.store_live') }}
                                </span>
                            @elseif($store->is_published)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900" title="{{ implode(', ', $storeLiveBlockers ?? []) }}">
                                    {{ __('vendor.store_published_incomplete') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                    {{ __('vendor.store_draft') }}
                                </span>
                            @endif
                        </div>
                        @if($storeIsLive ?? false)
                            <a href="{{ $storeUrl }}" target="_blank" rel="noopener"
                               class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:h-10 sm:w-auto">
                                <i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i>
                                {{ __('vendor.store_open_live') }}
                            </a>
                        @else
                            <p class="w-full truncate rounded-lg bg-gray-100 px-3 py-2 text-xs text-gray-500 sm:max-w-xs" title="{{ implode(', ', $storeLiveBlockers ?? []) }}">
                                {{ $storeUrl }}
                            </p>
                        @endif
                    </div>
                </div>
                @if(! ($storeIsLive ?? false) && ! empty($storeLiveBlockers ?? []))
                    <p class="mt-2 text-xs text-amber-800">
                        {{ __('vendor.store_live_blocked') }}
                        {{ implode(', ', $storeLiveBlockers) }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col lg:flex-row lg:items-start">
                <aside class="hidden w-56 shrink-0 border-r border-gray-200 bg-gray-50/60 lg:sticky lg:top-4 lg:block lg:self-start xl:w-60">
                    <nav class="p-4" aria-label="{{ __('vendor.online_store') }}">
                        <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.store_settings_menu') }}</p>
                        <div class="flex flex-col gap-0.5">
                            @foreach($storeSections as $key => $tab)
                                <a href="{{ route($tab['route']) }}"
                                   wire:navigate
                                   class="flex w-full items-center gap-2 rounded-md px-3 py-2.5 text-left text-sm font-semibold transition {{ ($section ?? '') === $key ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'text-gray-600 hover:bg-white/80 hover:text-gray-900' }}">
                                    <i class="fas {{ $tab['icon'] }} w-4 shrink-0 text-center text-xs" aria-hidden="true"></i>
                                    <span>{{ \App\Support\VendorStoreSections::label($key) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </nav>
                </aside>

                <div class="min-w-0 flex-1">
                    <div class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur-sm lg:hidden">
                        <p class="px-3 pt-2.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('vendor.store_settings_menu') }}
                        </p>
                        <nav class="store-mobile-nav-scroll flex gap-2 overflow-x-auto px-3 py-2.5" aria-label="{{ __('vendor.online_store') }}">
                            @foreach($storeSections as $key => $tab)
                                <a href="{{ route($tab['route']) }}"
                                   wire:navigate
                                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition {{ ($section ?? '') === $key ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    <i class="fas {{ $tab['icon'] }} text-[10px]" aria-hidden="true"></i>
                                    <span>{{ \App\Support\VendorStoreSections::label($key) }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="p-3 sm:p-6 max-lg:pb-28">
                        @yield('store-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @stack('store-scripts')
    @yield('store-scripts')
@endsection
