@php
    $isEdit = isset($role) && $role instanceof \App\Models\VendorRole;
    $selectedPermissionIds = collect(old('permissions', $isEdit ? $role->permissions->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<form action="{{ $isEdit ? route('vendor.staff-permissions.update', $role) : route('vendor.staff-permissions.store') }}"
      method="POST"
      class="rounded-xl border border-gray-200 bg-white shadow-sm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-5 p-4 sm:p-5">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="role_name" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.staff_permissions_role_name') }} *</label>
                <input type="text"
                       name="name"
                       id="role_name"
                       value="{{ old('name', $isEdit ? $role->name : '') }}"
                       maxlength="100"
                       required
                       class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="role_description" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.staff_permissions_role_description') }}</label>
                <input type="text"
                       name="description"
                       id="role_description"
                       value="{{ old('description', $isEdit ? ($role->description ?? '') : '') }}"
                       maxlength="255"
                       class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('description') border-red-500 @enderror">
                @error('description')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if($isEdit)
            <p class="text-xs text-gray-500">
                {{ __('vendor.staff_permissions_permission_count', ['count' => $role->permissions->count()]) }}
                <span class="mx-1 text-gray-300">·</span>
                {{ __('vendor.staff_permissions_staff_count', ['count' => $role->vendor_users_count]) }}
                @if($role->is_system)
                    <span class="mx-1 text-gray-300">·</span>
                    <span class="font-medium text-amber-800">{{ __('vendor.staff_permissions_system_role') }}</span>
                @endif
            </p>
        @endif

        <div class="border-t border-gray-100 pt-5">
            <p class="mb-1 text-sm font-medium text-gray-800">{{ __('vendor.staff_permissions_permissions') }}</p>
            <p class="mb-4 text-xs text-gray-500">{{ __('vendor.staff_permissions_form_permissions_hint') }}</p>

            @if ($permissions->isEmpty())
                <p class="text-sm text-gray-500">{{ __('vendor.staff_permissions_no_permission_list') }}</p>
            @else
                <div class="space-y-4">
                    @foreach ($permissions as $group => $groupPermissions)
                        <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-3 sm:p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ \App\Models\VendorPermission::groupLabel($group) }}</p>
                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                                @foreach ($groupPermissions as $permission)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-md p-2 hover:bg-white">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               @checked(in_array($permission->id, $selectedPermissionIds, true))
                                               class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="min-w-0 text-sm leading-snug text-gray-700">{{ $permission->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-gray-100 px-4 py-4 sm:flex-row sm:justify-end sm:px-5">
        <a href="{{ $isEdit ? route('vendor.staff-permissions.show', $role) : route('vendor.staff-permissions.index') }}"
           wire:navigate
           class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
            {{ __('vendor.staff_permissions_cancel') }}
        </a>
        <button type="submit"
                class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 sm:min-h-[40px]">
            <i class="fas {{ $isEdit ? 'fa-check' : 'fa-plus' }} text-xs" aria-hidden="true"></i>
            {{ $isEdit ? __('vendor.staff_permissions_update_role') : __('vendor.staff_permissions_create_role') }}
        </button>
    </div>
</form>
