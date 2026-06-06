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
    class="fixed inset-y-0 left-0 z-30 flex max-h-[100dvh] min-h-0 w-56 transform flex-col border-r border-gray-200 bg-white transition-transform duration-300 max-md:max-h-screen md:static md:max-h-none md:w-52 md:translate-x-0 lg:w-56"
>
    <div class="flex h-11 shrink-0 items-center justify-between gap-1.5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50 px-2 sm:h-12 md:px-2.5">
        <div class="flex min-w-0 flex-1 items-center gap-2">
            @if($sidebarVendor && $sidebarVendor->logo_url)
                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md border border-emerald-400/80 bg-white p-0.5 shadow-sm">
                    <img src="{{ $sidebarVendor->logo_url }}"
                         alt="{{ $sidebarVendor->name }}"
                         width="32"
                         height="32"
                         class="max-h-full max-w-full object-contain object-center"
                         loading="lazy"
                         decoding="async">
                </div>
            @else
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-sm">
                    <span class="text-sm font-bold leading-none text-white">
                        {{ strtoupper(substr($sidebarVendor->name ?? 'V', 0, 1)) }}
                    </span>
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-xs font-bold leading-snug text-gray-900 sm:text-sm">{{ $sidebarVendor->name ?? __('vendor.vendor') }}</h2>
            </div>
        </div>
        <button type="button" @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-500 hover:bg-white/60 hover:text-gray-700 md:hidden" aria-label="Close menu">
            <i class="fas fa-times text-base leading-none"></i>
        </button>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain px-1.5 py-1.5 [-webkit-overflow-scrolling:touch] md:px-2 md:py-1.5">
        <ul class="space-y-px">
            @vendorCan('dashboard.view')
            <li>
                <a wire:navigate href="{{ route('vendor.home') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.home')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.home') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-home" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.home') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('orders.view')
            <li>
                <a wire:navigate href="{{ route('vendor.orders.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.orders.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.orders.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.orders') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('deliveries.view')
            <li>
                <a wire:navigate href="{{ route('vendor.deliveries.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.deliveries.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.deliveries.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-truck" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.deliveries') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('returns.view')
            <li>
                <a wire:navigate href="{{ route('vendor.returns.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.returns.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.returns.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.returns') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('items.view')
            <li>
                <a wire:navigate href="{{ route('vendor.items.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                   {{ request()->routeIs('vendor.items.*') ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.items.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-box" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.items') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('categories.manage')
            <li>
                <a wire:navigate href="{{ route('vendor.categories.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.categories.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.categories.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.categories') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('reviews.view')
            <li>
                <a wire:navigate href="{{ route('vendor.reviews.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.reviews.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.reviews.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-star" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.reviews') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('customers.view')
            <li>
                <a wire:navigate href="{{ route('vendor.customers.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.customers.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.customers.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-user-friends" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.customers') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('staff.view')
            <li>
                <a wire:navigate href="{{ route('vendor.staff.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.staff.*') && !request()->routeIs('vendor.staff-permissions.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.staff.*') && !request()->routeIs('vendor.staff-permissions.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-users" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.staff') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('roles.manage')
            <li>
                <a wire:navigate href="{{ route('vendor.staff-permissions.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.staff-permissions.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.staff-permissions.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.staff_permissions') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('coupons.manage')
            <li>
                <a wire:navigate href="{{ route('vendor.coupons.index') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.coupons.*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.coupons.*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.coupons') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('calendar.view')
            <li>
                <a wire:navigate href="{{ route('vendor.calendar') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.calendar')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.calendar') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.calendar') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('settings.view')
            <li>
                <a wire:navigate href="{{ route('vendor.subscription.plans') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.subscription.plans')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.subscription.plans') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.subscription_plans') }}</span>
                </a>
            </li>
            @endvendorCan

            @vendorCan('support.view')
            <li>
                <a wire:navigate href="{{ route('vendor.support') }}"
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors
                          {{ request()->routeIs('vendor.support*')
                              ? 'bg-emerald-500 text-white shadow-sm'
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs leading-none
                          {{ request()->routeIs('vendor.support*') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                        <i class="fas fa-headset" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate">{{ __('vendor.help_support') }}</span>
                </a>
            </li>
            @endvendorCan
        </ul>
    </nav>

    <div class="shrink-0 border-t border-gray-200 px-1.5 py-1.5 md:px-2 md:py-1.5">
        <form action="{{ route('vendor.logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-md bg-red-500 px-2 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-red-600">
                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white/15 text-xs leading-none">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                </span>
                <span>{{ __('vendor.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

<div x-cloak
     x-show="sidebarOpen"
     @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-20 bg-black/50 md:hidden">
</div>
