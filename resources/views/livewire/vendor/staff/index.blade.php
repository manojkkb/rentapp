<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.staff_members') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('vendor.manage_team_members') }}</p>
        </div>
        <button type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700">
            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
            {{ __('vendor.add_staff_member') }}
        </button>
    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {{ $flashMessage }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-gray-400" aria-hidden="true"></i>
            </div>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   class="block w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-10 focus:border-emerald-500 focus:ring-emerald-500"
                   placeholder="{{ __('vendor.search') }} staff by name, mobile, or role...">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <i wire:loading wire:target="search" class="fas fa-spinner fa-spin text-gray-400" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm" wire:loading.class="opacity-60" wire:target="search,toggleStatus,saveStaff,deleteStaff">
        @include('vendor.staff.partials.staff-list', [
            'staff' => $staff,
            'livewireList' => true,
        ])
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="create-staff-modal" @keydown.escape.window="$wire.closeCreateModal()">
            <div class="flex min-h-screen items-center justify-center px-4 pb-20 pt-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeCreateModal" aria-hidden="true"></div>
                <div class="relative z-10 w-full max-w-lg rounded-lg bg-white shadow-xl" @click.stop>
                    <form wire:submit="saveStaff" class="overflow-hidden">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.add_staff_member') }}</h3>
                                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-4">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">{{ __('vendor.customer_name') }} *</label>
                                <input type="text" wire:model="newName" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('newName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">{{ __('vendor.mobile') }} *</label>
                                <input type="tel" wire:model="newMobile" maxlength="10" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('newMobile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">{{ __('vendor.email') }}</label>
                                <input type="email" wire:model="newEmail" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('newEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">{{ __('vendor.staff_role') }} *</label>
                                @if($roles->isEmpty())
                                    <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                        {{ __('vendor.staff_no_roles_hint') }}
                                    </p>
                                @else
                                    <select wire:model="newRoleId" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">{{ __('vendor.staff_select_role') }}</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('newRoleId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" wire:model="newIsActive" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-gray-700">{{ __('vendor.active') }}</span>
                            </label>
                        </div>
                        <div class="flex flex-col-reverse gap-2 border-t border-gray-200 px-6 py-4 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closeCreateModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('vendor.cancel') }}
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="saveStaff"
                                    @disabled($roles->isEmpty())
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-70">
                                <span wire:loading.remove wire:target="saveStaff">{{ __('vendor.add_staff_member') }}</span>
                                <span wire:loading wire:target="saveStaff"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
