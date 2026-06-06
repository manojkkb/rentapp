<!-- Mobile Bottom Navigation (App-style) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 shadow-lg">
    <div class="flex items-center justify-around h-16">

        @vendorCan('dashboard.view')
        <a wire:navigate href="{{ route('vendor.home') }}"
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.home')
                      ? 'text-emerald-600'
                      : 'text-gray-600 hover:text-emerald-600' }}">
            <i class="fas fa-home text-xl mb-1"></i>
            <span class="text-xs font-medium">{{ __('vendor.home') }}</span>
        </a>
        @endvendorCan

        @vendorCan('orders.view')
        <a wire:navigate href="{{ route('vendor.orders.index') }}"
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.orders.index', 'vendor.orders.show')
                      ? 'text-emerald-600'
                      : 'text-gray-600 hover:text-emerald-600' }} relative">
            <i class="fas fa-receipt text-xl mb-1"></i>
            <span class="text-xs font-medium">{{ __('vendor.orders') }}</span>
        </a>
        @endvendorCan

        @vendorCan('orders.create')
        @php
            $onOrderWizard = str_starts_with(request()->route()?->getName() ?? '', 'vendor.orders.create')
                || (request()->route()?->getName() ?? '') === 'vendor.orders.new';
        @endphp
        <a wire:navigate href="{{ route('vendor.orders.new') }}"
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ $onOrderWizard ? 'text-emerald-600' : 'text-gray-600 hover:text-emerald-600' }}">
            <i class="fas fa-file-circle-plus text-xl mb-1"></i>
            <span class="text-xs font-medium leading-tight text-center px-0.5">{{ __('vendor.create_order') }}</span>
        </a>
        @endvendorCan

        <div class="relative flex-1 h-full" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex flex-col items-center justify-center w-full h-full transition-colors text-gray-600 hover:text-emerald-600">
                <i class="fas fa-globe text-xl mb-1"></i>
                <span class="text-xs font-medium uppercase">{{ strtoupper($currentLanguage ?? 'en') }}</span>
            </button>

            <div x-show="open"
                 @click.away="open = false"
                 x-transition
                 class="absolute bottom-full right-0 mb-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                 style="display: none;">

                <div class="p-3 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('vendor.select_language') }}</h3>
                </div>

                <div class="py-2 max-h-80 overflow-y-auto">
                    @forelse($availableLanguages ?? [] as $language)
                        <form action="{{ route('vendor.language.switch') }}" method="POST" class="inline w-full">
                            @csrf
                            <input type="hidden" name="language" value="{{ $language->code }}">
                            <button type="submit"
                                    class="flex items-center justify-between w-full px-4 py-3 text-sm hover:bg-emerald-50 transition-colors {{ ($currentLanguage ?? 'en') == $language->code ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-700' }}">
                                <div class="flex items-center">
                                    <span class="text-xl mr-3">
                                        @if($language->code == 'en')🇬🇧
                                        @else🇮🇳
                                        @endif
                                    </span>
                                    <div class="text-left">
                                        <div class="font-medium">{{ $language->native_name }}</div>
                                        @if($language->native_name != $language->name)
                                            <div class="text-xs text-gray-500">{{ $language->name }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if(($currentLanguage ?? 'en') == $language->code)
                                    <i class="fas fa-check text-emerald-600"></i>
                                @endif
                            </button>
                        </form>
                    @empty
                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                            {{ __('vendor.no_languages') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <button @click="toggleSidebar()"
                class="flex flex-col items-center justify-center flex-1 h-full transition-colors text-gray-600 hover:text-emerald-600">
            <i class="fas fa-bars text-xl mb-1"></i>
            <span class="text-xs font-medium">{{ __('vendor.menu') }}</span>
        </button>

    </div>
</nav>
