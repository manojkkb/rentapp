@extends('vendor.layouts.app')

@section('title', __('vendor.add_staff_member'))
@section('page-title', __('vendor.add_staff_member'))
@section('main_bottom_class', 'pb-36 md:pb-8')

@section('content')
@php
    $ifc = 'block w-full min-h-[44px] rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-base sm:text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500';
    $ilabel = 'mb-1 block text-sm font-medium text-gray-800';
    $ihint = 'mt-1 text-xs leading-snug text-gray-500';
    $ierror = 'mt-1 text-xs font-medium text-red-600';
    $ireq = '<span class="text-red-500" aria-hidden="true">*</span>';
    $icard = 'overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80';
    $ihead = 'flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-slate-50 via-white to-emerald-50/30 px-4 py-3.5 sm:px-5 sm:py-4';
    $ibody = 'space-y-4 p-4 sm:p-5';
@endphp

<div class="mx-auto w-full max-w-2xl">
    <header class="mb-3 sm:mb-4">
        <a href="{{ route('vendor.staff.index') }}"
           class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back_to_staff') }}
        </a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.add_new_staff_title') }}</h1>
                <p class="mt-0.5 text-xs text-gray-600 sm:text-sm">{{ __('vendor.staff_create_subtitle') }}</p>
            </div>
            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('vendor.staff.index') }}"
                   class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit"
                        form="staff-create-form"
                        @if($roles->isEmpty()) disabled @endif
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('vendor.add_staff_member') }}
                </button>
            </div>
        </div>
    </header>

    <div class="mb-3 rounded-xl border border-amber-100 bg-amber-50/80 px-3.5 py-2.5 text-xs text-amber-900 sm:text-sm">
        <i class="fas fa-asterisk mr-1.5 text-[10px] text-red-500" aria-hidden="true"></i>
        {{ __('vendor.required_fields_note') }}
    </div>

    <section class="{{ $icard }}">
        <div class="{{ $ihead }}">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm shadow-emerald-600/20">
                <i class="fas fa-user-plus text-sm" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.staff_form_section_details') }}</h2>
                <p class="text-xs text-gray-600 sm:text-sm">{{ __('vendor.staff_create_subtitle') }}</p>
            </div>
        </div>

        <form id="staff-create-form" action="{{ route('vendor.staff.store') }}" method="POST" class="{{ $ibody }}">
            @csrf

            <div>
                <label for="name" class="{{ $ilabel }}">
                    {{ __('vendor.staff_name') }} {!! $ireq !!}
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="{{ $ifc }} @error('name') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="{{ __('vendor.staff_name') }}"
                       required
                       autocomplete="name">
                @error('name')
                    <p class="{{ $ierror }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="mobile" class="{{ $ilabel }}">
                    {{ __('vendor.mobile') }} {!! $ireq !!}
                </label>
                <input type="tel"
                       id="mobile"
                       name="mobile"
                       value="{{ old('mobile') }}"
                       class="{{ $ifc }} @error('mobile') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="10-digit mobile number"
                       maxlength="10"
                       inputmode="numeric"
                       pattern="[0-9]{10}"
                       required
                       autocomplete="tel">
                @error('mobile')
                    <p class="{{ $ierror }}">{{ $message }}</p>
                @enderror
                <p class="{{ $ihint }}">{{ __('vendor.staff_mobile_login_hint') }}</p>
            </div>

            <div>
                <label for="email" class="{{ $ilabel }}">
                    {{ __('vendor.email') }}
                    <span class="font-normal text-gray-500">({{ __('vendor.optional') }})</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="{{ $ifc }} @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="email@example.com"
                       autocomplete="email">
                @error('email')
                    <p class="{{ $ierror }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="vendor_role_id" class="{{ $ilabel }}">
                    {{ __('vendor.staff_role') }} {!! $ireq !!}
                </label>
                @if($roles->isEmpty())
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-3 text-sm text-amber-800">
                        {{ __('vendor.staff_no_roles_hint') }}
                        <a href="{{ route('vendor.staff-permissions.index') }}" class="font-semibold text-amber-900 underline hover:text-amber-950">
                            {{ __('vendor.staff_permissions') }}
                        </a>
                    </p>
                @else
                    <select id="vendor_role_id"
                            name="vendor_role_id"
                            class="{{ $ifc }} @error('vendor_role_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            required>
                        <option value="">{{ __('vendor.staff_select_role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ (string) old('vendor_role_id') === (string) $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_role_id')
                        <p class="{{ $ierror }}">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50/80 px-3.5 py-3">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-800">{{ __('vendor.active') }}</span>
                        <span class="mt-0.5 block text-xs text-gray-500">{{ __('vendor.staff_active_hint') }}</span>
                    </span>
                </label>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end sm:pt-5">
                <a href="{{ route('vendor.staff.index') }}"
                   class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit"
                        @if($roles->isEmpty()) disabled @endif
                        class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 sm:min-h-[40px]">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('vendor.add_staff_member') }}
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
