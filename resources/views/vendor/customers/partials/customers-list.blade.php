@if($customers->count() > 0)
    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.name') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.mobile') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.address') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.registered') }}
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($customers as $customer)
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- Name -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold text-sm">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">Added {{ $customer->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </td>

                    <!-- Mobile -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <i class="fas fa-phone text-emerald-500 text-xs mr-2"></i>
                            <span class="text-sm text-gray-900">{{ $customer->mobile }}</span>
                        </div>
                    </td>

                    <!-- Address -->
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 max-w-xs truncate">
                            {{ $customer->address ?? 'N/A' }}
                        </p>
                    </td>

                    <!-- Registered Status -->
                    <td class="px-6 py-4">
                        @if($customer->user_id)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                                <i class="fas fa-check-circle text-xs mr-1"></i>
                                Yes
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">
                                <i class="fas fa-times-circle text-xs mr-1"></i>
                                No
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
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
                                <a href="{{ route('vendor.customers.edit', $customer->id) }}" 
                                   class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                    Edit
                                </a>
                                <form action="{{ route('vendor.customers.destroy', $customer->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash w-5 mr-3"></i>
                                        Delete
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

    <!-- Mobile Cards -->
    <div class="md:hidden divide-y divide-gray-200">
        @foreach($customers as $customer)
        <div class="p-4">
            <!-- Customer Card -->
            <div class="space-y-3">
                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-lg">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 truncate">
                                {{ $customer->name }}
                            </h3>
                            <p class="text-xs text-gray-500 flex items-center mt-0.5">
                                <i class="fas fa-phone text-emerald-500 text-xs mr-1.5"></i>
                                {{ $customer->mobile }}
                            </p>
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
                            <a href="{{ route('vendor.customers.edit', $customer->id) }}" 
                               class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                Edit Customer
                            </a>
                            <form action="{{ route('vendor.customers.destroy', $customer->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                    <i class="fas fa-trash w-5 mr-3"></i>
                                    Delete Customer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                @if($customer->address)
                <div class="bg-gray-50 rounded-lg px-3 py-2">
                    <p class="text-xs font-medium text-gray-500 mb-1">Address</p>
                    <p class="text-sm text-gray-900">{{ $customer->address }}</p>
                </div>
                @endif

                <!-- Footer Info -->
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>
                        <i class="fas fa-clock mr-1"></i>
                        Added {{ $customer->created_at->diffForHumans() }}
                    </span>
                    @if($customer->user_id)
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                            <i class="fas fa-check-circle text-xs mr-1"></i>
                            Registered
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <i class="fas fa-user-friends text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Customers Yet</h3>
        <p class="text-sm text-gray-500 mb-6">Start adding customers to your database</p>
        <button type="button"
                @click="$dispatch('open-create-customer-modal')" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Add Your First Customer
        </button>
    </div>
@endif
