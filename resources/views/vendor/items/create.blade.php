@extends('vendor.layouts.app')

@section('title', __('vendor.add_item'))
@section('page-title', __('vendor.add_item'))

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('vendor.items.index') }}" 
       class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        {{ __('vendor.back_to_items') }}
    </a>
</div>

<!-- Form Card -->
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
            <h2 class="text-xl font-bold text-gray-900">{{ __('vendor.add_new_item_title') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('vendor.create_new_rental_item') }}</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.items.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Item Name -->
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('vendor.item_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       placeholder="{{ __('vendor.item_name_placeholder') }}"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-5">
                <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('vendor.category') }} <span class="text-red-500">*</span>
                </label>
                <select id="category_id" 
                        name="category_id" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('category_id') border-red-500 @enderror"
                        required>
                    <option value="">{{ __('vendor.select_category') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($categories->isEmpty())
                    <p class="mt-1 text-xs text-orange-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        {{ __('vendor.no_categories_found') }} <a href="{{ route('vendor.categories.create') }}" class="underline">{{ __('vendor.create_category_first') }}</a>
                    </p>
                @endif
            </div>

            <!-- Photo (square crop before upload) -->
            <div class="mb-5">
                <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('vendor.item_photo') }} <span class="text-gray-400 font-normal">({{ __('vendor.optional') }})</span>
                </label>
                <input type="file"
                       id="photo"
                       name="photo"
                       accept="image/*"
                       class="js-item-image-input block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-lg cursor-pointer @error('photo') border-red-500 @enderror">
                <p class="mt-2 text-xs text-gray-500">{{ __('vendor.item_photo_crop_hint') }}</p>
                @error('photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('vendor.description') }} <span class="text-gray-400">({{ __('vendor.optional') }})</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                          placeholder="{{ __('vendor.description_placeholder') }}">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pricing Section -->
            <div class="border-t border-gray-200 pt-5 mb-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.pricing') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.price') }} (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="{{ old('price') }}"
                               step="0.01"
                               min="0"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('price') border-red-500 @enderror"
                               placeholder="0.00"
                               required>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price Type -->
                    <div>
                        <label for="price_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.price_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="price_type" 
                                name="price_type" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('price_type') border-red-500 @enderror"
                                required>
                            @foreach($priceTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('price_type', 'per_day') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('price_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Stock Management Section -->
            <div class="border-t border-gray-200 pt-5 mb-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.stock') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Stock -->
                    <div>
                        <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="stock" 
                               name="stock" 
                               value="{{ old('stock', 1) }}"
                               min="0"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('stock') border-red-500 @enderror"
                               required>
                        @error('stock')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manage Stock -->
                    <div class="flex items-end">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="manage_stock" 
                                   value="1"
                                   {{ old('manage_stock', true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Track stock quantity</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <div class="border-t border-gray-200 pt-5 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.status') }}</h3>
                
                <div class="space-y-3">
                    <!-- Is Available -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_available" 
                               value="1"
                               {{ old('is_available', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.available_for_rent') }}</span>
                    </label>

                    <!-- Is Active -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.active') }}</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.items.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('vendor.add_item') }}
                </button>
            </div>
        </form>
    </div>
</div>

@include('vendor.items.partials.item-image-crop-modal')
@endsection
