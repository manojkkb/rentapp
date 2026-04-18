@extends('vendor.layouts.app')

@section('title', 'Subcategories - ' . $category->name . ' - Rentkia')
@section('page-title', 'Subcategories')

@section('content')
<div class="space-y-6" x-data="subcategoryModal()">
    
    <!-- Back Button -->
    <div>
        <a href="{{ route('vendor.categories.index') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 bg-white hover:bg-emerald-50 rounded-lg border border-gray-200 transition-all active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">Back to Categories</span>
            <span class="sm:hidden">Back</span>
        </a>
    </div>

    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-12 h-12 flex items-center justify-center bg-emerald-50 rounded-xl">
                    <i class="fas fa-folder-tree text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-layer-group text-emerald-600 mr-1"></i>
                        <span class="font-medium">{{ __('vendor.subcategories_count', ['count' => $subcategories->total()]) }}</span>
                    </p>
                </div>
            </div>
        </div>
        <button type="button"
                @click="openModal()"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>
            {{ __('vendor.add_subcategory') }}
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

    <!-- Subcategories List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($subcategories->count() > 0)
            <!-- Desktop View - Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Subcategory
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subcategories as $subcategory)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-50 rounded-lg mr-3">
                                            <i class="fas fa-tag text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $subcategory->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <i class="fas fa-box text-xs mr-1"></i>
                                                {{ $subcategory->items->count() }} items
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-block" x-data="{ isActive: {{ $subcategory->is_active ? 'true' : 'false' }} }">
                                        <button @click="toggleStatus('{{ route('vendor.categories.toggle', $subcategory) }}', $el, $data)" 
                                                type="button"
                                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" 
                                                :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                                :title="isActive ? 'Click to deactivate' : 'Click to activate'">
                                            <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" 
                                                  :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                        </button>
                                        <span class="ml-2 text-xs font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                        <button @click.stop="open = !open" 
                                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                                type="button">
                                            <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                                        </button>
                                        
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                            <a href="{{ route('vendor.categories.edit', $subcategory) }}" 
                                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-eye w-5 text-gray-400"></i>
                                                <span class="ml-3">View Details</span>
                                            </a>
                                            <a href="{{ route('vendor.categories.edit', $subcategory) }}" 
                                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-edit w-5 text-blue-500"></i>
                                                <span class="ml-3">Edit</span>
                                            </a>
                                            <form action="{{ route('vendor.categories.destroy', $subcategory) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                    <i class="fas fa-trash w-5"></i>
                                                    <span class="ml-3">Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View - Cards -->
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($subcategories as $subcategory)
                    <div class="p-4">
                        <!-- Subcategory Card -->
                        <div class="space-y-3">
                            <!-- Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="w-12 h-12 flex items-center justify-center bg-emerald-50 rounded-xl flex-shrink-0">
                                        <i class="fas fa-tag text-emerald-600 text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-semibold text-gray-900 truncate">
                                            {{ $subcategory->name }}
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <i class="fas fa-box text-xs mr-1"></i>
                                            {{ $subcategory->items->count() }} items
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- 3-Dot Menu -->
                                <div class="relative ml-2 flex-shrink-0" x-data="{ menuOpen: false }" @click.away="menuOpen = false">
                                    <button @click.stop="menuOpen = !menuOpen" 
                                            class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                                            type="button">
                                        <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                                    </button>
                                    
                                    <div x-show="menuOpen" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50">
                                        <a href="{{ route('vendor.categories.edit', $subcategory) }}" 
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                            <i class="fas fa-eye w-5 text-gray-400"></i>
                                            <span class="ml-3 font-medium">View Details</span>
                                        </a>
                                        <a href="{{ route('vendor.categories.edit', $subcategory) }}" 
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                            <i class="fas fa-edit w-5 text-blue-500"></i>
                                            <span class="ml-3 font-medium">Edit Subcategory</span>
                                        </a>
                                        <form action="{{ route('vendor.categories.destroy', $subcategory) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                                <i class="fas fa-trash w-5"></i>
                                                <span class="ml-3 font-medium">Delete Subcategory</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Tags -->
                            <div class="flex items-center space-x-2 flex-wrap gap-2">
                                <!-- Status Toggle -->
                                <div class="inline-block" x-data="{ isActive: {{ $subcategory->is_active ? 'true' : 'false' }} }">
                                    <button @click="toggleStatus('{{ route('vendor.categories.toggle', $subcategory) }}', $el, $data)" 
                                            type="button"
                                            class="relative inline-flex items-center h-7 rounded-full w-12 transition-colors focus:outline-none active:ring-2 active:ring-offset-2 active:ring-emerald-500" 
                                            :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                            :title="isActive ? 'Tap to deactivate' : 'Tap to activate'">
                                        <span class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform shadow-md" 
                                              :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                    <span class="ml-2 text-xs font-semibold" :class="isActive ? 'text-emerald-700' : 'text-gray-600'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $subcategories->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-layer-group text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Subcategories Yet</h3>
                <p class="text-sm text-gray-500 mb-6">Start organizing items under "{{ $category->name }}" by creating subcategories</p>
                <button type="button"
                        @click="openModal()"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('vendor.add_subcategory') }}
                </button>
            </div>
        @endif
    </div>

    <!-- Add subcategory modal -->
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center p-4"
         @keydown.escape.window="open && closeModal()"
         role="dialog"
         aria-modal="true"
         aria-labelledby="add-subcategory-title">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px]" @click="closeModal()" aria-hidden="true"></div>
        <div class="relative w-full max-w-lg bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
             @click.stop>
            <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-emerald-100/80 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="add-subcategory-title" class="text-lg font-bold text-gray-900">{{ __('vendor.add_subcategory') }}</h2>
                    <p class="text-sm text-gray-600 mt-0.5">
                        <i class="fas fa-layer-group text-emerald-600 mr-1"></i>
                        {{ __('vendor.category') }}: <span class="font-semibold text-gray-900">{{ $category->name }}</span>
                    </p>
                </div>
                <button type="button"
                        @click="closeModal()"
                        class="p-2 rounded-lg text-gray-500 hover:bg-white/80 hover:text-gray-800 transition-colors shrink-0"
                        aria-label="{{ __('vendor.cancel') }}">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form class="p-5 space-y-4" @submit.prevent="submitForm()">
                <div>
                    <label for="modal_sub_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="modal_sub_name"
                           x-model="name"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           :class="errors.name ? 'border-red-500' : 'border-gray-300'"
                           placeholder="{{ __('vendor.category_name_placeholder') }}"
                           autocomplete="off"
                           required>
                    <template x-if="errors.name">
                        <p class="mt-1.5 text-sm text-red-600" x-text="errors.name[0]"></p>
                    </template>
                </div>
                <div>
                    <label for="modal_sub_icon" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Icon <span class="text-gray-500 font-normal text-xs">(Font Awesome)</span>
                    </label>
                    <input type="text"
                           id="modal_sub_icon"
                           x-model="icon"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           :class="errors.icon ? 'border-red-500' : 'border-gray-300'"
                           placeholder="fa-tag">
                    <template x-if="errors.icon">
                        <p class="mt-1.5 text-sm text-red-600" x-text="errors.icon[0]"></p>
                    </template>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-start cursor-pointer gap-3">
                        <input type="checkbox"
                               x-model="isActive"
                               class="mt-1 w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>
                            <span class="text-sm font-medium text-gray-900">{{ __('vendor.active') }}</span>
                            <span class="block text-xs text-gray-600 mt-0.5">{{ __('vendor.category') }} {{ __('vendor.available') }}</span>
                        </span>
                    </label>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                    <button type="button"
                            @click="closeModal()"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors disabled:opacity-60 disabled:pointer-events-none"
                            :disabled="submitting">
                        <span class="inline-flex items-center" x-show="submitting" x-cloak>
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Saving…
                        </span>
                        <span class="inline-flex items-center" x-show="!submitting">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('vendor.add_category') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function subcategoryModal() {
    return {
        open: false,
        submitting: false,
        errors: {},
        name: '',
        icon: 'fa-folder',
        isActive: true,
        parentId: {{ (int) $category->id }},
        storeUrl: @json(route('vendor.categories.store')),
        resetForm() {
            this.errors = {};
            this.name = '';
            this.icon = 'fa-folder';
            this.isActive = true;
        },
        openModal() {
            this.resetForm();
            this.open = true;
        },
        closeModal() {
            this.open = false;
        },
        async submitForm() {
            this.errors = {};
            this.submitting = true;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const fd = new FormData();
            fd.append('_token', token);
            fd.append('name', this.name.trim());
            fd.append('parent_id', String(this.parentId));
            fd.append('icon', this.icon || '');
            fd.append('is_active', this.isActive ? '1' : '0');
            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: fd,
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    window.location.reload();
                    return;
                }
                if (res.status === 422 && data.errors) {
                    this.errors = data.errors;
                    return;
                }
                alert(data.message || 'Could not create subcategory.');
            } catch (e) {
                console.error(e);
                alert('A network error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        },
    };
}

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
                // Create temporary toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-y-0';
                toast.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-check-circle"></i>
                        <span>${data.message}</span>
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
        } else {
            // Show error message
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${data.message || 'Failed to update status'}</span>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>An error occurred while updating the status</span>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    })
    .finally(() => {
        // Re-enable button
        element.disabled = false;
        element.style.opacity = '1';
    });
}
</script>
@endsection
