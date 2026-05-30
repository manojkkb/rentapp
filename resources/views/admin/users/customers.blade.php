@extends('admin.layouts.app')

@section('title', 'Customers - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Rental Customers</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customers across all vendor stores</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.customers'), 'placeholder' => 'Name or mobile...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Customer</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Mobile</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Vendor store</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Registered user</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($customers as $customer)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $customer->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $customer->mobile ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $customer->vendor->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $customer->user->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $customer->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $customers->links() }}
</div>
@endsection
