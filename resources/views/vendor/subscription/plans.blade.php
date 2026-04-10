@extends('vendor.layouts.app')

@section('title', __('vendor.subscription_plans_page_title'))
@section('page-title', __('vendor.subscription_plans'))

@section('content')
@php
    $hasMonthly = $plansByType->flatten(1)->contains(fn ($p) => $p->billing_cycle === 'monthly');
    $hasYearly = $plansByType->flatten(1)->contains(fn ($p) => $p->billing_cycle === 'yearly');
@endphp

<div class="max-w-6xl mx-auto pb-12" x-data="{ billing: '{{ $hasMonthly ? 'monthly' : 'yearly' }}' }">
    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
            {{ __('vendor.subscription_plans') }}
        </h1>
        <p class="mt-2 text-sm sm:text-base text-gray-600 max-w-xl mx-auto">
            {{ __('vendor.billing_intro') }}
        </p>

        {{-- Monthly / Yearly toggle --}}
        @if($hasMonthly && $hasYearly)
            <div class="mt-8 inline-flex rounded-xl bg-gray-100/80 p-1 ring-1 ring-gray-200/80 shadow-sm">
                <button
                    type="button"
                    @click="billing = 'monthly'"
                    :class="billing === 'monthly'
                        ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200'
                        : 'text-gray-600 hover:text-gray-900'"
                    class="relative rounded-lg px-5 py-2.5 text-sm font-semibold transition-all min-w-[7rem]"
                >
                    {{ __('vendor.billing_monthly') }}
                </button>
                <button
                    type="button"
                    @click="billing = 'yearly'"
                    :class="billing === 'yearly'
                        ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200'
                        : 'text-gray-600 hover:text-gray-900'"
                    class="relative rounded-lg px-5 py-2.5 text-sm font-semibold transition-all min-w-[7rem]"
                >
                    {{ __('vendor.billing_yearly') }}
                    @php
                        $sampleMonthly = $plansByType->flatten(1)->firstWhere('billing_cycle', 'monthly');
                        $sampleYearly = $plansByType->flatten(1)->firstWhere('billing_cycle', 'yearly');
                        $maxSave = 0;
                        if ($sampleMonthly && $sampleYearly) {
                            foreach ($plansByType as $typePlans) {
                                $m = $typePlans->firstWhere('billing_cycle', 'monthly');
                                $y = $typePlans->firstWhere('billing_cycle', 'yearly');
                                if ($m && $y && (float) $m->price > 0) {
                                    $annual = (float) $m->price * 12;
                                    $save = round((1 - (float) $y->price / $annual) * 100);
                                    $maxSave = max($maxSave, $save);
                                }
                            }
                        }
                    @endphp
                    @if($maxSave > 0)
                        <span class="ml-1.5 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                            {{ __('vendor.billing_save_up_to_pct', ['pct' => $maxSave]) }}
                        </span>
                    @endif
                </button>
            </div>
        @endif
    </div>

    {{-- Plan grid --}}
    @if($plansByType->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-gray-600">{{ __('vendor.billing_no_plans_yet') }}</p>
        </div>
    @else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($plansByType as $type => $typePlans)
            @php
                $monthly = $typePlans->firstWhere('billing_cycle', 'monthly');
                $yearly = $typePlans->firstWhere('billing_cycle', 'yearly');
                $tierLabel = match ($type) {
                    'silver' => __('vendor.tier_silver'),
                    'gold' => __('vendor.tier_gold'),
                    'diamond' => __('vendor.tier_diamond'),
                    default => ucfirst((string) $type),
                };
                $isPopular = ($monthly && $monthly->is_popular) || ($yearly && $yearly->is_popular);
                $savingsPct = null;
                if ($monthly && $yearly && (float) $monthly->price > 0) {
                    $annualFromMonthly = (float) $monthly->price * 12;
                    $savingsPct = max(0, round((1 - (float) $yearly->price / $annualFromMonthly) * 100));
                }
            @endphp

            <div
                class="relative flex flex-col rounded-2xl border transition-shadow duration-200
                    {{ $isPopular
                        ? 'border-emerald-400/80 bg-gradient-to-b from-emerald-50/90 to-white shadow-lg shadow-emerald-900/5 ring-1 ring-emerald-200/60'
                        : 'border-gray-200 bg-white shadow-sm hover:shadow-md' }}"
            >
                @if($isPopular)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="inline-flex rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow-sm">
                            {{ __('vendor.billing_most_popular') }}
                        </span>
                    </div>
                @endif

                <div class="flex flex-1 flex-col p-6 sm:p-7 pt-8">
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $tierLabel }}</div>
                    <h3 class="text-lg font-bold text-gray-900">
                        @if($monthly && $yearly)
                            <span x-show="billing === 'monthly'" x-cloak>{{ $monthly->name }}</span>
                            <span x-show="billing === 'yearly'" x-cloak>{{ $yearly->name }}</span>
                        @elseif($monthly)
                            {{ $monthly->name }}
                        @elseif($yearly)
                            {{ $yearly->name }}
                        @else
                            {{ __('vendor.billing_plan_fallback', ['name' => $tierLabel]) }}
                        @endif
                    </h3>

                    {{-- Price: monthly --}}
                    @if($monthly)
                        <div x-show="billing === 'monthly'" x-cloak class="mt-4">
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-gray-900">₹{{ number_format((float) $monthly->price, 0, '.', ',') }}</span>
                                <span class="text-sm font-medium text-gray-500">{{ __('vendor.billing_per_month') }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.billing_billed_every_days', ['days' => $monthly->duration_days]) }}</p>
                        </div>
                    @endif

                    {{-- Price: yearly --}}
                    @if($yearly)
                        <div x-show="billing === 'yearly'" x-cloak class="mt-4">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="text-3xl font-bold text-gray-900">₹{{ number_format((float) $yearly->price, 0, '.', ',') }}</span>
                                <span class="text-sm font-medium text-gray-500">{{ __('vendor.billing_per_year') }}</span>
                                @if($savingsPct !== null && $savingsPct > 0)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                                        {{ __('vendor.billing_save_pct', ['pct' => $savingsPct]) }}
                                    </span>
                                @endif
                            </div>
                            @if($monthly)
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('vendor.billing_equiv_per_month_annually', ['amount' => number_format((float) $yearly->price / max(1, (int) round($yearly->duration_days / 30)), 0, '.', ',')]) }}
                                </p>
                            @else
                                <p class="mt-1 text-xs text-gray-500">{{ __('vendor.billing_billed_every_days', ['days' => $yearly->duration_days]) }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Missing cycle for this tier --}}
                    @if($hasMonthly && $hasYearly)
                        @if(!$monthly)
                            <div x-show="billing === 'monthly'" x-cloak class="mt-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/80 px-3 py-4 text-center">
                                <p class="text-sm text-gray-600">{{ __('vendor.billing_no_monthly_tier') }}</p>
                            </div>
                        @endif
                        @if(!$yearly)
                            <div x-show="billing === 'yearly'" x-cloak class="mt-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/80 px-3 py-4 text-center">
                                <p class="text-sm text-gray-600">{{ __('vendor.billing_no_yearly_tier') }}</p>
                            </div>
                        @endif
                    @endif

                    @php
                        $featurePlan = $monthly ?? $yearly;
                        $features = $featurePlan && is_array($featurePlan->features) ? $featurePlan->features : [];
                    @endphp

                    <ul class="mt-6 flex-1 space-y-3 text-sm text-gray-700">
                        @forelse($features as $key => $value)
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                                </span>
                                <span>
                                    <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    @if(is_bool($value))
                                        <span class="text-gray-600"> — {{ $value ? __('vendor.billing_feature_yes') : __('vendor.billing_feature_no') }}</span>
                                    @else
                                        <span class="text-gray-600"> — {{ $value }}</span>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-500">{{ __('vendor.billing_no_features') }}</li>
                        @endforelse
                    </ul>

                    {{-- CTA: one block per available cycle so data-id stays correct --}}
                    <div class="mt-8 space-y-2">
                        @if($monthly)
                            <button
                                type="button"
                                x-show="billing === 'monthly'"
                                x-cloak
                                class="buy-btn inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                data-id="{{ $monthly->id }}"
                                data-price="{{ $monthly->price }}"
                            >
                                {{ __('vendor.billing_subscribe_monthly') }}
                            </button>
                        @endif
                        @if($yearly)
                            <button
                                type="button"
                                x-show="billing === 'yearly'"
                                x-cloak
                                class="buy-btn inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                data-id="{{ $yearly->id }}"
                                data-price="{{ $yearly->price }}"
                            >
                                {{ __('vendor.billing_subscribe_yearly') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@section('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.querySelectorAll('.buy-btn').forEach(button => {

    button.addEventListener('click', async function () {

        let planId = this.dataset.id;

        let response = await fetch('create-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                plan_id: planId
            })
        });

        let data = await response.json();

        var options = {
            "key": "{{ env('RAZORPAY_KEY') }}",
            "amount": data.amount,
            "currency": "INR",
            "name": "RentApp",
            "description": data.plan_name,
            "order_id": data.order_id,

            "handler": function (response) {

                fetch('verify-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...response,
                        plan_id: planId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(@json(__('vendor.payment_success')));
                });
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    });

});
</script>
@endsection
