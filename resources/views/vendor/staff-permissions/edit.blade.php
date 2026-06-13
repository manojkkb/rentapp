@extends('vendor.layouts.app')

@section('title', __('vendor.staff_permissions_edit_role'))
@section('page-title', __('vendor.staff_permissions_edit_role'))

@section('content')
<div class="mx-auto max-w-3xl space-y-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
    <header>
        <a href="{{ route('vendor.staff-permissions.show', $role) }}"
           wire:navigate
           class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back_to_role') }}
        </a>
        <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.staff_permissions_edit_role') }}</h1>
        <p class="mt-0.5 text-sm text-gray-600">{{ $role->name }}</p>
    </header>

    @if ($permissions->isEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            {{ __('vendor.staff_permissions_missing_seed') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-red-700">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @include('vendor.staff-permissions.partials.role-form', ['role' => $role])
</div>
@endsection
