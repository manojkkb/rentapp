@extends('vendor.layouts.app')

@section('title', $role->name)
@section('page-title', __('vendor.staff_permissions_role_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm';
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('vendor.staff-permissions.index') }}"
               wire:navigate
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_staff_permissions') }}
            </a>
            <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ $role->name }}</h1>
            @if($role->description)
                <p class="mt-0.5 text-sm text-gray-600">{{ $role->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('vendor.staff-permissions.edit', $role) }}"
               wire:navigate
               class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                {{ __('vendor.staff_permissions_edit_role') }}
            </a>
            @unless($role->is_system)
                <form action="{{ route('vendor.staff-permissions.destroy', $role) }}"
                      method="POST"
                      onsubmit="return confirm(@js(__('vendor.staff_permissions_delete_confirm')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                        <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                        {{ __('vendor.delete') }}
                    </button>
                </form>
            @endunless
        </div>
    </header>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
            <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <p class="flex-1">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
            @foreach($errors->all() as $error)
                <p class="text-sm text-red-700">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="{{ $card }}">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/70">{{ __('vendor.staff_permissions') }}</p>
                    <p class="mt-2 text-3xl font-bold">{{ $role->permissions->count() }}</p>
                    <p class="mt-1 text-sm text-white/80">{{ __('vendor.staff_permissions_permissions') }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                    <i class="fas fa-user-shield text-xl" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                    {{ __('vendor.staff_permissions_staff_count', ['count' => $role->vendor_users_count]) }}
                </span>
                @if($role->is_system)
                    <span class="inline-flex rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                        {{ __('vendor.staff_permissions_system_role') }}
                    </span>
                @endif
            </div>
        </div>
    </section>

    <section class="{{ $card }}">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.staff_permissions_role_details') }}</h2>
        </div>
        <dl class="grid grid-cols-1 gap-px bg-gray-100 sm:grid-cols-2">
            <div class="bg-white px-4 py-3 sm:col-span-2">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.staff_permissions_role_name') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $role->name }}</dd>
            </div>
            <div class="bg-white px-4 py-3 sm:col-span-2">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.staff_permissions_role_description') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $role->description ?: __('vendor.not_specified') }}</dd>
            </div>
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.staff_permissions_permissions') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $role->permissions->count() }}</dd>
            </div>
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.staff') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $role->vendor_users_count }}</dd>
            </div>
        </dl>
    </section>

    <section class="{{ $card }}">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.staff_permissions_permissions') }}</h2>
        </div>
        <div class="p-4 sm:p-5">
            @if($permissionsByGroup->isEmpty())
                <p class="text-sm text-gray-500">{{ __('vendor.staff_permissions_no_permission_list') }}</p>
            @else
                <div class="space-y-4">
                    @foreach($permissionsByGroup as $group => $groupPermissions)
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ \App\Models\VendorPermission::groupLabel($group) }}</p>
                            <ul class="flex flex-wrap gap-2">
                                @foreach($groupPermissions as $permission)
                                    <li class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800 ring-1 ring-emerald-100">
                                        <i class="fas fa-check text-[10px] text-emerald-600" aria-hidden="true"></i>
                                        {{ $permission->label() }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="{{ $card }}">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.staff_permissions_assigned_staff') }}</h2>
        </div>
        @if($staffMembers->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-gray-500 sm:px-5">
                {{ __('vendor.staff_permissions_no_assigned_staff') }}
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($staffMembers as $member)
                    <li class="flex items-center justify-between gap-3 px-4 py-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $member->user?->name ?? __('vendor.staff') }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $member->user?->email }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $member->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' }}">
                                {{ $member->is_active ? __('vendor.active') : __('vendor.inactive') }}
                            </span>
                            @if(Route::has('vendor.staff.show'))
                                <a href="{{ route('vendor.staff.show', $member) }}"
                                   wire:navigate
                                   class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                    {{ __('vendor.view') }}
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
