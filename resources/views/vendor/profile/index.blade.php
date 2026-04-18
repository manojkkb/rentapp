@extends('vendor.layouts.app')

@section('title', __('vendor.vendor_profile'))
@section('page-title', __('vendor.vendor_profile'))

@section('content')
<div x-data="{ activeTab: 'personal' }">
    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start space-x-3">
            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-900">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Error Messages -->
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 mb-2">{{ __('vendor.fix_errors') }}</p>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <button @click="activeTab = 'personal'" 
                    :class="activeTab === 'personal' ? 'bg-emerald-50 text-emerald-700 border-b-2 border-emerald-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                    class="flex-1 px-6 py-4 text-sm font-semibold transition-colors">
                <i class="fas fa-user mr-2"></i>
                Personal Profile
            </button>
            <button @click="activeTab = 'business'" 
                    :class="activeTab === 'business' ? 'bg-emerald-50 text-emerald-700 border-b-2 border-emerald-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                    class="flex-1 px-6 py-4 text-sm font-semibold transition-colors">
                <i class="fas fa-building mr-2"></i>
                Business Profile
            </button>
        </div>
    </div>

    <!-- Personal Profile Section -->
    <div x-show="activeTab === 'personal'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Personal Info Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Personal Overview</h3>
                    </div>
                    <div class="p-6 text-center">
                        <div class="mb-6">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}"
                                     alt=""
                                     class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-blue-100 shadow-lg">
                            @else
                                <div class="w-32 h-32 rounded-full mx-auto bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center border-4 border-blue-100 shadow-lg">
                                    <span class="text-white text-4xl font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ Auth::user()->name }}</h2>
                        <p class="text-gray-600 mb-4">
                            <i class="fas fa-phone mr-2"></i>{{ Auth::user()->mobile }}
                        </p>
                        @if(Auth::user()->email)
                            <p class="text-gray-600">
                                <i class="fas fa-envelope mr-2"></i>{{ Auth::user()->email }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Edit Personal Info Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Edit Personal Information</h3>
                    </div>
                    <form action="{{ route('vendor.profile.update.personal') }}" method="POST" enctype="multipart/form-data" class="p-6">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label for="avatar" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.profile_photo') }} <span class="text-gray-400 font-normal">({{ __('vendor.optional') }})</span>
                            </label>
                            <input type="file"
                                   id="avatar"
                                   name="avatar"
                                   accept="image/*"
                                   class="js-user-avatar-input block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 border border-gray-300 rounded-lg cursor-pointer @error('avatar') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.profile_photo_crop_hint') }}</p>
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="user_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="user_name" 
                                   name="name" 
                                   value="{{ old('name', Auth::user()->name) }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="mb-6">
                            <label for="user_mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                                Mobile Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="user_mobile" 
                                   name="mobile" 
                                   value="{{ Auth::user()->mobile }}"
                                   readonly
                                   disabled
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Mobile number cannot be changed
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="user_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   id="user_email" 
                                   name="email" 
                                   value="{{ old('email', Auth::user()->email) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button type="submit" 
                                    class="px-6 py-3 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <i class="fas fa-save mr-2"></i>{{ __('vendor.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Profile Section -->
    <div x-show="activeTab === 'business'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Profile Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.profile_overview') }}</h3>
                </div>

                <!-- Logo and Basic Info -->
                <div class="p-6 text-center">
                    <div class="mb-6">
                        @if($vendor->logo_url)
                            <img src="{{ $vendor->logo_url }}"
                                 alt="{{ $vendor->name }}"
                                 class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-emerald-100 shadow-lg">
                        @else
                            <div class="w-32 h-32 rounded-full mx-auto bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center border-4 border-emerald-100 shadow-lg">
                                <span class="text-white text-4xl font-bold">
                                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $vendor->name }}</h2>
                    
                    <!-- Business Category -->
                    @if($vendor->businessCategory)
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-briefcase mr-1"></i>{{ $vendor->businessCategory->name }}
                        </p>
                    @endif
                    
                    <!-- Status Badges -->
                    <div class="flex items-center justify-center gap-2 mb-4">
                        @if($vendor->is_verified)
                            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>{{ __('vendor.verified') }}
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">
                                <i class="fas fa-clock mr-1"></i>{{ __('vendor.pending_verification') }}
                            </span>
                        @endif

                        @if($vendor->is_active)
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                <i class="fas fa-check mr-1"></i>{{ __('vendor.active') }}
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">
                                <i class="fas fa-times mr-1"></i>{{ __('vendor.inactive') }}
                            </span>
                        @endif
                    </div>

                    <!-- Rating -->
                    @if($vendor->rating > 0)
                        <div class="flex items-center justify-center mb-4">
                            <div class="flex items-center space-x-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($vendor->rating))
                                        <i class="fas fa-star text-yellow-500"></i>
                                    @elseif($i - 0.5 <= $vendor->rating)
                                        <i class="fas fa-star-half-alt text-yellow-500"></i>
                                    @else
                                        <i class="far fa-star text-yellow-500"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-2 text-sm text-gray-600">({{ number_format($vendor->rating, 1) }} / {{ $vendor->total_reviews }} {{ __('vendor.reviews') }})</span>
                        </div>
                    @endif

                    <!-- GST Number -->
                    @if($vendor->gst_number)
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">{{ __('vendor.gst_number') }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $vendor->gst_number }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit Business Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Edit Business Information</h3>
                </div>

                @php
                    $vendorUser = Auth::user()->vendors()->where('vendors.id', $vendor->id)->first();
                    $isOwner = $vendorUser && $vendorUser->pivot->is_owner;
                @endphp

                @if(!$isOwner)
                    <div class="p-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start space-x-3">
                            <i class="fas fa-lock text-yellow-600 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900 mb-1">Owner Access Only</p>
                                <p class="text-sm text-yellow-800">Only the business owner can edit business information. Contact your administrator for changes.</p>
                            </div>
                        </div>
                    </div>
                @else
                <!-- Form -->
                <form action="{{ route('vendor.profile.update.business') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Business Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.business_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $vendor->name) }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>

                    <!-- Business Category -->
                    <div class="mb-6">
                        <label for="business_category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Business Category <span class="text-red-500">*</span>
                        </label>
                        <select id="business_category_id" 
                                name="business_category_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('business_category_id', $vendor->business_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">{{ __('vendor.category_help_text') }}</p>
                    </div>

                    <!-- Logo Upload -->
                    <div class="mb-6">
                        <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.business_logo') }}
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($vendor->logo_url)
                                    <img src="{{ $vendor->logo_url }}"
                                         alt="{{ __('vendor.current_logo') }}"
                                         class="w-16 h-16 rounded-lg object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file"
                                       id="logo"
                                       name="logo"
                                       accept="image/*"
                                       class="js-vendor-logo-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('logo') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">{{ __('vendor.business_logo_crop_hint') }}</p>
                                @error('logo')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="mb-6">
                        <h4 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-map-marker-alt mr-2 text-emerald-600"></i>{{ __('vendor.business_address') }}
                        </h4>

                        <!-- Address Line 1 -->
                        <div class="mb-4">
                            <label for="address_line1" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.address_line1') }}
                            </label>
                            <input type="text" 
                                   id="address_line1" 
                                   name="address_line1" 
                                   value="{{ old('address_line1', $vendor->address_line1) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>

                        <!-- Address Line 2 -->
                        <div class="mb-4">
                            <label for="address_line2" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.address_line2') }}
                            </label>
                            <input type="text" 
                                   id="address_line2" 
                                   name="address_line2" 
                                   value="{{ old('address_line2', $vendor->address_line2) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>

                        <!-- City, State, Postal Code -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('vendor.city') }}
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city', $vendor->city) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('vendor.state') }}
                                </label>
                                <input type="text" 
                                       id="state" 
                                       name="state" 
                                       value="{{ old('state', $vendor->state) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('vendor.postal_code') }}
                                </label>
                                <input type="text" 
                                       id="postal_code" 
                                       name="postal_code" 
                                       value="{{ old('postal_code', $vendor->postal_code) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Country -->
                        <div class="mb-4">
                            <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.country') }}
                            </label>
                            <input type="text" 
                                   id="country" 
                                   name="country" 
                                   value="{{ old('country', $vendor->country) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Business Details -->
                    <div class="mb-6">
                        <h4 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-building mr-2 text-emerald-600"></i>{{ __('vendor.business_details') }}
                        </h4>

                        <!-- GST Number -->
                        <div class="mb-4">
                            <label for="gst_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.gst_number') }}
                            </label>
                            <input type="text" 
                                   id="gst_number" 
                                   name="gst_number" 
                                   value="{{ old('gst_number', $vendor->gst_number) }}"
                                   placeholder="{{ __('vendor.gst_placeholder') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">{{ __('vendor.gst_help_text') }}</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('vendor.home') }}" 
                           class="px-6 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>{{ __('vendor.cancel') }}
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <i class="fas fa-save mr-2"></i>{{ __('vendor.save_changes') }}
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@include('vendor.profile.partials.vendor-logo-crop-modal')
@include('vendor.profile.partials.user-avatar-crop-modal')
@endsection
