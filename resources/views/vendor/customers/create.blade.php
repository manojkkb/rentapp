@extends('vendor.layouts.app')

@section('title', 'Add Customer - Rentkia')
@section('page-title', 'Add Customer')

@section('content')
<!-- Back Button -->
<div class="mb-4 md:mb-6">
    <a href="{{ route('vendor.customers.index') }}" 
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 bg-white hover:bg-emerald-50 rounded-lg border border-gray-200 transition-all active:scale-95">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="hidden sm:inline">Back to Customers</span>
        <span class="sm:hidden">Back</span>
    </a>
</div>

<div class="max-w-2xl">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Add New Customer</h1>
        <p class="text-sm md:text-base text-gray-600">Register a new customer to your database</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Form Header -->
        <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Customer Details</h2>
                    <p class="text-xs md:text-sm text-gray-600">Fill in the information below</p>
                </div>
            </div>
        </div>

        <form action="{{ route('vendor.customers.store') }}" method="POST" class="p-4 md:p-6">
            @csrf

            <!-- Customer Name -->
            <div class="mb-5 md:mb-6">
                <label for="name" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       placeholder="Enter customer's full name"
                       required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Mobile Number -->
            <div class="mb-5 md:mb-6">
                <label for="mobile" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-phone text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="mobile" 
                           id="mobile" 
                           value="{{ old('mobile') }}"
                           class="w-full pl-11 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('mobile') border-red-500 @enderror"
                           placeholder="10-digit mobile number"
                           maxlength="10"
                           pattern="[0-9]{10}"
                           required>
                </div>
                @error('mobile')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Address -->
            <div class="mb-6 md:mb-8">
                <label for="address" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Address <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <textarea 
                    name="address" 
                    id="address" 
                    rows="3"
                    class="w-full px-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none @error('address') border-red-500 @enderror"
                    placeholder="Enter customer's address">{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.customers.index') }}" 
                   class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-center text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                    <i class="fas fa-save mr-2"></i>
                    Save Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
