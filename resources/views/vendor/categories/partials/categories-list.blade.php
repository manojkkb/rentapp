@if($categories->count() > 0)
    <!-- Desktop View - Table -->
    <div class="hidden md:block overflow-x-auto overflow-y-visible">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.category') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.items') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.status') }}
                    </th>
                  
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors relative">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 mr-3 border border-gray-100 bg-gray-50">
                                @else
                                    <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg mr-3 flex-shrink-0">
                                        <i class="fas fa-tag text-emerald-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $category->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        @if($category->subcategories->count() > 0)
                                            <a href="{{ route('vendor.categories.subcategories', $category) }}" 
                                            class="inline-flex items-center px-2.5 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-semibold rounded-full transition-colors">
                                                <i class="fas fa-folder-tree text-xs mr-1.5"></i>
                                                <span>{{ $category->subcategories->count() }} {{ __('vendor.subcategories') }}</span>
                                                <i class="fas fa-arrow-right text-[10px] ml-1.5"></i>
                                            </a>
                                        @else
                                            <button type="button"
                                                    onclick="openAddSubcategoryModal({{ $category->id }}, @js($category->name))"
                                                    class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-full transition-colors">
                                                <i class="fas fa-plus text-xs mr-1.5"></i>
                                                <span>{{ __('vendor.add_subcategory') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 flex items-center justify-center bg-blue-50 rounded-lg mr-2">
                                    <i class="fas fa-box text-blue-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">{{ $category->items->count() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="inline-block" x-data="{ isActive: {{ $category->is_active ? 'true' : 'false' }} }">
                                <button @click="toggleStatus('{{ route('vendor.categories.toggle', $category) }}', $el, $data)" 
                                        type="button"
                                        class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" 
                                        :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                        :title="isActive ? '{{ __('vendor.click_to_deactivate') }}' : '{{ __('vendor.click_to_activate') }}'">
                                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" 
                                          :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                                <span class="ml-2 text-xs font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
                            </div>
                        </td>
                       
                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block" x-data="{ dropdownOpen: false }">
                                <button @click="dropdownOpen = !dropdownOpen" 
                                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        type="button"
                                        x-ref="dropdownButton">
                                    <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                                </button>
                                
                                <div x-show="dropdownOpen" 
                                     @click.away="dropdownOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="fixed w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1"
                                     style="display: none; z-index: 9999;"
                                     x-init="$watch('dropdownOpen', value => {
                                         if(value) {
                                             let rect = $refs.dropdownButton.getBoundingClientRect();
                                             $el.style.top = rect.bottom + 5 + 'px';
                                             $el.style.left = (rect.right - 192) + 'px';
                                         }
                                     })">
                                    <a href="{{ route('vendor.categories.edit', $category) }}" 
                                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-eye w-5 text-gray-400 mr-3"></i>
                                        {{ __('vendor.view') }}
                                    </a>
                                    <button type="button" 
                                            onclick="openEditModal({{ $category->id }}, @js($category->name), @js($category->icon), {{ $category->is_active ? 'true' : 'false' }}, @js(route('vendor.categories.update', $category)), @js($category->image_url))"
                                            class="w-full text-left block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-edit w-5 text-blue-500 mr-3"></i>
                                        {{ __('vendor.edit') }}
                                    </button>
                                    <button type="button"
                                            onclick="openAddSubcategoryModal({{ $category->id }}, @js($category->name))"
                                            class="w-full text-left block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-plus w-5 text-emerald-600 mr-3"></i>
                                        {{ __('vendor.add_subcategory') }}
                                    </button>
                                    <button type="button" 
                                            onclick="deleteCategory('{{ route('vendor.categories.destroy', $category) }}', '{{ __('vendor.confirm_delete') }}')"
                                            class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash w-5 mr-3"></i>
                                        {{ __('vendor.delete') }}
                                    </button>
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
        @foreach($categories as $category)
            <div class="p-4">
                <!-- Main Category Card -->
                <div class="space-y-3">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-gray-100 bg-gray-50">
                            @else
                                <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 rounded-xl flex-shrink-0">
                                    <i class="fas fa-tag text-emerald-600 text-lg"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 truncate">
                                    {{ $category->name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-box text-xs mr-1"></i>
                                        {{ $category->items->count() }} items
                                    </p>
                                    <!-- Manage Subcategories Button (Mobile) -->
                                    @if($category->subcategories->count() > 0)
                                        <a href="{{ route('vendor.categories.subcategories', $category) }}" 
                                           class="inline-flex items-center px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-semibold rounded-full active:bg-purple-200 transition-all">
                                            <i class="fas fa-folder-tree text-[8px] mr-1"></i>
                                            <span>{{ $category->subcategories->count() }} {{ __('vendor.subcategories') }}</span>
                                            <i class="fas fa-arrow-right text-[8px] ml-1"></i>
                                        </a>
                                    @else
                                        <button type="button"
                                                onclick="openAddSubcategoryModal({{ $category->id }}, @js($category->name))"
                                                class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-semibold rounded-full active:bg-gray-200 transition-all">
                                            <i class="fas fa-plus text-[8px] mr-1"></i>
                                            <span>{{ __('vendor.add_subcategory') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- 3-Dot Menu -->
                        <div class="relative ml-2 flex-shrink-0" x-data="{ mobileDropdownOpen: false }">
                            <button @click="mobileDropdownOpen = !mobileDropdownOpen" 
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                                    type="button">
                                <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                            </button>
                            
                            <div x-show="mobileDropdownOpen" 
                                 @click.away="mobileDropdownOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                                 style="display: none;">
                                <a href="{{ route('vendor.categories.edit', $category) }}" 
                                   class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-eye w-5 text-gray-400 mr-3"></i>
                                    {{ __('vendor.view') }}
                                </a>
                                <button type="button" 
                                        onclick="openEditModal({{ $category->id }}, @js($category->name), @js($category->icon), {{ $category->is_active ? 'true' : 'false' }}, @js(route('vendor.categories.update', $category)), @js($category->image_url))"
                                        class="w-full text-left block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-edit w-5 text-blue-500 mr-3"></i>
                                    {{ __('vendor.edit') }}
                                </button>
                                <button type="button"
                                        onclick="openAddSubcategoryModal({{ $category->id }}, @js($category->name))"
                                        class="w-full text-left block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-plus w-5 text-emerald-600 mr-3"></i>
                                    {{ __('vendor.add_subcategory') }}
                                </button>
                                <button type="button" 
                                        onclick="deleteCategory('{{ route('vendor.categories.destroy', $category) }}', '{{ __('vendor.confirm_delete') }}')"
                                        class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                    <i class="fas fa-trash w-5 mr-3"></i>
                                    {{ __('vendor.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Tags -->
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                        <!-- Status Toggle -->
                        <div class="inline-block" x-data="{ isActive: {{ $category->is_active ? 'true' : 'false' }} }">
                            <button @click="toggleStatus('{{ route('vendor.categories.toggle', $category) }}', $el, $data)" 
                                    type="button"
                                    class="relative inline-flex items-center h-7 rounded-full w-12 transition-colors focus:outline-none active:ring-2 active:ring-offset-2 active:ring-emerald-500" 
                                    :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                    :title="isActive ? '{{ __('vendor.tap_to_deactivate') }}' : '{{ __('vendor.tap_to_activate') }}'">
                                <span class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform shadow-md" 
                                      :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                            <span class="ml-2 text-xs font-semibold" :class="isActive ? 'text-emerald-700' : 'text-gray-600'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200" id="pagination-container">
        {{ $categories->links() }}
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <i class="fas fa-tags text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_categories_found') }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ __('vendor.start_by_creating_category') }}</p>
        <button type="button"
                @click="$dispatch('open-create-modal')" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>
            {{ __('vendor.add_category') }}
        </button>
    </div>
@endif
