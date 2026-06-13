@php
    $isEdit = isset($coupon) && $coupon instanceof \App\Models\Coupon;
    $startDateValue = old('start_date', $isEdit && $coupon->start_date ? $coupon->start_date->format('Y-m-d') : '');
    $endDateValue = old('end_date', $isEdit && $coupon->end_date ? $coupon->end_date->format('Y-m-d') : '');
@endphp

<form action="{{ $isEdit ? route('vendor.coupons.update', $coupon) : route('vendor.coupons.store') }}"
      method="POST"
      class="space-y-5"
      x-data="{
          code: @js(old('code', $isEdit ? $coupon->code : '')),
          name: @js(old('name', $isEdit ? ($coupon->name ?? '') : '')),
          type: @js(old('type', $isEdit ? $coupon->type : 'percent')),
          value: @js(old('value', $isEdit ? (string) $coupon->value : '')),
          minOrder: @js(old('min_order_amount', $isEdit && $coupon->min_order_amount > 0 ? (string) $coupon->min_order_amount : '')),
          maxDiscount: @js(old('max_discount_amount', $isEdit && $coupon->max_discount_amount ? (string) $coupon->max_discount_amount : '')),
          usageLimit: @js(old('usage_limit', $isEdit && $coupon->usage_limit ? (string) $coupon->usage_limit : '')),
          startDate: @js($startDateValue),
          endDate: @js($endDateValue),
      }">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="start_date" id="coupon_start_date" :value="startDate">
    <input type="hidden" name="end_date" id="coupon_end_date" :value="endDate">

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="space-y-5 lg:col-span-3">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.coupon_form_basics') }}</h2>
                <div class="space-y-3">
                    <div>
                        <label for="coupon_code" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.coupon_code') }} *</label>
                        <input type="text"
                               name="code"
                               id="coupon_code"
                               x-model="code"
                               placeholder="{{ __('vendor.coupon_code_placeholder') }}"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 font-mono text-sm uppercase tracking-wide focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('code') border-red-500 @enderror"
                               required>
                        @error('code') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="coupon_name" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.name') }}</label>
                        <input type="text"
                               name="name"
                               id="coupon_name"
                               x-model="name"
                               placeholder="{{ __('vendor.coupon_name_placeholder') }}"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.coupon_form_discount') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="coupon_type" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.type') }} *</label>
                        <select name="type"
                                id="coupon_type"
                                x-model="type"
                                class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                required>
                            <option value="percent">{{ __('vendor.percentage') }}</option>
                            <option value="fixed">{{ __('vendor.fixed_amount') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="coupon_value" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.value') }} *</label>
                        <input type="number"
                               name="value"
                               id="coupon_value"
                               step="0.01"
                               x-model="value"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('value') border-red-500 @enderror"
                               required>
                        @error('value') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.coupon_form_rules') }}</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="min_order_amount" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.min_order') }}</label>
                        <input type="number"
                               name="min_order_amount"
                               id="min_order_amount"
                               step="0.01"
                               x-model="minOrder"
                               placeholder="{{ __('vendor.no_limit') }}"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label for="max_discount_amount" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.max_discount') }}</label>
                        <input type="number"
                               name="max_discount_amount"
                               id="max_discount_amount"
                               step="0.01"
                               x-model="maxDiscount"
                               placeholder="{{ __('vendor.no_limit') }}"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="usage_limit" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.usage_limit') }}</label>
                        <input type="number"
                               name="usage_limit"
                               id="usage_limit"
                               x-model="usageLimit"
                               placeholder="{{ __('vendor.unlimited') }}"
                               class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.coupon_form_validity') }}</h2>
                <div x-data="couponValidityDates({
                         startDate: @js($startDateValue),
                         endDate: @js($endDateValue),
                     })"
                     x-init="init()"
                     class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <label class="block text-xs font-bold uppercase tracking-wide text-emerald-800">{{ __('vendor.select_start_date') }}</label>
                        <div class="coupon-date-input-wrapper">
                            <input type="text"
                                   x-ref="couponStartDate"
                                   readonly
                                   placeholder="{{ __('vendor.select_date') }}"
                                   class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500">
                            <span class="date-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                        </div>
                    </div>
                    <div class="space-y-1 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <label class="block text-xs font-bold uppercase tracking-wide text-emerald-800">{{ __('vendor.select_end_date') }}</label>
                        <div class="coupon-date-input-wrapper">
                            <input type="text"
                                   x-ref="couponEndDate"
                                   readonly
                                   placeholder="{{ __('vendor.select_date') }}"
                                   class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500">
                            <span class="date-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                        </div>
                        @error('end_date') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('vendor.active') }}</p>
                    <p class="text-xs text-gray-500">{{ __('vendor.coupon_form_active_hint') }}</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="peer sr-only"
                           @checked(old('is_active', $isEdit ? $coupon->is_active : true))>
                    <span class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full bg-gray-300 transition-colors peer-checked:bg-emerald-500 peer-checked:[&>span]:translate-x-6">
                        <span class="inline-block h-5 w-5 translate-x-1 transform rounded-full bg-white shadow transition-transform"></span>
                    </span>
                </label>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="sticky top-4 space-y-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.coupon_form_preview') }}</h2>
                <div class="overflow-hidden rounded-2xl border-2 border-dashed border-violet-300 bg-gradient-to-br from-violet-600 to-indigo-700 p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between gap-2 border-b border-dashed border-white/30 pb-3">
                        <i class="fas fa-ticket-alt text-lg text-white/80" aria-hidden="true"></i>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-white/70">{{ __('vendor.coupons') }}</span>
                    </div>
                    <p class="mt-4 font-mono text-2xl font-bold tracking-wider" x-text="code ? code.toUpperCase() : 'CODE'"></p>
                    <p class="mt-1 text-sm text-white/85" x-show="name" x-text="name"></p>
                    <p class="mt-3 text-lg font-bold">
                        <template x-if="type === 'percent' && value">
                            <span x-text="parseFloat(value) + '% {{ __('vendor.off') }}'"></span>
                        </template>
                        <template x-if="type !== 'percent' && value">
                            <span x-text="'₹' + parseFloat(value).toFixed(2) + ' {{ __('vendor.off') }}'"></span>
                        </template>
                        <template x-if="!value">
                            <span>— {{ __('vendor.off') }}</span>
                        </template>
                    </p>
                    <ul class="mt-4 space-y-1 text-xs text-white/75">
                        <li x-show="minOrder">{{ __('vendor.min') }} ₹<span x-text="minOrder"></span></li>
                        <li x-show="maxDiscount && type === 'percent'">{{ __('vendor.max') }} ₹<span x-text="maxDiscount"></span></li>
                        <li x-show="usageLimit">{{ __('vendor.usage_limit') }}: <span x-text="usageLimit"></span></li>
                        <li x-show="startDate || endDate">
                            <span x-show="startDate && endDate" x-text="startDate + ' – ' + endDate"></span>
                            <span x-show="!startDate && endDate">{{ __('vendor.until') }} <span x-text="endDate"></span></span>
                            <span x-show="startDate && !endDate">{{ __('vendor.from') }} <span x-text="startDate"></span></span>
                        </li>
                    </ul>
                </div>
                <p class="text-[11px] text-gray-500">{{ __('vendor.coupon_form_preview_hint') }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
        <a href="{{ $isEdit ? route('vendor.coupons.show', $coupon) : route('vendor.coupons.index') }}"
           wire:navigate
           class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
            {{ __('vendor.cancel') }}
        </a>
        <button type="submit"
                class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 sm:min-h-[40px]">
            <i class="fas {{ $isEdit ? 'fa-check' : 'fa-plus' }} text-xs" aria-hidden="true"></i>
            {{ $isEdit ? __('vendor.update_coupon') : __('vendor.create_coupon') }}
        </button>
    </div>
</form>
