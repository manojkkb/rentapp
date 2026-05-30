@extends('admin.layouts.app')

@section('title', 'Suspended - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Suspended &amp; Inactive</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Deactivated vendor stores and staff access</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.suspended'), 'placeholder' => 'Search...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Inactive vendor stores</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Store</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Owner</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">City</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($inactiveVendors as $vendor)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $vendor->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $vendor->user->name ?? $vendor->owner_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $vendor->city ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <form action="{{ route('admin.users.vendors.toggle-active', $vendor) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700">
                                    Activate
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No inactive vendor stores.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-gray-100 px-5 py-3 dark:border-gray-700">{{ $inactiveVendors->links('pagination::tailwind', ['pageName' => 'vendors_page']) }}</div>
    </div>

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Inactive staff access</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Store</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Role</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($inactiveStaff as $row)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $row->user_name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $row->vendor_name }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ $row->is_owner ? 'Owner' : ($row->role ?? 'Staff') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form action="{{ route('admin.users.staff.toggle-active', $row->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700">
                                    Restore access
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No inactive staff records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
