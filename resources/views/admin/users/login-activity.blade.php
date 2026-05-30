@extends('admin.layouts.app')

@section('title', 'Login Activity - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Login Activity</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Active vendor user sessions on the platform</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.login-activity'), 'placeholder' => 'Name, email, or mobile...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Contact</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">IP address</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Last activity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($sessions as $session)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $session->name ?? 'Unknown' }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            @if($session->mobile){{ $session->mobile }}@endif
                            @if($session->email)<div class="text-xs">{{ $session->email }}</div>@endif
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $session->ip_address ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('d M Y H:i') }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">No active sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sessions->links() }}
</div>
@endsection
