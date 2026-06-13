@php
    $roleLabel = function ($vendorUser) {
        if ($vendorUser->is_owner) {
            return __('vendor.owner');
        }
        if ($vendorUser->vendorRole) {
            return $vendorUser->vendorRole->name;
        }

        return ucfirst($vendorUser->role ?? __('vendor.staff'));
    };
@endphp

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
                @foreach($staff as $vendorUser)
                @php $member = $vendorUser->user; @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="ml-3">
                                <a href="{{ route('vendor.staff.show', $vendorUser) }}"
                                   @if($livewireList ?? false) wire:navigate @endif
                                   class="text-sm font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">
                                    {{ $member->name }}
                                </a>
                                @if($vendorUser->is_owner)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                        <i class="fas fa-crown mr-1"></i>{{ __('vendor.owner') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ $member->mobile }}</p>
                        @if($member->email && !str_contains($member->email, '@staff.temp') && !str_contains($member->email, '@rentapp.temp') && !str_contains($member->email, '@rentkia.temp'))
                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-800">
                            <i class="fas fa-user-tag mr-1.5"></i>
                            {{ $roleLabel($vendorUser) }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        @if(!$vendorUser->is_owner)
                            @if($livewireList ?? false)
                                <div class="inline-block" wire:key="staff-toggle-{{ $vendorUser->id }}">
                                    <button type="button"
                                            wire:click="toggleStatus({{ $vendorUser->id }})"
                                            wire:loading.attr="disabled"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 {{ $vendorUser->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                            title="{{ $vendorUser->is_active ? __('vendor.click_to_deactivate') : __('vendor.click_to_activate') }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $vendorUser->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <span class="ml-2 text-xs font-medium {{ $vendorUser->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                                        {{ $vendorUser->is_active ? __('vendor.active') : __('vendor.inactive') }}
                                    </span>
                                </div>
                            @else
                            <div class="inline-block" x-data="{ isActive: {{ $vendorUser->is_active ? 'true' : 'false' }} }">
                                <form action="{{ route('vendor.staff.toggle', $vendorUser) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
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
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                <i class="fas fa-crown mr-1"></i>
                                {{ __('vendor.owner') }}
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($vendorUser->last_login_at)
                            {{ \Carbon\Carbon::parse($vendorUser->last_login_at)->diffForHumans() }}
                        @else
                            <span class="text-gray-400">{{ __('vendor.never') }}</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right">
                        @if(!$vendorUser->is_owner)
                            <div class="relative inline-block" x-data="{ dropdownOpen: false }">
                                <button @click="dropdownOpen = !dropdownOpen"
                                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        type="button"
                                        x-ref="dropdownButton">
                                    <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                                </button>

                                <div x-show="dropdownOpen"
                                     @click.away="dropdownOpen = false"
                                     x-transition
                                     class="fixed w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1"
                                     style="display: none; z-index: 9999;"
                                     x-init="$watch('dropdownOpen', value => {
                                         if(value) {
                                             let rect = $refs.dropdownButton.getBoundingClientRect();
                                             $el.style.top = rect.bottom + 5 + 'px';
                                             $el.style.left = (rect.right - 192) + 'px';
                                         }
                                     })">
                                    <a href="{{ route('vendor.staff.show', $vendorUser) }}"
                                       @if($livewireList ?? false) wire:navigate @endif
                                       class="block px-4 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                                        <i class="fas fa-eye w-5 text-gray-400 mr-3"></i>
                                        {{ __('vendor.view_staff') }}
                                    </a>
                                    <a href="{{ route('vendor.staff.edit', $vendorUser) }}"
                                       @if($livewireList ?? false) wire:navigate @endif
                                       class="block px-4 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                                        <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                        {{ __('vendor.edit') }}
                                    </a>
                                    @if($livewireList ?? false)
                                        <button type="button"
                                                wire:click="deleteStaff({{ $vendorUser->id }})"
                                                wire:confirm="{{ __('vendor.confirm_delete') }}"
                                                class="block w-full px-4 py-2.5 text-left text-sm text-red-600 transition-colors hover:bg-red-50">
                                            <i class="fas fa-trash-alt w-5 mr-3"></i>
                                            {{ __('vendor.delete') }}
                                        </button>
                                    @else
                                    <form action="{{ route('vendor.staff.destroy', $vendorUser) }}"
                                          method="POST"
                                          onsubmit="return confirm(@js(__('vendor.confirm_delete')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash-alt w-5 mr-3"></i>
                                            {{ __('vendor.delete') }}
                                        </button>
                                    </form>
                                    @endif
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
        @foreach($staff as $vendorUser)
        @php $member = $vendorUser->user; @endphp
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center flex-1 min-w-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <a href="{{ route('vendor.staff.show', $vendorUser) }}"
                           @if($livewireList ?? false) wire:navigate @endif
                           class="block truncate text-sm font-semibold text-emerald-700 hover:underline">
                            {{ $member->name }}
                        </a>
                        <p class="text-xs text-gray-600">{{ $member->mobile }}</p>
                    </div>
                </div>

                @if(!$vendorUser->is_owner)
                    <div class="relative ml-2 flex-shrink-0" x-data="{ mobileDropdownOpen: false }">
                        <button @click="mobileDropdownOpen = !mobileDropdownOpen"
                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                                type="button">
                            <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                        </button>

                        <div x-show="mobileDropdownOpen"
                             @click.away="mobileDropdownOpen = false"
                             x-transition
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                             style="display: none;">
                            <a href="{{ route('vendor.staff.show', $vendorUser) }}"
                               @if($livewireList ?? false) wire:navigate @endif
                               class="block border-b border-gray-100 px-4 py-3 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50 active:bg-gray-100">
                                <i class="fas fa-eye w-5 text-gray-400 mr-3"></i>
                                {{ __('vendor.view_staff') }}
                            </a>
                            <a href="{{ route('vendor.staff.edit', $vendorUser) }}"
                               @if($livewireList ?? false) wire:navigate @endif
                               class="block border-b border-gray-100 px-4 py-3 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50 active:bg-gray-100">
                                <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                {{ __('vendor.edit') }}
                            </a>
                            @if($livewireList ?? false)
                                <button type="button"
                                        wire:click="deleteStaff({{ $vendorUser->id }})"
                                        wire:confirm="{{ __('vendor.confirm_delete') }}"
                                        class="block w-full px-4 py-3 text-left text-sm text-red-600 transition-colors hover:bg-red-50 active:bg-red-100">
                                    <i class="fas fa-trash-alt w-5 mr-3"></i>
                                    {{ __('vendor.delete') }}
                                </button>
                            @else
                            <form action="{{ route('vendor.staff.destroy', $vendorUser) }}"
                                  method="POST"
                                  onsubmit="return confirm(@js(__('vendor.confirm_delete')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                    <i class="fas fa-trash-alt w-5 mr-3"></i>
                                    {{ __('vendor.delete') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center space-x-4 mb-3 text-xs">
                <span class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-800 rounded">
                    <i class="fas fa-user-tag mr-1"></i>
                    {{ $roleLabel($vendorUser) }}
                </span>
                @if($vendorUser->is_owner)
                    <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded">
                        <i class="fas fa-crown mr-1"></i>{{ __('vendor.owner') }}
                    </span>
                @endif
            </div>

            @if($vendorUser->last_login_at)
                <p class="text-xs text-gray-500 mb-3">
                    <i class="fas fa-clock mr-1"></i>
                    {{ __('vendor.last_login') }}: {{ \Carbon\Carbon::parse($vendorUser->last_login_at)->diffForHumans() }}
                </p>
            @endif

            @if(!$vendorUser->is_owner)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">{{ __('vendor.status') }}</span>
                    @if($livewireList ?? false)
                        <div class="inline-block">
                            <button type="button"
                                    wire:click="toggleStatus({{ $vendorUser->id }})"
                                    class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors {{ $vendorUser->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform {{ $vendorUser->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            <span class="ml-2 text-xs font-semibold {{ $vendorUser->is_active ? 'text-emerald-700' : 'text-gray-600' }}">
                                {{ $vendorUser->is_active ? __('vendor.active') : __('vendor.inactive') }}
                            </span>
                        </div>
                    @else
                    <div class="inline-block" x-data="{ isActive: {{ $vendorUser->is_active ? 'true' : 'false' }} }">
                        <form action="{{ route('vendor.staff.toggle', $vendorUser) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
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
                    @endif
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

    @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $staff->links() }}
        </div>
    @endif
@else
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-users text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_staff_yet') }}</h3>
        <p class="text-gray-600 mb-6">{{ __('vendor.manage_team_members') }}</p>
        <a href="{{ route('vendor.staff.create') }}"
           @if($livewireList ?? false) wire:navigate @endif
           class="inline-flex min-h-[44px] items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
            {{ __('vendor.add_staff_member') }}
        </a>
    </div>
@endif
