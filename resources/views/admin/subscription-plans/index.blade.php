@extends('admin.layouts.app')

@section('title', 'Subscription Plans - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.subscriptions.index') }}" class="text-green-600 hover:text-green-700">← Subscriptions</a>
            </p>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white">Subscription Plans</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage paid plans vendors can purchase after their free trial.</p>
        </div>
        <a href="{{ route('admin.subscriptions.plans.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Add plan
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tier</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Billing</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Price</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Days</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($plans as $plan)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $plan->name }}
                            @if($plan->is_popular)
                                <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Popular</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-700 dark:text-gray-300">{{ $plan->type }}</td>
                        <td class="px-4 py-3 capitalize text-gray-700 dark:text-gray-300">{{ $plan->billing_cycle }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            ₹{{ number_format((float) $plan->price, 0) }}
                            @if($plan->discount_price)
                                <span class="text-xs text-gray-500">(sale ₹{{ number_format((float) $plan->discount_price, 0) }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $plan->duration_days }}</td>
                        <td class="px-4 py-3">
                            @if($plan->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="font-semibold text-green-600 hover:text-green-700">Edit</a>
                            <form action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 font-semibold text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">No plans yet. Run the seeder or create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Vendors get a {{ \App\Support\PlatformSettings::trialDays() }}-day free trial from their business creation date. Paid plans apply per vendor after the trial ends.
    </p>
</div>
@endsection
