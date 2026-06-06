<nav class="shrink-0 border-b border-gray-200 bg-white shadow-sm">
    <div class="flex h-11 items-center justify-between gap-2 px-3 sm:h-12 sm:px-4">
            <!-- Left: Menu Toggle & Breadcrumb -->
            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                <!-- Mobile Menu Toggle -->
                <button @click="toggleSidebar()" 
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-base"></i>
                </button>

                <!-- Breadcrumb -->
                <div class="hidden min-w-0 items-center gap-1.5 text-xs md:flex sm:text-sm">
                    <a wire:navigate href="{{ route('vendor.home') }}" class="text-gray-500 hover:text-emerald-600 transition-colors">
                        <i class="fas fa-home text-sm"></i>
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="truncate font-medium text-gray-700">@yield('page-title', 'Home')</span>
                </div>
            </div>

            <!-- Right: Search, Notifications, Profile -->
            <div class="flex shrink-0 items-center gap-0.5 sm:gap-1 md:gap-2">
                
                @include('vendor.layouts.partials.global-search')

                <!-- Language Selector -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="inline-flex h-8 items-center rounded-md px-1.5 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none sm:px-2">
                        <i class="fas fa-globe text-base"></i>
                        <span class="ml-1.5 hidden text-xs font-medium md:inline">
                            {{ strtoupper($currentLanguage ?? 'en') }}
                        </span>
                    </button>

                    <!-- Language Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        
                        <div class="p-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('vendor.select_language') }}</h3>
                        </div>

                        <div class="py-2 max-h-80 overflow-y-auto">
                            @forelse($availableLanguages ?? [] as $language)
                                <form action="{{ route('vendor.language.switch') }}" method="POST" class="inline w-full">
                                    @csrf
                                    <input type="hidden" name="language" value="{{ $language->code }}">
                                    <button type="submit" 
                                            class="flex items-center justify-between w-full px-4 py-2 text-sm hover:bg-emerald-50 transition-colors {{ ($currentLanguage ?? 'en') == $language->code ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-700' }}">
                                        <div class="flex items-center">
                                            <span class="text-lg mr-3">
                                                @if($language->code == 'en')🇬🇧
                                                @else🇮🇳
                                                @endif
                                            </span>
                                            <div class="text-left">
                                                <div>{{ $language->native_name }}</div>
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

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="relative inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bell text-base"></i>
                        <span class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('vendor.notifications') }}</h3>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            <div class="p-4 text-center text-gray-500 text-sm">
                                {{ __('vendor.no_notifications') }}
                            </div>
                        </div>

                        <div class="p-3 border-t border-gray-200 text-center">
                            <a href="#" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                {{ __('vendor.view_all_notifications') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                @php $navUser = Auth::user(); @endphp
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center gap-1.5 rounded-md px-1.5 py-1 hover:bg-gray-50 focus:outline-none sm:px-2">
                        @if($navUser?->avatar_url)
                            <img src="{{ $navUser->avatar_url }}"
                                 alt=""
                                 class="h-7 w-7 rounded-full object-cover ring-2 ring-emerald-500">
                        @else
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-xs font-semibold text-white">
                                {{ strtoupper(substr($navUser->name ?? __('vendor.user'), 0, 1)) }}
                            </div>
                        @endif
                        <span class="hidden max-w-[7rem] truncate text-xs font-medium text-gray-700 md:block">
                            {{ Str::limit($navUser->name ?? __('vendor.user'), 15) }}
                        </span>
                        <i class="fas fa-chevron-down hidden text-[10px] text-gray-500 md:inline"></i>
                    </button>

                    <!-- Profile Dropdown Menu -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        
                        <!-- Profile Info -->
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                            <div class="flex items-center space-x-3">
                                @if($navUser?->avatar_url)
                                    <img src="{{ $navUser->avatar_url }}"
                                         alt=""
                                         class="w-10 h-10 rounded-full object-cover ring-2 ring-emerald-500 flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                                        {{ strtoupper(substr($navUser->name ?? __('vendor.user'), 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $navUser->name ?? __('vendor.user') }}</p>
                                    <p class="text-xs text-gray-600 truncate">{{ $navUser->mobile ?? '' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a wire:navigate href="{{ route('vendor.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-user w-5"></i>
                                <span class="ml-3">{{ __('vendor.my_profile') }}</span>
                            </a>
                            <a wire:navigate href="{{ route('vendor.select') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-exchange-alt w-5"></i>
                                <span class="ml-3">{{ __('vendor.switch_vendor') }}</span>
                            </a>
                            <a wire:navigate href="{{ route('vendor.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-plus-circle w-5"></i>
                                <span class="ml-3">{{ __('vendor.create_new_vendor') }}</span>
                            </a>
                            @vendorCan('support.view')
                            <a wire:navigate href="{{ route('vendor.support') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-question-circle w-5"></i>
                                <span class="ml-3">{{ __('vendor.help_support') }}</span>
                            </a>
                            @endvendorCan
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-gray-200">
                            <form action="{{ route('vendor.logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="ml-3 font-medium">{{ __('vendor.logout') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</nav>