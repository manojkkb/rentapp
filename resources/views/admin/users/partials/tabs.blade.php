@php
    $c = $counts ?? [];
    $tabs = [
        ['route' => 'admin.users.index', 'label' => 'All Users', 'count' => $c['all_users'] ?? null],
        ['route' => 'admin.users.customers', 'label' => 'Customers', 'count' => $c['customers'] ?? null],
        ['route' => 'admin.users.vendor-accounts', 'label' => 'Vendor Staff', 'count' => $c['vendor_accounts'] ?? null],
        ['route' => 'admin.users.admins', 'label' => 'Admins', 'count' => $c['admins'] ?? null],
        ['route' => 'admin.users.kyc', 'label' => 'KYC Verification', 'count' => $c['kyc_pending'] ?? null, 'badge' => true],
        ['route' => 'admin.users.suspended', 'label' => 'Suspended', 'count' => $c['suspended'] ?? null],
        ['route' => 'admin.users.login-activity', 'label' => 'Login Activity', 'count' => null],
    ];
@endphp

<nav class="flex flex-wrap gap-2 border-b border-gray-200 pb-4 dark:border-gray-700">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($tab['route']) ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-green-50 hover:text-green-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-green-900/30 dark:hover:text-green-400' }}">
            {{ $tab['label'] }}
            @if(isset($tab['count']) && $tab['count'] > 0)
                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ request()->routeIs($tab['route']) ? 'bg-white/20 text-white' : (($tab['badge'] ?? false) ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                    {{ $tab['count'] }}
                </span>
            @endif
        </a>
    @endforeach
</nav>
