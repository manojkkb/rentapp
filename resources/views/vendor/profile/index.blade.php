@extends('vendor.layouts.app')

@section('title', 'Vendor Profile - RentApp')
@section('page-title', 'Vendor Profile')

@section('content')
<div>
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
                    <p class="text-sm font-medium text-red-900 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Profile Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Profile Overview</h3>
                </div>

                <!-- Logo and Basic Info -->
                <div class="p-6 text-center">
                    <div class="mb-6">
                        @if($vendor->logo)
                            <img src="{{ asset('storage/' . $vendor->logo) }}" 
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
                    
                    <!-- Status Badges -->
                    <div class="flex items-center justify-center gap-2 mb-4">
                        @if($vendor->is_verified)
                            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">
                                <i class="fas fa-clock mr-1"></i>Pending Verification
                            </span>
                        @endif

                        @if($vendor->is_active)
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                <i class="fas fa-check mr-1"></i>Active
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">
                                <i class="fas fa-times mr-1"></i>Inactive
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
                            <span class="ml-2 text-sm text-gray-600">({{ number_format($vendor->rating, 1) }} / {{ $vendor->total_reviews }} reviews)</span>
                        </div>
                    @endif

                    <!-- GST Number -->
                    @if($vendor->gst_number)
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">GST Number</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $vendor->gst_number }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Edit Profile</h3>
                </div>

                <!-- Form -->
                <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Business Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Business Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $vendor->name) }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>

                    <!-- Logo Upload -->
                    <div class="mb-6">
                        <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                            Business Logo
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($vendor->logo)
                                    <img src="{{ asset('storage/' . $vendor->logo) }}" 
                                         alt="Current logo" 
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
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Max size: 2MB. Formats: JPG, PNG, GIF</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="mb-6">
                        <h4 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-map-marker-alt mr-2 text-emerald-600"></i>Business Address
                        </h4>

                        <!-- Address Line 1 -->
                        <div class="mb-4">
                            <label for="address_line1" class="block text-sm font-semibold text-gray-700 mb-2">
                                Address Line 1
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
                                Address Line 2
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
                                    City
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city', $vendor->city) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                                    State
                                </label>
                                <input type="text" 
                                       id="state" 
                                       name="state" 
                                       value="{{ old('state', $vendor->state) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Postal Code
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
                                Country
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
                            <i class="fas fa-building mr-2 text-emerald-600"></i>Business Details
                        </h4>

                        <!-- GST Number -->
                        <div class="mb-4">
                            <label for="gst_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                GST Number
                            </label>
                            <input type="text" 
                                   id="gst_number" 
                                   name="gst_number" 
                                   value="{{ old('gst_number', $vendor->gst_number) }}"
                                   placeholder="e.g., 22AAAAA0000A1Z5"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Enter your GST registration number if applicable</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('vendor.home') }}" 
                           class="px-6 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
