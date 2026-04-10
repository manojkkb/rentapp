@php
    $sidebarVendor = Auth::user()->currentVendor();
@endphp
<aside
    x-show="sidebarOpen"
    @click.away="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed md:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 transform md:translate-x-0 transition-transform duration-300 flex flex-col"
>
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between gap-2 p-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
        <div class="flex items-center min-w-0 flex-1 gap-3">
            @if($sidebarVendor && $sidebarVendor->logo_url)
                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border-2 border-emerald-500 bg-white shadow-sm flex items-center justify-center p-1">
                    <img src="{{ $sidebarVendor->logo_url }}"
                         alt="{{ $sidebarVendor->name }}"
                         width="48"
                         height="48"
                         class="max-h-full max-w-full object-contain object-center"
                         loading="lazy"
                         decoding="async">
                </div>
            @else
                <div class="h-12 w-12 shrink-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-sm">
                    <span class="text-white font-bold text-xl leading-none">
                        {{ strtoupper(substr($sidebarVendor->name ?? 'V', 0, 1)) }}
                    </span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h2 class="text-sm font-bold text-gray-900 truncate">{{ $sidebarVendor->name ?? __('vendor.vendor') }}</h2>
                <p class="text-xs text-gray-600 truncate flex items-center mt-0.5">
                    <i class="fas fa-phone text-[10px] mr-1.5 text-emerald-600 shrink-0"></i>
                    <span class="truncate">{{ Auth::user()->mobile ?? __('vendor.no_phone') }}</span>
                </p>
            </div>
        </div>
        <button type="button" @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);" class="md:hidden text-gray-500 hover:text-gray-700 shrink-0 p-1 rounded-lg hover:bg-white/60" aria-label="Close menu">
            <i class="fas fa-times text-xl leading-none"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 overflow-y-auto">
        <ul class="space-y-0.5">
            <!-- Home -->
            <li>
                <a href="{{ route('vendor.home') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.home')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.home') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-home" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.home') }}</span>
                </a>
            </li>

            <!-- Items -->
            <li>
                <a href="{{ route('vendor.items.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                   {{ request()->routeIs('vendor.items.*') ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.items.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-box" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.items') }}</span>
                </a>
            </li>

            <!-- Categories -->
            <li>
                <a href="{{ route('vendor.categories.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.categories.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.categories.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.categories') }}</span>
                </a>
            </li>

            <!-- Orders -->
            <li>
                <a href="{{ route('vendor.orders.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.orders.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.orders.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.orders') }}</span>
                </a>
            </li>

            <!-- Cart -->
            <li>
                <a href="{{ route('vendor.carts.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.carts.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.carts.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.cart') }}</span>
                </a>
            </li>

            <!-- Reviews -->
            <li>
                <a href="{{ route('vendor.reviews.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.reviews.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.reviews.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-star" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.reviews') }}</span>
                </a>
            </li>

            <!-- Customers -->
            <li>
                <a href="{{ route('vendor.customers.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.customers.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.customers.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-user-friends" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.customers') }}</span>
                </a>
            </li>

            <!-- Staff -->
            <li>
                <a href="{{ route('vendor.staff.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.staff.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.staff.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-users" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.staff') }}</span>
                </a>
            </li>

            <!-- Coupons -->
            <li>
                <a href="{{ route('vendor.coupons.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.coupons.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.coupons.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.coupons') }}</span>
                </a>
            </li>

            <!-- Calendar -->
            <li>
                <a href="{{ route('vendor.calendar') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.calendar')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.calendar') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.calendar') }}</span>
                </a>
            </li>

            <!-- Subscription -->
            <li>
                <a href="{{ route('vendor.subscription.plans') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->routeIs('vendor.subscription.plans')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[15px] leading-none
                          {{ request()->routeIs('vendor.subscription.plans') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                    </span>
                    <span class="truncate">{{ __('vendor.subscription_plans') }}</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Logout -->
    <div class="p-3 border-t border-gray-200 shrink-0">
        <form action="{{ route('vendor.logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="group w-full flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 transition-colors">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-[15px] leading-none">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                </span>
                <span>{{ __('vendor.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

<!-- Overlay for mobile -->
<div x-show="sidebarOpen"
     @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-20 md:hidden">
</div>
