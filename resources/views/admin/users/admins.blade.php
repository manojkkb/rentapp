@extends('admin.layouts.app')

@section('title', 'Admins - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Admin Users</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rentkia platform administrators</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.admins'), 'placeholder' => 'Name, email, or phone...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Email</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Phone</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Role</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($admins as $admin)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $admin->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $admin->email }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $admin->phone ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($admin->is_super_admin)
                                <span class="rounded-lg bg-purple-100 px-2 py-1 text-xs font-bold text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">Super Admin</span>
                            @else
                                <span class="text-gray-600">Admin</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($admin->is_active)
                                <span class="text-xs font-bold text-green-600">Active</span>
                            @else
                                <span class="text-xs font-bold text-red-600">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No admins found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $admins->links() }}
</div>
@endsection
