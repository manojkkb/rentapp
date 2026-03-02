@extends('vendor.layouts.app')

@section('title', 'Edit Category - RentApp')
@section('page-title', 'Edit Category')

@section('content')
<!-- Back Button - Mobile Friendly -->
<div class="mb-4 md:mb-6">
    <a href="{{ route('vendor.categories.index') }}" 
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 bg-white hover:bg-emerald-50 rounded-lg border border-gray-200 transition-all active:scale-95">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="hidden sm:inline">Back to Categories</span>
        <span class="sm:hidden">Back</span>
    </a>
</div>

<div class="max-w-2xl">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Edit Category</h1>
        <p class="text-sm md:text-base text-gray-600">Update category information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Form Header -->
        <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                    <i class="fas fa-edit text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Category Details</h2>
                    <p class="text-xs md:text-sm text-gray-600">Update the information below</p>
                </div>
            </div>
        </div>

        <form action="{{ route('vendor.categories.update', $category) }}" method="POST" class="p-4 md:p-6">
            @csrf
            @method('PUT')

            <!-- Category Type Info -->
            @if($category->parent_id)
                <div class="mb-5 md:mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-layer-group text-emerald-600"></i>
                        <p class="text-sm font-medium text-emerald-900">
                            This is a subcategory under: <span class="font-bold">{{ $category->parent->name }}</span>
                        </p>
                    </div>
                </div>
            @endif

            <!-- Category Name -->
            <div class="mb-5 md:mb-6">
                <label for="name" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $category->name) }}"
                       class="w-full px-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       placeholder="e.g., Electronics, Furniture, Tools"
                       required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Icon -->
            <div class="mb-5 md:mb-6">
                <label for="icon" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    Icon <span class="text-gray-500 text-xs">(FontAwesome class)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="{{ $category->icon ?? 'fas fa-icons' }} text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="icon" 
                           id="icon" 
                           value="{{ old('icon', $category->icon ?? 'fa-folder') }}"
                           class="w-full pl-12 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('icon') border-red-500 @enderror"
                           placeholder="fa-folder, fa-box, fa-tag">
                    @error('icon')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                <p class="mt-2 text-xs md:text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Use FontAwesome class names. Examples: fa-folder, fa-box, fa-tag, fa-laptop, fa-car
                </p>
            </div>

            <!-- Status -->
            <div class="mb-6 md:mb-8">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 mt-0.5">
                        <div class="ml-3">
                            <div class="text-sm md:text-base font-medium text-gray-900">Active Category</div>
                            <div class="text-xs md:text-sm text-gray-600 mt-0.5">Category will be visible to customers</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end space-y-2 space-y-reverse sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.categories.index') }}" 
                   class="w-full sm:w-auto text-center px-5 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-lg font-semibold transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg font-semibold transition-all active:scale-95">
                    <i class="fas fa-save mr-2"></i>
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
