@extends('vendor.layouts.app')

@section('title', 'Edit Item - RentApp')
@section('page-title', 'Edit Item')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('vendor.items.index') }}" 
       class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Items
    </a>
</div>

<!-- Form Card -->
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
            <h2 class="text-xl font-bold text-gray-900">Edit Item</h2>
            <p class="text-sm text-gray-600 mt-1">Update item details and settings</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.items.update', $item->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Current Slug Info -->
            <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-link mr-2"></i>
                    <strong>Current URL:</strong> <code class="bg-blue-100 px-2 py-1 rounded">{{ $item->slug }}</code>
                </p>
                <p class="text-xs text-blue-600 mt-1">Slug will be auto-updated if you change the item name</p>
            </div>

            <!-- Item Name -->
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Item Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $item->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       placeholder="e.g., Camera, Laptop, Tent"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-5">
                <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="category_id" 
                        name="category_id" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('category_id') border-red-500 @enderror"
                        required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" 
                                {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Description <span class="text-gray-400">(Optional)</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                          placeholder="Describe the item features, condition, specifications...">{{ old('description', $item->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pricing Section -->
            <div class="border-t border-gray-200 pt-5 mb-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                            Price (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="{{ old('price', $item->price) }}"
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
                            Price Type <span class="text-red-500">*</span>
                        </label>
                        <select id="price_type" 
                                name="price_type" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('price_type') border-red-500 @enderror"
                                required>
                            @foreach($priceTypes as $key => $label)
                                <option value="{{ $key }}" 
                                        {{ old('price_type', $item->price_type) == $key ? 'selected' : '' }}>
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
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Stock -->
                    <div>
                        <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">
                            Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="stock" 
                               name="stock" 
                               value="{{ old('stock', $item->stock) }}"
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
                                   {{ old('manage_stock', $item->manage_stock) ? 'checked' : '' }}
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Track stock quantity</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <div class="border-t border-gray-200 pt-5 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                
                <div class="space-y-3">
                    <!-- Is Available -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_available" 
                               value="1"
                               {{ old('is_available', $item->is_available) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Available for rent</span>
                    </label>

                    <!-- Is Active -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Active (visible to customers)</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <!-- Delete Button -->
                <button type="button"
                        onclick="if(confirm('Are you sure you want to delete this item?')) document.getElementById('delete-form').submit();"
                        class="px-5 py-2.5 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Item
                </button>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('vendor.items.index') }}" 
                       class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Update Item
                    </button>
                </div>
            </div>
        </form>

        <!-- Separate Delete Form -->
        <form id="delete-form" 
              action="{{ route('vendor.items.destroy', $item->id) }}" 
              method="POST" 
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
