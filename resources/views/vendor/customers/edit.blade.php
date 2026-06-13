@extends('vendor.layouts.app')

@section('title', __('vendor.edit_customer'))
@section('page-title', __('vendor.edit_customer'))
@section('main_bottom_class', 'pb-36 md:pb-8')

@section('content')
@php
    $fc = 'block w-full min-h-[40px] rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500';
    $flabel = 'mb-0.5 block text-sm font-medium text-gray-800';
    $ferror = 'mt-0.5 text-xs text-red-600';
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white';
    $head = 'border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3';
    $body = 'space-y-3 p-3 sm:space-y-4 sm:p-4';
    $grid2 = 'grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4';
    $initial = strtoupper(substr($customer->name, 0, 1));
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="mb-0 sm:mb-1">
        <div class="mb-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
            <a href="{{ route('vendor.customers.index') }}"
               wire:navigate
               class="inline-flex items-center gap-1.5 text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_customers') }}
            </a>
            <span class="hidden text-gray-300 sm:inline">·</span>
            <a href="{{ route('vendor.customers.show', $customer) }}"
               wire:navigate
               class="hidden truncate font-medium text-gray-600 hover:text-emerald-600 sm:inline">{{ $customer->name }}</a>
        </div>
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.edit_customer_title') }}</h1>
                <p class="mt-0.5 truncate text-sm text-gray-500">{{ $customer->name }}</p>
            </div>
            <div class="hidden shrink-0 items-center gap-2 sm:flex">
                <a href="{{ route('vendor.customers.index') }}"
                   wire:navigate
                   class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit"
                        form="customer-edit-form"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-save text-xs" aria-hidden="true"></i>
                    {{ __('vendor.save_changes') }}
                </button>
            </div>
        </div>
    </header>

    {{-- Customer summary --}}
    <section class="{{ $card }}">
        <div class="flex items-start gap-3 p-3 sm:items-center sm:gap-4 sm:p-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-lg font-bold text-white shadow-sm ring-2 ring-emerald-100 sm:h-14 sm:w-14 sm:text-xl">
                {{ $initial }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-base font-bold text-gray-900 sm:text-lg">{{ $customer->name }}</p>
                <p class="mt-0.5 flex items-center gap-1.5 text-sm text-gray-600">
                    <i class="fas fa-phone text-[10px] text-emerald-600" aria-hidden="true"></i>
                    <span class="font-medium tabular-nums">{{ $customer->mobile }}</span>
                </p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                        {{ $customer->is_active ? __('vendor.active') : __('vendor.inactive') }}
                    </span>
                    @if($customer->user_id)
                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-100">
                            <i class="fas fa-check-circle mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.registered') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <form id="customer-edit-form"
          action="{{ route('vendor.customers.update', $customer) }}"
          method="POST"
          class="space-y-3 sm:space-y-4">
        @csrf
        @method('PUT')

        <section class="{{ $card }}">
            <div class="{{ $head }}">
                <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.customer_contact_details') }}</h2>
            </div>
            <div class="{{ $body }}">
                <div class="{{ $grid2 }}">
                    <div class="sm:col-span-1">
                        <label for="name" class="{{ $flabel }}">
                            {{ __('vendor.customer_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $customer->name) }}"
                               class="{{ $fc }} @error('name') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                               placeholder="{{ __('vendor.customer_name') }}"
                               required>
                        @error('name')
                            <p class="{{ $ferror }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <label for="mobile" class="{{ $flabel }}">
                            {{ __('vendor.customer_mobile') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-phone text-xs" aria-hidden="true"></i>
                            </span>
                            <input type="text"
                                   name="mobile"
                                   id="mobile"
                                   value="{{ old('mobile', $customer->mobile) }}"
                                   class="{{ $fc }} pl-9 @error('mobile') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                   placeholder="10-digit mobile"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   inputmode="numeric"
                                   required>
                        </div>
                        @error('mobile')
                            <p class="{{ $ferror }}">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="{{ $flabel }}">
                        {{ __('vendor.customer_address') }}
                        <span class="text-xs font-normal text-gray-500">({{ __('vendor.optional') }})</span>
                    </label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              class="{{ $fc }} resize-y min-h-[5rem] @error('address') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                              placeholder="{{ __('vendor.customer_address') }}">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <p class="{{ $ferror }}">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 text-xs text-gray-600 sm:flex sm:items-center sm:justify-between sm:gap-4">
                    <p>
                        <span class="font-semibold text-gray-700">{{ __('vendor.customer_member_since') }}:</span>
                        {{ $customer->created_at->format('M j, Y') }}
                    </p>
                    <p class="mt-1 truncate font-mono text-[11px] text-gray-500 sm:mt-0 sm:max-w-[14rem]">{{ $customer->uuid }}</p>
                </div>
            </div>
        </section>
    </form>

    {{-- Mobile sticky actions --}}
    <div class="fixed inset-x-0 bottom-16 z-50 border-t border-gray-200 bg-white/95 px-3 py-2.5 backdrop-blur-md md:hidden">
        <div class="mx-auto flex max-w-4xl gap-2">
            <a href="{{ route('vendor.customers.index') }}"
               wire:navigate
               class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                {{ __('vendor.cancel') }}
            </a>
            <button type="submit"
                    form="customer-edit-form"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-save text-xs" aria-hidden="true"></i>
                {{ __('vendor.save_changes') }}
            </button>
        </div>
    </div>
</div>
@endsection
