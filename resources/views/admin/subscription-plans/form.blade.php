@extends('admin.layouts.app')

@section('title', ($plan->exists ? 'Edit' : 'Create') . ' Subscription Plan - Admin')

@section('content')
@php $isEdit = $plan->exists; @endphp
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.subscriptions.plans.index') }}" class="text-green-600 hover:text-green-700">← Subscription plans</a>
        </p>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white">{{ $isEdit ? 'Edit' : 'Create' }} subscription plan</h1>
    </div>

    <form method="POST"
          action="{{ $isEdit ? route('admin.subscriptions.plans.update', $plan) : route('admin.subscriptions.plans.store') }}"
          class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Plan name</label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Tier</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @foreach(['silver', 'gold', 'diamond'] as $tier)
                        <option value="{{ $tier }}" @selected(old('type', $plan->type) === $tier)>{{ ucfirst($tier) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Billing cycle</label>
                <select name="billing_cycle" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle) === 'monthly')>Monthly</option>
                    <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle) === 'yearly')>Yearly</option>
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Price (₹)</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Sale price (optional)</label>
                <input type="number" step="0.01" min="0" name="discount_price" value="{{ old('discount_price', $plan->discount_price) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Duration (days)</label>
                <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        @php $features = is_array($plan->features) ? $plan->features : []; @endphp
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Max listings</label>
                <input type="number" min="0" name="max_listings" value="{{ old('max_listings', $features['max_listings'] ?? '') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Max staff users</label>
                <input type="number" min="0" name="max_users" value="{{ old('max_users', $features['max_users'] ?? '') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="priority_support" value="1" @checked(old('priority_support', $features['priority_support'] ?? false)) class="rounded">
                Priority support
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="advanced_reports" value="1" @checked(old('advanced_reports', $features['advanced_reports'] ?? false)) class="rounded">
                Advanced reports
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true)) class="rounded">
                Active
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $plan->is_popular ?? false)) class="rounded">
                Mark as popular
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                {{ $isEdit ? 'Save changes' : 'Create plan' }}
            </button>
            <a href="{{ route('admin.subscriptions.plans.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
