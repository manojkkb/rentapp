@extends('vendor.layouts.app')

@section('title', __('vendor.staff_management'))
@section('page-title', __('vendor.staff_management'))

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.staff_members') }}</h2>
        <p class="text-sm text-gray-600 mt-1">{{ __('vendor.manage_team_members') }}</p>
    </div>
    <button type="button"
            @click="$dispatch('open-create-staff-modal')" 
            class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.add_staff_member') }}
    </button>
</div>

<!-- Search Bar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
        <input type="text" 
               id="staffSearch" 
               class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" 
               placeholder="{{ __('vendor.search') }} staff by name, mobile, or role...">
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <div id="searchSpinner" class="hidden">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Shimmer Loading Indicator -->
<div id="staffLoadingIndicator" class="hidden">
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
        <!-- Desktop Shimmer -->
        <div class="hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></th>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></th>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                        <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                                <div class="ml-3">
                                    <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></td>
                        <td class="px-6 py-4"><div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div></td>
                        <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                        <td class="px-6 py-4"><div class="h-3 bg-gray-200 rounded shimmer w-24"></div></td>
                        <td class="px-6 py-4"><div class="h-8 w-8 bg-gray-200 rounded shimmer"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <!-- Mobile Shimmer -->
        <div class="md:hidden divide-y divide-gray-200">
            @for($i = 0; $i < 3; $i++)
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center flex-1">
                        <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                        <div class="ml-3">
                            <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                        </div>
                    </div>
                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                </div>
                <div class="h-6 w-20 bg-gray-200 rounded shimmer mb-3"></div>
                <div class="h-12 w-full bg-gray-200 rounded shimmer"></div>
            </div>
            @endfor
        </div>
    </div>
</div>

<!-- Staff List -->
<div id="staffContainer" class="bg-white rounded-xl shadow-sm border border-gray-200">
    @include('vendor.staff.partials.staff-list', ['staff' => $staff])
</div>

<!-- Create Staff Modal -->
<div x-data="{ showCreateModal: false }" 
     @open-create-staff-modal.window="showCreateModal = true"
     @close-create-staff-modal.window="showCreateModal = false"
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
            
            <form id="createStaffForm" method="POST" action="{{ route('vendor.staff.store') }}">
                @csrf
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                                <i class="fas fa-user-plus text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Add New Staff Member</h3>
                                <p class="text-sm text-gray-600">Create a new staff account</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="modal_staff_name" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="Enter full name"
                               required>
                    </div>

                    <!-- Mobile -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="mobile" 
                               id="modal_staff_mobile" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="10-digit mobile number"
                               maxlength="10"
                               pattern="[0-9]{10}"
                               required>
                        <p class="mt-1 text-xs text-gray-500">This will be used for login</p>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address <span class="text-gray-400">(Optional)</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="modal_staff_email" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="email@example.com">
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" 
                                id="modal_staff_role" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                required>
                            <option value="">Select role</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="modal_staff_is_active"
                                   value="1"
                                   checked
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Active (Can login immediately)</span>
                        </label>
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
                            id="createStaffSubmitBtn"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        <span id="createStaffSubmitBtnText">Add Staff Member</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Make loadStaff function global
var loadStaff;

document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('staffLoadingIndicator');
    const container = document.getElementById('staffContainer');
    const searchInput = document.getElementById('staffSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    let searchTimeout;

    // Show shimmer briefly on initial load
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
                loadStaff(1, searchInput.value);
            }, 500);
        });
    }

    // Pagination click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="page="]')) {
            e.preventDefault();
            const url = new URL(e.target.closest('a[href*="page="]').href);
            const page = url.searchParams.get('page');
            loadStaff(page, searchInput ? searchInput.value : '');
        }
    });

    // Load staff via AJAX
    loadStaff = function(page, search) {
        // Show spinner in search box
        if (searchSpinner) {
            searchSpinner.classList.remove('hidden');
        }
        
        // Show loading indicator and hide staff
        if (loader && container) {
            loader.classList.remove('hidden');
            container.classList.add('hidden');
        }
        
        const url = new URL('{{ route('vendor.staff.index') }}');
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
            const newUrl = `{{ route('vendor.staff.index') }}?page=${page || 1}${search ? '&search=' + encodeURIComponent(search) : ''}`;
            window.history.pushState({}, '', newUrl);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load staff', 'error');
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

    // Handle create staff form submission
    const createStaffForm = document.getElementById('createStaffForm');
    if (createStaffForm) {
        createStaffForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('createStaffSubmitBtn');
            const submitBtnText = document.getElementById('createStaffSubmitBtnText');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            
            const formData = new FormData(createStaffForm);
            
            fetch('{{ route('vendor.staff.store') }}', {
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
                    showToast(data.message || 'Staff member added successfully!', 'success');
                    
                    // Reset form
                    createStaffForm.reset();
                    document.getElementById('modal_staff_is_active').checked = true;
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-create-staff-modal'));
                    
                    // Reload staff list
                    setTimeout(() => {
                        loadStaff(1, searchInput ? searchInput.value : '');
                    }, 300);
                } else {
                    showToast(data.message || 'Error adding staff member', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding staff member', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtnText.innerHTML = '<i class="fas fa-plus mr-2"></i>Add Staff Member';
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
