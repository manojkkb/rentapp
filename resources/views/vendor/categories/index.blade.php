@extends('vendor.layouts.app')

@section('title', __('vendor.categories_management'))
@section('page-title', __('vendor.categories'))

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('vendor.categories') }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-folder-tree text-emerald-600 mr-1"></i>
                <span class="font-medium">{{ $categories->total() }}</span> {{ __('vendor.total_categories_count', ['count' => $categories->total()]) }}
            </p>
        </div>
        <button type="button"
                @click="$dispatch('open-create-modal')" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>
            {{ __('vendor.add_category') }}
        </button>
    </div>

    <!-- Messages -->
    @if (session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <div class="text-red-700 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" 
                   id="categorySearch" 
                   class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" 
                   placeholder="{{ __('vendor.search') }} {{ __('vendor.categories') }}...">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <div id="searchSpinner" class="hidden">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Shimmer Loading Indicator -->
    <div id="categoriesLoadingIndicator" class="hidden">
        <style>
            @keyframes shimmer {
                0% { background-position: -1000px 0; }
                100% { background-position: 1000px 0; }
            }
            .shimmer {
                animation: shimmer 2s infinite linear;
                background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
                background-size: 1000px 100%;
            }
        </style>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Desktop Table Shimmer -->
            <div class="hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                                <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                                <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                                <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-32"></div></th>
                                <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                                        <div class="w-32 h-4 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                                <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                                <td class="px-6 py-4"><div class="h-3 bg-gray-200 rounded shimmer w-28"></div></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                                        <div class="w-40 h-4 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                                <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                                <td class="px-6 py-4"><div class="h-3 bg-gray-200 rounded shimmer w-28"></div></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                                        <div class="w-36 h-4 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                                <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                                <td class="px-6 py-4"><div class="h-3 bg-gray-200 rounded shimmer w-28"></div></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Mobile Cards Shimmer -->
            <div class="md:hidden divide-y divide-gray-200">
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                            <div>
                                <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </div>
                        </div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        <div class="flex space-x-2">
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                            <div>
                                <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </div>
                        </div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        <div class="flex space-x-2">
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                            <div>
                                <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </div>
                        </div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        <div class="flex space-x-2">
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                            <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200" id="categoriesContainer">
        @include('vendor.categories.partials.categories-list', ['categories' => $categories])
    </div>
</div>

<!-- Create Category Modal -->
<div x-data="{ open: false }" 
     @open-create-modal.window="open = true"
     @close-create-modal.window="open = false"
     @keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     id="createCategoryModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         @click="open = false"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>
    
    <!-- Modal Container -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0 relative z-10">
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
             x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                            <i class="fas fa-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('vendor.add_new_category_title') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('vendor.fill_information') }}</p>
                        </div>
                    </div>
                    <button @click="open = false" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="createCategoryForm" class="p-6">
                @csrf

                <!-- Category Name -->
                <div class="mb-5">
                    <label for="modal_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="modal_name" 
                           class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                           placeholder="{{ __('vendor.category_name_placeholder') }}"
                           required>
                    <p class="mt-1 text-sm text-red-600 hidden" id="name_error"></p>
                </div>

                <!-- Icon -->
                <div class="mb-5">
                    <label for="modal_icon" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('vendor.optional') }} Icon <span class="text-gray-500 text-xs">(FontAwesome class)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-icons text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="icon" 
                               id="modal_icon" 
                               value="fa-folder"
                               class="w-full pl-12 pr-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                               placeholder="fa-folder, fa-box, fa-tag">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Use FontAwesome class names. Examples: fa-folder, fa-box, fa-tag
                    </p>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="modal_is_active" 
                                   checked
                                   class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 mt-0.5">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">{{ __('vendor.active') }}</div>
                                <div class="text-xs text-gray-600 mt-0.5">{{ __('vendor.category') }} {{ __('vendor.available') }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Form Errors -->
                <div id="formErrors" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-red-700" id="formErrorsContent"></div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end space-y-2 space-y-reverse sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            @click="open = false"
                            class="w-full sm:w-auto text-center px-5 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-lg font-semibold transition-all">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg font-semibold transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>
                        <span id="submitBtnText">{{ __('vendor.add_category') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div x-data="{ open: false }" 
     @open-edit-modal.window="open = true"
     @close-edit-modal.window="open = false"
     @keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     id="editCategoryModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         @click="open = false"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>
    
    <!-- Modal Container -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0 relative z-10">
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
             x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 flex items-center justify-center bg-blue-600 rounded-lg">
                            <i class="fas fa-edit text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('vendor.edit_category') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('vendor.update_category_information') }}</p>
                        </div>
                    </div>
                    <button @click="open = false" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="editCategoryForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="category_id" id="edit_category_id">

                <!-- Category Name -->
                <div class="mb-5">
                    <label for="edit_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="edit_name" 
                           class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="{{ __('vendor.category_name_placeholder') }}"
                           required>
                    <p class="mt-1 text-sm text-red-600 hidden" id="edit_name_error"></p>
                </div>

                <!-- Icon -->
                <div class="mb-5">
                    <label for="edit_icon" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('vendor.optional') }} Icon <span class="text-gray-500 text-xs">(FontAwesome class)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-icons text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="icon" 
                               id="edit_icon" 
                               class="w-full pl-12 pr-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                               placeholder="fa-folder, fa-box, fa-tag">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Use FontAwesome class names. Examples: fa-folder, fa-box, fa-tag
                    </p>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="edit_is_active" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">{{ __('vendor.active') }}</div>
                                <div class="text-xs text-gray-600 mt-0.5">{{ __('vendor.category') }} {{ __('vendor.available') }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Form Errors -->
                <div id="editFormErrors" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-red-700" id="editFormErrorsContent"></div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end space-y-2 space-y-reverse sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            @click="open = false"
                            class="w-full sm:w-auto text-center px-5 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-lg font-semibold transition-all">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" 
                            id="editSubmitBtn"
                            class="w-full sm:w-auto px-5 py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg font-semibold transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>
                        <span id="editSubmitBtnText">{{ __('vendor.update_category') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
[x-cloak] {
    display: none !important;
}
</style>
@endpush

@section('scripts')
<script>
// AJAX toggle function for category status
function toggleStatus(url, element, alpineData) {
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Disable button during request
    element.disabled = true;
    element.style.opacity = '0.6';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update Alpine.js reactive state
            alpineData.isActive = data.is_active;
            
            // Optional: Show success message
            if (data.message) {
                showToast(data.message, 'success');
            }
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating the status', 'error');
    })
    .finally(() => {
        // Re-enable button
        element.disabled = false;
        element.style.opacity = '1';
    });
}

// AJAX delete function for category
function deleteCategory(url, confirmMessage) {
    if (!confirm(confirmMessage)) {
        return false;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Reload categories list
            const searchInput = document.getElementById('categorySearch');
            const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
            loadCategories(currentPage, searchInput ? searchInput.value : '');
        } else {
            showToast(data.message || 'Failed to delete category', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the category', 'error');
    });
    
    return false;
}

// Make loadCategories function global so it can be called from deleteCategory
var loadCategories;

// AJAX search and pagination functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('categorySearch');
    const categoriesContainer = document.getElementById('categoriesContainer');
    const searchSpinner = document.getElementById('searchSpinner');
    const createCategoryForm = document.getElementById('createCategoryForm');
    let searchTimeout;

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadCategories(1, searchInput.value);
        }, 500);
    });

    // Pagination click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="page="]')) {
            e.preventDefault();
            const url = new URL(e.target.closest('a[href*="page="]').href);
            const page = url.searchParams.get('page');
            loadCategories(page, searchInput.value);
        }
    });

    // Load categories via AJAX
    loadCategories = function(page, search) {
        // Show spinner in search box
        searchSpinner.classList.remove('hidden');
        
        // Show loading indicator and hide categories
        const loadingIndicator = document.getElementById('categoriesLoadingIndicator');
        loadingIndicator.classList.remove('hidden');
        categoriesContainer.classList.add('hidden');
        
        const url = new URL('{{ route('vendor.categories.index') }}');
        url.searchParams.append('page', page || 1);
        if (search) {
            url.searchParams.append('search', search);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            categoriesContainer.innerHTML = html;
            
            // Update URL without page reload
            const newUrl = `{{ route('vendor.categories.index') }}?page=${page || 1}${search ? '&search=' + encodeURIComponent(search) : ''}`;
            window.history.pushState({}, '', newUrl);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load categories', 'error');
        })
        .finally(() => {
            // Hide spinner and loading indicator
            searchSpinner.classList.add('hidden');
            const loadingIndicator = document.getElementById('categoriesLoadingIndicator');
            loadingIndicator.classList.add('hidden');
            categoriesContainer.classList.remove('hidden');
        });
    }

    // Handle create category form submission
    if (createCategoryForm) {
        createCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const formErrors = document.getElementById('formErrors');
            const formErrorsContent = document.getElementById('formErrorsContent');
            const nameError = document.getElementById('name_error');
            
            // Reset errors
            formErrors.classList.add('hidden');
            nameError.classList.add('hidden');
            nameError.textContent = '';
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            
            const formData = new FormData(createCategoryForm);
            
            // Explicitly handle checkbox - if not checked, set to 0, if checked, set to 1
            const isActiveCheckbox = document.getElementById('modal_is_active');
            if (!isActiveCheckbox.checked) {
                formData.set('is_active', '0');
            } else {
                formData.set('is_active', '1');
            }
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route('vendor.categories.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    showToast(data.message, 'success');
                    
                    // Reset form
                    createCategoryForm.reset();
                    document.getElementById('modal_is_active').checked = true;
                    
                    // Close modal - Try multiple methods for reliability
                    const modal = document.getElementById('createCategoryModal');
                    if (modal && modal.__x) {
                        // Alpine.js 3.x method
                        modal.__x.$data.open = false;
                    } else if (modal && modal._x_dataStack) {
                        // Alpine.js 2.x method
                        modal._x_dataStack[0].open = false;
                    } else {
                        // Fallback: dispatch event
                        window.dispatchEvent(new CustomEvent('close-create-modal'));
                    }
                    
                    // Reload categories list with small delay for smooth closing animation
                    setTimeout(() => {
                        loadCategories(1, searchInput.value);
                    }, 300);
                } else if (response.status === 422) {
                    // Validation errors
                    if (data.errors) {
                        let errorMessage = '';
                        Object.keys(data.errors).forEach(key => {
                            errorMessage += data.errors[key].join('<br>') + '<br>';
                            
                            // Show error next to name field
                            if (key === 'name') {
                                nameError.textContent = data.errors[key][0];
                                nameError.classList.remove('hidden');
                            }
                        });
                        formErrorsContent.innerHTML = errorMessage;
                        formErrors.classList.remove('hidden');
                    }
                } else {
                    // Other errors
                    formErrorsContent.textContent = data.message || 'Failed to create category';
                    formErrors.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to create category. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtnText.innerHTML = '<i class="fas fa-save mr-2"></i>{{ __('vendor.add_category') }}';
            });
        });
    }
});

// Function to open edit modal and populate with category data
function openEditModal(id, name, icon, isActive, updateUrl) {
    // Populate form fields
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_icon').value = icon || '';
    document.getElementById('edit_is_active').checked = isActive;
    
    // Store update URL for form submission
    document.getElementById('editCategoryForm').dataset.updateUrl = updateUrl;
    
    // Reset errors
    document.getElementById('editFormErrors').classList.add('hidden');
    document.getElementById('edit_name_error').classList.add('hidden');
    
    // Open modal
    window.dispatchEvent(new CustomEvent('open-edit-modal'));
}

// Handle edit category form submission
document.addEventListener('DOMContentLoaded', function() {
    const editCategoryForm = document.getElementById('editCategoryForm');
    
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('editSubmitBtn');
            const submitBtnText = document.getElementById('editSubmitBtnText');
            const formErrors = document.getElementById('editFormErrors');
            const formErrorsContent = document.getElementById('editFormErrorsContent');
            const nameError = document.getElementById('edit_name_error');
            
            // Reset errors
            formErrors.classList.add('hidden');
            nameError.classList.add('hidden');
            nameError.textContent = '';
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            
            const formData = new FormData(e.target);
            
            // Explicitly handle checkbox - if not checked, set to 0, if checked, set to 1
            const isActiveCheckbox = document.getElementById('edit_is_active');
            if (!isActiveCheckbox.checked) {
                formData.set('is_active', '0');
            } else {
                formData.set('is_active', '1');
            }
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const updateUrl = e.target.dataset.updateUrl;
            
            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    showToast(data.message, 'success');
                    
                    // Close modal
                    const modal = document.getElementById('editCategoryModal');
                    if (modal && modal.__x) {
                        modal.__x.$data.open = false;
                    } else if (modal && modal._x_dataStack) {
                        modal._x_dataStack[0].open = false;
                    } else {
                        window.dispatchEvent(new CustomEvent('close-edit-modal'));
                    }
                    
                    // Reload categories list
                    const searchInput = document.getElementById('categorySearch');
                    setTimeout(() => {
                        loadCategories(1, searchInput ? searchInput.value : '');
                    }, 300);
                } else if (response.status === 422) {
                    // Validation errors
                    if (data.errors) {
                        let errorMessage = '';
                        Object.keys(data.errors).forEach(key => {
                            errorMessage += data.errors[key].join('<br>') + '<br>';
                            
                            // Show error next to name field
                            if (key === 'name') {
                                nameError.textContent = data.errors[key][0];
                                nameError.classList.remove('hidden');
                            }
                        });
                        formErrorsContent.innerHTML = errorMessage;
                        formErrors.classList.remove('hidden');
                    }
                } else {
                    // Other errors
                    formErrorsContent.textContent = data.message || 'Failed to update category';
                    formErrors.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to update category. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtnText.innerHTML = '<i class="fas fa-save mr-2"></i>{{ __('vendor.update_category') }}';
            });
        });
    }
});

// Toast notification helper
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-y-0`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateY(-100%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
