@php
    $tabs = [
        ['route' => 'admin.dashboard', 'label' => 'Overview', 'icon' => 'fa-chart-pie'],
        ['route' => 'admin.dashboard.revenue', 'label' => 'Revenue Analytics', 'icon' => 'fa-indian-rupee-sign'],
        ['route' => 'admin.dashboard.bookings', 'label' => 'Booking Analytics', 'icon' => 'fa-calendar-check'],
        ['route' => 'admin.dashboard.vendors', 'label' => 'Vendor Performance', 'icon' => 'fa-store'],
        ['route' => 'admin.dashboard.cities', 'label' => 'City Analytics', 'icon' => 'fa-map-marker-alt'],
    ];
@endphp

<nav class="flex flex-wrap gap-2 border-b border-gray-200 pb-4 dark:border-gray-700">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition {{ request()->routeIs($tab['route']) ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-green-50 hover:text-green-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-green-900/30 dark:hover:text-green-400' }}">
            <i class="fas {{ $tab['icon'] }} text-xs"></i>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
