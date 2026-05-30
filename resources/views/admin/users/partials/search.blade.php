<form method="GET" action="{{ $action }}" class="flex flex-wrap gap-2">
    <div class="relative min-w-[200px] flex-1">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="search"
               name="q"
               value="{{ $search }}"
               placeholder="{{ $placeholder ?? 'Search...' }}"
               class="w-full rounded-xl border-2 border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-gray-900 focus:border-green-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
    </div>
    <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">Search</button>
    @if($search)
        <a href="{{ $action }}" class="rounded-xl border-2 border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Clear</a>
    @endif
</form>
