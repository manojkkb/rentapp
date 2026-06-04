@extends('admin.layouts.app')

@section('title', $vendor->name . ' - Vendor')

@section('content')
@php
    $languages = ['en' => 'English', 'hi' => 'Hindi', 'bn' => 'Bengali', 'mr' => 'Marathi', 'te' => 'Telugu', 'ta' => 'Tamil', 'gu' => 'Gujarati', 'ur' => 'Urdu', 'kn' => 'Kannada', 'or' => 'Odia', 'ml' => 'Malayalam', 'pa' => 'Punjabi'];
    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'confirmed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    ];
@endphp
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-4">
            @if($vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-gray-100 dark:ring-gray-700">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-2xl text-green-700 dark:bg-green-900/40 dark:text-green-400">
                    <i class="fas fa-store"></i>
                </div>
            @endif
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.vendors.index') }}" class="text-green-600 hover:text-green-700">← All vendors</a>
                </p>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">{{ $vendor->name }}</h1>
                <p class="mt-0.5 text-sm text-gray-500">{{ $vendor->slug }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if($vendor->is_active)
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">Active</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                    @endif
                    @if($vendor->is_verified)
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">KYC verified</span>
                    @else
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">KYC pending</span>
                    @endif
                    @if($vendor->businessCategory)
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $vendor->businessCategory->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(!$vendor->is_verified)
                <form action="{{ route('admin.users.kyc.approve', $vendor) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Approve KYC</button>
                </form>
            @endif
            <form action="{{ route('admin.users.vendors.toggle-active', $vendor) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                    {{ $vendor->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('admin.vendors.edit', $vendor) }}"
               class="rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700">Edit vendor</a>
        </div>
    </div>

    @include('admin.users.partials.alerts')

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total orders</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($stats['orders_total']) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($stats['orders_completed']) }} completed</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">GMV (completed)</p>
            <p class="mt-1 text-2xl font-black text-green-600">₹{{ number_format($stats['gmv'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catalog</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($stats['items']) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($stats['categories']) }} categories</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">People</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($stats['customers']) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($stats['staff']) }} staff · {{ number_format($stats['reviews']) }} reviews</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Business details --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Business details</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Owner</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ $vendor->owner_name ?: ($vendor->user?->name ?? '—') }}</dd>
                    </div>
                    @if($vendor->user)
                        <div>
                            <dt class="text-gray-500">Owner contact</dt>
                            <dd class="text-gray-900 dark:text-white">
                                @if($vendor->user->mobile)<div>{{ $vendor->user->mobile }}</div>@endif
                                @if($vendor->user->email)<div class="text-gray-500">{{ $vendor->user->email }}</div>@endif
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Address</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $vendor->full_address ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">GST</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $vendor->gst_number ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Language</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $languages[$vendor->language] ?? strtoupper($vendor->language) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Rating</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">
                            {{ number_format((float) $vendor->rating, 1) }}
                            <span class="text-xs font-normal text-gray-500">({{ $vendor->total_reviews }} reviews)</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Joined</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $vendor->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Subscription</h2>
                <div class="mt-4 text-sm">
                    @if($subscriptionStatus === 'active' && $activeSubscription)
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $activeSubscription->subscriptionPlan?->name ?? 'Plan' }}</p>
                        <p class="mt-1 capitalize text-gray-600 dark:text-gray-400">
                            {{ $activeSubscription->subscriptionPlan?->type }} · {{ $activeSubscription->subscriptionPlan?->billing_cycle }}
                        </p>
                        <p class="mt-2 text-gray-500">
                            Expires {{ $activeSubscription->expiry_date?->format('d M Y') ?? '—' }}
                        </p>
                        <p class="mt-1 text-xs font-semibold text-green-600">
                            Active · ₹{{ number_format((float) $activeSubscription->amount, 0) }}
                            @if($activeSubscription->payment_gateway === 'manual')
                                <span class="text-gray-500 font-normal">· Manual</span>
                            @endif
                        </p>
                    @elseif($subscriptionStatus === 'trial')
                        <p class="font-semibold text-amber-700 dark:text-amber-400">Free trial</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Ends {{ $trialEndsAt->format('d M Y') }}</p>
                    @else
                        <p class="text-gray-500">No active subscription</p>
                        @if($trialEndsAt->isPast())
                            <p class="mt-1 text-xs text-gray-400">Trial ended {{ $trialEndsAt->format('d M Y') }}</p>
                        @endif
                    @endif
                </div>

                <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-700">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Manual upgrade</h3>
                    @if($subscriptionPlans->isEmpty())
                        <p class="mt-3 text-sm text-gray-500">No active plans available. Add plans in Subscriptions first.</p>
                    @else
                        <form action="{{ route('admin.vendors.subscription.upgrade', $vendor) }}" method="POST" class="mt-3 space-y-3">
                            @csrf
                            <div>
                                <label for="subscription_plan_id" class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-400">Plan</label>
                                <select
                                    id="subscription_plan_id"
                                    name="subscription_plan_id"
                                    required
                                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select a plan</option>
                                    @foreach($subscriptionPlans as $plan)
                                        <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>
                                            {{ $plan->name }}
                                            · ₹{{ number_format((float) $plan->price, 0) }}
                                            · {{ $plan->duration_days }} days
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-xs leading-relaxed text-gray-500">
                                Starts today and replaces any current active plan. Payment is recorded as manual (admin).
                            </p>
                            <button type="submit"
                                    class="w-full rounded-xl bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700">
                                Apply plan
                            </button>
                        </form>
                    @endif
                </div>

                @if($subscriptionHistory->isNotEmpty())
                    <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Recent history</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach($subscriptionHistory as $sub)
                                <li class="rounded-lg bg-gray-50 px-3 py-2 text-xs dark:bg-gray-900/40">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $sub->subscriptionPlan?->name ?? 'Plan #'.$sub->id }}
                                        </span>
                                        <span class="shrink-0 capitalize text-gray-500">{{ $sub->status }}</span>
                                    </div>
                                    <p class="mt-0.5 text-gray-500">
                                        {{ $sub->start_date?->format('d M Y') ?? '—' }}
                                        → {{ $sub->expiry_date?->format('d M Y') ?? '—' }}
                                        @if($sub->payment_gateway === 'manual')
                                            · manual
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Staff + recent orders --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white">Staff &amp; access</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">User</th>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Role</th>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($vendor->users as $member)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $member->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->mobile ?? $member->email }}</p>
                                    </td>
                                    <td class="px-5 py-3 capitalize text-gray-700 dark:text-gray-300">
                                        {{ $member->pivot->role ?? 'staff' }}
                                        @if($member->pivot->is_owner)
                                            <span class="ml-1 text-xs text-green-600">(owner)</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($member->pivot->is_active)
                                            <span class="text-xs font-semibold text-green-600">Active</span>
                                        @else
                                            <span class="text-xs font-semibold text-gray-500">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-gray-500">No staff linked.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white">Recent orders</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Order</th>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Customer</th>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Event date</th>
                                <th class="px-5 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Status</th>
                                <th class="px-5 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                        {{ $order->order_number ?? '#'.$order->id }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $order->customer?->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $order->start_at?->format('d M Y') ?? $order->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $statusColors[$order->status] ?? $statusColors['pending'] }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        ₹{{ number_format((float) $order->grand_total, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-500">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
