@extends('admin.layouts.app')

@section('title', 'Vendor Subscriptions - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Vendor Subscriptions</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Active and past subscription records per vendor</p>
        </div>
        <a href="{{ route('admin.subscriptions.plans.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
            <i class="fas fa-layer-group mr-2"></i>Manage plans
        </a>
    </div>

    @include('admin.users.partials.alerts')

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase text-gray-500">Total</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($counts['total']) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <p class="text-xs font-semibold uppercase text-green-700 dark:text-green-400">Active</p>
            <p class="mt-1 text-2xl font-black text-green-800 dark:text-green-300">{{ number_format($counts['active']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase text-gray-500">Expired</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($counts['expired']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase text-gray-500">Cancelled</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($counts['cancelled']) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Vendor, user, or plan..."
               class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        <select name="status" class="rounded-xl border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all" @selected($status === 'all')>All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="expired" @selected($status === 'expired')>Expired</option>
            <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            <option value="trial" @selected($status === 'trial')>Trial</option>
        </select>
        <button type="submit" class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 dark:bg-gray-700">Filter</button>
        @if($search || $status !== 'all')
            <a href="{{ route('admin.subscriptions.index') }}" class="text-center text-sm font-semibold text-gray-500">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Vendor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Plan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Period</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($subscriptions as $sub)
                        @php
                            $statusClass = match($sub->status) {
                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                'expired' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                'trial' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $sub->vendor?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $sub->user?->name }} · {{ $sub->user?->mobile }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $sub->subscriptionPlan?->name ?? '—' }}
                                <span class="block text-xs capitalize text-gray-500">{{ $sub->subscriptionPlan?->type }} {{ $sub->subscriptionPlan?->billing_cycle }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $sub->start_date?->format('d M Y') ?? '—' }}
                                <span class="text-gray-400">→</span>
                                {{ $sub->expiry_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                ₹{{ number_format((float) $sub->amount, 0) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $statusClass }}">{{ $sub->status }}</span>
                                @if($sub->auto_renew)
                                    <span class="ml-1 text-xs text-gray-400">auto-renew</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($sub->vendor)
                                    <a href="{{ route('admin.vendors.show', $sub->vendor) }}" class="font-semibold text-green-600 hover:text-green-700">Vendor</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No subscriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $subscriptions->links() }}
</div>
@endsection
