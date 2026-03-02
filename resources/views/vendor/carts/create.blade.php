@extends('vendor.layouts.app')

@section('title', 'Add Cart - RentApp')
@section('page-title', 'Add Cart')

@section('content')
<!-- Back Button -->
<div class="mb-4 md:mb-6">
    <a href="{{ route('vendor.carts.index') }}" 
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 rounded-lg border border-gray-200 transition-all active:scale-95">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="hidden sm:inline">Back to Carts</span>
        <span class="sm:hidden">Back</span>
    </a>
</div>

<div class="max-w-2xl">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Create New Cart</h1>
        <p class="text-sm md:text-base text-gray-600">Start a new cart for a customer</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Form Header -->
        <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-blue-600 rounded-lg">
                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Cart Details</h2>
                    <p class="text-xs md:text-sm text-gray-600">Fill in the information below</p>
                </div>
            </div>
        </div>

        <form action="{{ route('vendor.carts.store') }}" method="POST" class="p-4 md:p-6">
            @csrf

            <!-- Customer Selection -->
            <div class="mb-5 md:mb-6">
                <label for="customer_id" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Select Customer <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <select name="customer_id" 
                            id="customer_id"
                            class="w-full pl-11 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('customer_id') border-red-500 @enderror"
                            required>
                        <option value="">Choose a customer...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} - {{ $customer->mobile }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('customer_id')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
                @if($customers->count() == 0)
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            No customers found. <a href="{{ route('vendor.customers.create') }}" class="font-semibold underline hover:text-yellow-900">Create a customer first</a>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Cart Name -->
            <div class="mb-5 md:mb-6">
                <label for="cart_name" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Cart Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="cart_name" 
                           id="cart_name" 
                           value="{{ old('cart_name') }}"
                           class="w-full pl-11 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('cart_name') border-red-500 @enderror"
                           placeholder="e.g., Wedding Event, Birthday Party, Corporate Meeting"
                           required>
                </div>
                @error('cart_name')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Booking Dates -->
            <div class="mb-6 md:mb-8">
                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-3">
                    <i class="fas fa-calendar text-blue-600 mr-1"></i>
                    Booking Dates <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Start Time -->
                    <div>
                        <label for="start_time" class="block text-xs font-medium text-gray-600 mb-2">
                            Start Date & Time
                        </label>
                        <input type="datetime-local" 
                               name="start_time" 
                               id="start_time" 
                               value="{{ old('start_time') }}"
                               class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('start_time') border-red-500 @enderror">
                        @error('start_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- End Time -->
                    <div>
                        <label for="end_time" class="block text-xs font-medium text-gray-600 mb-2">
                            End Date & Time
                        </label>
                        <input type="datetime-local" 
                               name="end_time" 
                               id="end_time" 
                               value="{{ old('end_time') }}"
                               class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('end_time') border-red-500 @enderror">
                        @error('end_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-blue-900 font-semibold mb-1">After creating the cart</p>
                        <p class="text-sm text-blue-800">
                            You can add items, apply discounts, and manage the booking details.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.carts.index') }}" 
                   class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-center text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                    <i class="fas fa-save mr-2"></i>
                    Create Cart
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
