@extends('admin.layouts.app')

@section('title', 'Vendor Staff - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Vendor Staff &amp; Owners</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Users linked to one or more vendor stores</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.vendor-accounts'), 'placeholder' => 'Name, email, or mobile...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Mobile</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Vendor stores</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Current store</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $user->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $user->mobile ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->vendors as $vendor)
                                    <span class="rounded-lg bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        {{ $vendor->name }}
                                        @if($vendor->pivot->is_owner) <span class="text-amber-600">(owner)</span> @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ $user->vendors->firstWhere('id', $user->vendor_id)?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No vendor accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
