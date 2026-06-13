@extends('vendor.layouts.app')

@section('title', __('vendor.staff_permissions_title'))
@section('page-title', __('vendor.staff_permissions_title'))

@section('content')
<div class="mx-auto max-w-5xl space-y-5 pb-[max(1rem,env(safe-area-inset-bottom))]">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.staff_permissions_title') }}</h1>
            <p class="mt-0.5 text-sm text-gray-600">{{ __('vendor.staff_permissions_subtitle') }}</p>
        </div>
        <a href="{{ route('vendor.staff-permissions.create') }}"
           wire:navigate
           class="inline-flex min-h-[44px] shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 sm:min-h-[40px]">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.staff_permissions_add_role') }}
        </a>
    </header>

    @if (session('success'))
        <div class="rounded-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
            <p class="text-sm text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-red-700">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @unless ($hasPermissionDefinitions)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            {{ __('vendor.staff_permissions_missing_seed') }}
        </div>
    @endunless

    <div class="space-y-3">
        @forelse ($roles as $role)
            <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-emerald-200/80 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                                <i class="fas fa-user-shield text-sm" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold text-gray-900">
                                    <a href="{{ route('vendor.staff-permissions.show', $role) }}"
                                       wire:navigate
                                       class="hover:text-emerald-700">{{ $role->name }}</a>
                                </h2>
                                @if ($role->is_system)
                                    <span class="mt-0.5 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-600">{{ __('vendor.staff_permissions_system_role') }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($role->description)
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $role->description }}</p>
                        @endif

                        <p class="mt-2 text-xs text-gray-500">
                            {{ __('vendor.staff_permissions_permission_count', ['count' => $role->permissions_count]) }}
                            <span class="mx-1 text-gray-300">·</span>
                            {{ __('vendor.staff_permissions_staff_count', ['count' => $role->vendor_users_count]) }}
                        </p>

                        @if ($role->permissions->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($role->permissions->take(8) as $permission)
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">{{ $permission->label() }}</span>
                                @endforeach
                                @if ($role->permissions->count() > 8)
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">+{{ $role->permissions->count() - 8 }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2 border-t border-gray-100 pt-3 sm:border-0 sm:pt-0">
                        <a href="{{ route('vendor.staff-permissions.show', $role) }}"
                           wire:navigate
                           class="inline-flex min-h-[40px] flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:flex-none">
                            <i class="fas fa-eye text-xs text-gray-400" aria-hidden="true"></i>
                            {{ __('vendor.view') }}
                        </a>
                        <a href="{{ route('vendor.staff-permissions.edit', $role) }}"
                           wire:navigate
                           class="inline-flex min-h-[40px] flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:flex-none">
                            <i class="fas fa-pen text-xs text-gray-400" aria-hidden="true"></i>
                            {{ __('vendor.edit') }}
                        </a>
                        @unless ($role->is_system)
                            <form action="{{ route('vendor.staff-permissions.destroy', $role) }}"
                                  method="POST"
                                  class="flex-1 sm:flex-none"
                                  onsubmit="return confirm(@js(__('vendor.staff_permissions_delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 px-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                    <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                                    {{ __('vendor.delete') }}
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <i class="fas fa-user-shield text-2xl text-gray-400" aria-hidden="true"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">{{ __('vendor.staff_permissions_no_roles') }}</h3>
                <a href="{{ route('vendor.staff-permissions.create') }}"
                   wire:navigate
                   class="mt-5 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('vendor.staff_permissions_add_role') }}
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
