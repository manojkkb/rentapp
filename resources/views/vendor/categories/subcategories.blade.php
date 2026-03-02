@extends('vendor.layouts.app')

@section('title', 'Subcategories - ' . $category->name . ' - RentApp')
@section('page-title', 'Subcategories')

@section('content')
<div class="space-y-6">
    
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
                        <span class="font-medium">{{ $subcategories->total() }}</span> subcategories
                    </p>
                </div>
            </div>
        </div>
        <a href="{{ route('vendor.categories.create', ['parent_id' => $category->id]) }}" 
           class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>
            Add<span class="hidden sm:inline ml-1">Subcategory</span>
        </a>
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
                <a href="{{ route('vendor.categories.create', ['parent_id' => $category->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Your First Subcategory
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

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
