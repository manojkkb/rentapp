@extends('vendor.layouts.app')

@section('title', 'Add Staff Member - RentApp')
@section('page-title', 'Add Staff Member')

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
            <h2 class="text-xl font-bold text-gray-900">Add New Staff Member</h2>
            <p class="text-sm text-gray-600 mt-1">Create a new staff account for your team</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.staff.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Name -->
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
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
                       value="{{ old('mobile') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('mobile') border-red-500 @enderror"
                       placeholder="10-digit mobile number"
                       maxlength="10"
                       pattern="[0-9]{10}"
                       required>
                @error('mobile')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">This will be used for login. Users can work for multiple vendors.</p>
            </div>

            <!-- Email -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address <span class="text-gray-400">(Optional)</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
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
                        <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
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
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Active (Can login immediately)</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.staff.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Add Staff Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
