@extends('admin.layouts.app')

@section('title', 'My Profile - Admin')

@section('content')
@php
    $avatarUrl = $admin->avatar_url ?? $admin->initialsAvatarUrl();
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">My Profile</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your account details and password</p>
    </div>

    @include('admin.users.partials.alerts')

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col items-center gap-4 border-b border-gray-100 pb-6 dark:border-gray-700 sm:flex-row sm:items-start">
            <img src="{{ $avatarUrl }}" alt="{{ $admin->name }}"
                 class="h-20 w-20 rounded-2xl object-cover ring-2 ring-green-500">
            <div class="text-center sm:text-left">
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $admin->name }}</p>
                <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                @if($admin->phone)
                    <p class="text-sm text-gray-500">{{ $admin->phone }}</p>
                @endif
                <div class="mt-2 flex flex-wrap justify-center gap-2 sm:justify-start">
                    @if($admin->is_super_admin)
                        <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">Super Admin</span>
                    @endif
                    @if($admin->is_active)
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">Active</span>
                    @endif
                </div>
                <p class="mt-2 text-xs text-gray-400">Member since {{ $admin->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Profile photo</label>
                <input type="file" name="avatar" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:font-semibold file:text-green-700 dark:text-gray-400">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Full name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                           placeholder="Optional">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-5 dark:border-gray-700">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Change password</h2>
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep your current password</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Current password</label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">New password</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Confirm new password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                    Save changes
                </button>
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
