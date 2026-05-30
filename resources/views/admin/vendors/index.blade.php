@extends('admin.layouts.app')

@section('title', 'All Vendors - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">All Vendors</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rental businesses on the Rentkia marketplace</p>
        </div>
        <a href="{{ route('admin.vendors.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Add vendor
        </a>
    </div>

    @include('admin.users.partials.alerts')

    <form method="GET" action="{{ route('admin.vendors.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search name, city, owner..."
               class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        <select name="status" class="rounded-xl border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all" @selected($status === 'all')>All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            <option value="verified" @selected($status === 'verified')>Verified</option>
            <option value="pending" @selected($status === 'pending')>KYC pending</option>
        </select>
        <button type="submit" class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 dark:bg-gray-700">Filter</button>
        @if($search || $status !== 'all')
            <a href="{{ route('admin.vendors.index') }}" class="text-center text-sm font-semibold text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Vendor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Owner</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Location</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Category</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Orders</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($vendor->logo_url)
                                        <img src="{{ $vendor->logo_url }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">
                                            <i class="fas fa-store"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="font-semibold text-gray-900 hover:text-green-600 dark:text-white dark:hover:text-green-400">
                                            {{ $vendor->name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ $vendor->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <p>{{ $vendor->owner_name ?: ($vendor->user?->name ?? '—') }}</p>
                                @if($vendor->user?->mobile)
                                    <p class="text-xs text-gray-500">{{ $vendor->user->mobile }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $vendor->city ?: '—' }}{{ $vendor->state ? ', '.$vendor->state : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $vendor->businessCategory?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                {{ number_format($vendor->orders_count) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if($vendor->is_active)
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                    @endif
                                    @if($vendor->is_verified)
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Verified</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">KYC pending</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300">View</a>
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="ml-3 font-semibold text-green-600 hover:text-green-700">Edit</a>
                                <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this vendor? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 font-semibold text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $vendors->links() }}
</div>
@endsection
