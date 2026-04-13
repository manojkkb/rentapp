@if($staff->count() > 0)
    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.user') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.contact') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.role') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.status') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.last_login') }}
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ __('vendor.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($staff as $member)
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- User Info -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $member->name }}</p>
                                @if($member->pivot->is_owner)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                        <i class="fas fa-crown mr-1"></i>{{ __('vendor.owner') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <!-- Contact -->
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ $member->mobile }}</p>
                        @if($member->email && !str_contains($member->email, '@staff.temp') && !str_contains($member->email, '@rentapp.temp') && !str_contains($member->email, '@rentkia.temp'))
                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                        @endif
                    </td>

                    <!-- Role -->
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full
                            @if($member->pivot->role == 'manager') bg-purple-100 text-purple-800
                            @elseif($member->pivot->role == 'cashier') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            <i class="fas fa-user-tag mr-1.5"></i>
                            {{ ucfirst($member->pivot->role ?? 'staff') }}
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4">
                        @if(!$member->pivot->is_owner)
                            <div class="inline-block" x-data="{ isActive: {{ $member->pivot->is_active ? 'true' : 'false' }} }">
                                <form action="{{ route('vendor.staff.toggle', $member->pivot->id) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                                    @csrf
                                    <button type="submit"
                                            class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" 
                                            :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                            :title="isActive ? '{{ __('vendor.click_to_deactivate') }}' : '{{ __('vendor.click_to_activate') }}'">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" 
                                              :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                </form>
                                <span class="ml-2 text-xs font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                <i class="fas fa-crown mr-1"></i>
                                {{ __('vendor.owner') }}
                            </span>
                        @endif
                    </td>

                    <!-- Last Login -->
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($member->pivot->last_login_at)
                            {{ \Carbon\Carbon::parse($member->pivot->last_login_at)->diffForHumans() }}
                        @else
                            <span class="text-gray-400">{{ __('vendor.never') }}</span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 text-right">
                        @if(!$member->pivot->is_owner)
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
                                    <a href="{{ route('vendor.staff.edit', $member->pivot->id) }}" 
                                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-edit w-5 text-blue-500 mr-3"></i>
                                        {{ __('vendor.edit') }}
                                    </a>
                                    <form action="{{ route('vendor.staff.destroy', $member->pivot->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('{{ __('vendor.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash-alt w-5 mr-3"></i>
                                            {{ __('vendor.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">N/A</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden divide-y divide-gray-200">
        @foreach($staff as $member)
        <div class="p-4">
            <!-- User Info -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center flex-1 min-w-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $member->name }}</p>
                        <p class="text-xs text-gray-600">{{ $member->mobile }}</p>
                    </div>
                </div>
                
                @if(!$member->pivot->is_owner)
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
                            <a href="{{ route('vendor.staff.edit', $member->pivot->id) }}" 
                               class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                <i class="fas fa-edit w-5 text-blue-500 mr-3"></i>
                                {{ __('vendor.edit') }}
                            </a>
                            <form action="{{ route('vendor.staff.destroy', $member->pivot->id) }}" 
                                  method="POST"
                                  onsubmit="return confirm('{{ __('vendor.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                    <i class="fas fa-trash-alt w-5 mr-3"></i>
                                    {{ __('vendor.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Metadata -->
            <div class="flex items-center space-x-4 mb-3 text-xs">
                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded">
                    <i class="fas fa-user-tag mr-1"></i>
                    {{ ucfirst($member->pivot->role ?? 'staff') }}
                </span>
                @if($member->pivot->is_owner)
                    <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded">
                        <i class="fas fa-crown mr-1"></i>{{ __('vendor.owner') }}
                    </span>
                @endif
            </div>

            @if($member->pivot->last_login_at)
                <p class="text-xs text-gray-500 mb-3">
                    <i class="fas fa-clock mr-1"></i>
                    {{ __('vendor.last_login') }}: {{ \Carbon\Carbon::parse($member->pivot->last_login_at)->diffForHumans() }}
                </p>
            @endif

            <!-- Status Toggle -->
            @if(!$member->pivot->is_owner)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">{{ __('vendor.status') }}</span>
                    <div class="inline-block" x-data="{ isActive: {{ $member->pivot->is_active ? 'true' : 'false' }} }">
                        <form action="{{ route('vendor.staff.toggle', $member->pivot->id) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                            @csrf
                            <button type="submit"
                                    class="relative inline-flex items-center h-7 rounded-full w-12 transition-colors focus:outline-none active:ring-2 active:ring-offset-2 active:ring-emerald-500" 
                                    :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                    :title="isActive ? '{{ __('vendor.tap_to_deactivate') }}' : '{{ __('vendor.tap_to_activate') }}'">
                                <span class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform shadow-md" 
                                      :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </form>
                        <span class="ml-2 text-xs font-semibold" :class="isActive ? 'text-emerald-700' : 'text-gray-600'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
                    </div>
                </div>
            @else
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                    <i class="fas fa-crown mr-1"></i>
                    {{ __('vendor.owner') }}
                </span>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $staff->links() }}
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-users text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Staff Members Yet</h3>
        <p class="text-gray-600 mb-6">Start building your team by adding staff members</p>
        <button type="button"
                @click="$dispatch('open-create-staff-modal')" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
            <i class="fas fa-plus mr-2"></i>
            Add First Staff Member
        </button>
    </div>
@endif
