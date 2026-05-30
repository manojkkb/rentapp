@extends('admin.layouts.app')

@section('title', 'Business Categories - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Business Categories</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Industry types vendors choose when registering on Rentkia</p>
        </div>
        <a href="{{ route('admin.business-categories.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Add category
        </a>
    </div>

    @include('admin.users.partials.alerts')

    <form method="GET" action="{{ route('admin.business-categories.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search name or slug..."
               class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        <select name="type" class="rounded-xl border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all" @selected($type === 'all')>All types</option>
            <option value="parent" @selected($type === 'parent')>Top-level only</option>
            <option value="child" @selected($type === 'child')>Sub-categories</option>
            <option value="active" @selected($type === 'active')>Active</option>
            <option value="inactive" @selected($type === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 dark:bg-gray-700">Filter</button>
        @if($search || $type !== 'all')
            <a href="{{ route('admin.business-categories.index') }}" class="text-center text-sm font-semibold text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Parent</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Vendors</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Sub-cats</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($category->icon)
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="{{ $category->icon }}"></i>
                                        </span>
                                    @else
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">
                                            <i class="fas fa-briefcase"></i>
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $category->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $category->parent?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                {{ number_format($category->vendors_count) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                {{ $category->children_count }}
                            </td>
                            <td class="px-4 py-3">
                                @if($category->is_active)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.business-categories.edit', $category) }}" class="font-semibold text-green-600 hover:text-green-700">Edit</a>
                                <form action="{{ route('admin.business-categories.destroy', $category) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 font-semibold text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No categories found. Run the seeder or add one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $categories->links() }}
</div>
@endsection
