@extends('vendor.layouts.app')

@section('title', __('vendor.customers_management'))
@section('page-title', __('vendor.customers'))

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.customers') }}</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-users text-emerald-600 mr-1"></i>
                    <span class="font-medium">{{ __('vendor.total_customers_count', ['count' => $customers->total()]) }}</span>
                </p>
            </div>
        </div>
    </div>
    <button type="button"
            @click="$dispatch('open-create-customer-modal')" 
            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.add_customer') }}
    </button>
</div>

<!-- Search Bar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
        <input type="text" 
               id="customerSearch" 
               class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" 
               placeholder="{{ __('vendor.search') }} {{ __('vendor.customers') }}...">
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <div id="searchSpinner" class="hidden">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Messages -->
@if (session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
            <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
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

<!-- Shimmer Loading Indicator -->
<div id="customersLoadingIndicator" class="hidden">
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
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
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
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-12 mr-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-16 mr-2"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="flex space-x-2">
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-12 mr-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-16 mr-2"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="flex space-x-2">
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-12 mr-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="flex items-start">
                        <div class="h-4 bg-gray-200 rounded shimmer w-16 mr-2"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-1"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
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

<!-- Customers List -->
<div id="customersContainer" class="bg-white rounded-xl shadow-sm border border-gray-200">
    @include('vendor.customers.partials.customers-list', ['customers' => $customers])
</div>

<!-- Create Customer Modal -->
<div x-data="{ showCreateModal: false }" 
     @open-create-customer-modal.window="showCreateModal = true"
     @close-create-customer-modal.window="showCreateModal = false"
     x-show="showCreateModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @click.self="showCreateModal = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <!-- Background overlay -->
        <div x-show="showCreateModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-black/50" 
             @click="showCreateModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showCreateModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10"
             @click.stop>
            
            <form id="createCustomerForm" method="POST" action="{{ route('vendor.customers.store') }}">
                @csrf
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                                <i class="fas fa-user-plus text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Add New Customer</h3>
                                <p class="text-sm text-gray-600">Fill in customer details</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Customer Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="modal_customer_name" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="Enter customer's full name"
                               required>
                    </div>

                    <!-- Mobile Number -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="mobile" 
                                   id="modal_customer_mobile" 
                                   class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                   placeholder="10-digit mobile number"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Address <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <textarea 
                            name="address" 
                            id="modal_customer_address" 
                            rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                            placeholder="Enter customer's address"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" 
                            @click="showCreateModal = false"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="createCustomerSubmitBtn"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                        <i class="fas fa-save mr-2"></i>
                        <span id="createCustomerSubmitBtnText">Add Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Make loadCustomers function global
var loadCustomers;

document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('customersLoadingIndicator');
    const container = document.getElementById('customersContainer');
    const searchInput = document.getElementById('customerSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    let searchTimeout;

    // Show shimmer briefly for better UX on initial load
    if (loader && container) {
        loader.classList.remove('hidden');
        container.classList.add('hidden');
        
        setTimeout(() => {
            loader.classList.add('hidden');
            container.classList.remove('hidden');
        }, 300);
    }

    // Search functionality with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadCustomers(1, searchInput.value);
            }, 500);
        });
    }

    // Pagination click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="page="]')) {
            e.preventDefault();
            const url = new URL(e.target.closest('a[href*="page="]').href);
            const page = url.searchParams.get('page');
            loadCustomers(page, searchInput ? searchInput.value : '');
        }
    });

    // Load customers via AJAX
    loadCustomers = function(page, search) {
        // Show spinner in search box
        if (searchSpinner) {
            searchSpinner.classList.remove('hidden');
        }
        
        // Show loading indicator and hide customers
        if (loader && container) {
            loader.classList.remove('hidden');
            container.classList.add('hidden');
        }
        
        const url = new URL('{{ route('vendor.customers.index') }}');
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
            if (container) {
                container.innerHTML = html;
            }
            
            // Update URL without page reload
            const newUrl = `{{ route('vendor.customers.index') }}?page=${page || 1}${search ? '&search=' + encodeURIComponent(search) : ''}`;
            window.history.pushState({}, '', newUrl);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load customers', 'error');
        })
        .finally(() => {
            // Hide spinner and loading indicator
            if (searchSpinner) {
                searchSpinner.classList.add('hidden');
            }
            if (loader && container) {
                loader.classList.add('hidden');
                container.classList.remove('hidden');
            }
        });
    };

    // Handle create customer form submission
    const createCustomerForm = document.getElementById('createCustomerForm');
    if (createCustomerForm) {
        createCustomerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('createCustomerSubmitBtn');
            const submitBtnText = document.getElementById('createCustomerSubmitBtnText');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            
            const formData = new FormData(createCustomerForm);
            
            fetch('{{ route('vendor.customers.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Customer created successfully!', 'success');
                    
                    // Reset form
                    createCustomerForm.reset();
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-create-customer-modal'));
                    
                    // Reload customers list with small delay
                    setTimeout(() => {
                        loadCustomers(1, searchInput ? searchInput.value : '');
                    }, 300);
                } else {
                    showToast(data.message || 'Error creating customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error creating customer', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtnText.innerHTML = '<i class="fas fa-save mr-2"></i>Add Customer';
            });
        });
    }
});

// Toast notification function
function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50`;
    toast.innerHTML = `
        <i class="fas ${icon} text-2xl"></i>
        <div>
            <p class="font-medium">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-100">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endsection
