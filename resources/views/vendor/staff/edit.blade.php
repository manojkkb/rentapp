@extends('vendor.layouts.app')

@section('title', 'Edit Staff Member - RentApp')
@section('page-title', 'Edit Staff Member')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('vendor.staff.index') }}" 
       class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Staff
    </a>
</div>

<!-- Form Card -->
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
            <h2 class="text-xl font-bold text-gray-900">Edit Staff Member</h2>
            <p class="text-sm text-gray-600 mt-1">Update staff member information</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.staff.update', $vendorUser->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $staffUser->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       placeholder="Enter full name"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile -->
            <div class="mb-5">
                <label for="mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="mobile" 
                       name="mobile" 
                       value="{{ old('mobile', $staffUser->mobile) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('mobile') border-red-500 @enderror"
                       placeholder="10-digit mobile number"
                       maxlength="10"
                       pattern="[0-9]{10}"
                       required>
                @error('mobile')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address <span class="text-gray-400">(Optional)</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', !str_contains($staffUser->email, '@staff.temp') && !str_contains($staffUser->email, '@rentapp.temp') ? $staffUser->email : '') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                       placeholder="email@example.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-5">
                <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role" 
                        name="role" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('role') border-red-500 @enderror"
                        required>
                    <option value="">Select role</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', $vendorUser->role) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $vendorUser->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Active (Can login)</span>
                </label>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Password Section -->
            <div class="mb-6" x-data="{ showPassword: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                    <button type="button" 
                            @click="showPassword = !showPassword"
                            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                        <span x-show="!showPassword">Show Form</span>
                        <span x-show="showPassword" style="display: none;">Hide Form</span>
                    </button>
                </div>

                <div x-show="showPassword" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display: none;">
                    
                    <div class="p-4 bg-gray-50 rounded-lg mb-4">
                        <p class="text-xs text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Leave blank to keep the current password
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            New Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                               placeholder="Enter new password"
                               minlength="6">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                               placeholder="Confirm new password"
                               minlength="6">
                    </div>
                </div>
            </div>

            <!-- Last Login Info -->
            @if($vendorUser->last_login_at)
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                    <strong>Last Login:</strong> {{ \Carbon\Carbon::parse($vendorUser->last_login_at)->format('M d, Y h:i A') }}
                    ({{ \Carbon\Carbon::parse($vendorUser->last_login_at)->diffForHumans() }})
                </p>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.staff.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Update Staff Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
