@extends('admin.layouts.app')

@section('title', 'All Users - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">User Management</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Platform accounts (vendor owners &amp; staff)</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.index'), 'placeholder' => 'Name, email, or mobile...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">User</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Contact</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Language</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Vendors</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $user->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            @if($user->mobile)<div>{{ $user->mobile }}</div>@endif
                            @if($user->email)<div class="text-xs">{{ $user->email }}</div>@endif
                        </td>
                        <td class="px-5 py-3 uppercase text-gray-600">{{ $user->language ?? 'en' }}</td>
                        <td class="px-5 py-3 text-right font-bold text-green-600">{{ $user->vendors_count }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
