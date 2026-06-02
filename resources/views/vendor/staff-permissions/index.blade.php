@extends('vendor.layouts.app')

@section('title', __('vendor.staff_permissions_title'))
@section('page-title', __('vendor.staff_permissions_title'))

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.staff_permissions_title') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('vendor.staff_permissions_subtitle') }}</p>
    </div>
    <button type="button"
            @click="$dispatch('open-role-modal', { mode: 'create' })"
            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.staff_permissions_add_role') }}
    </button>
</div>

@if (session('success'))
    <div class="mb-6 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
        <p class="text-sm text-emerald-700">{{ session('success') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
        @foreach ($errors->all() as $error)
            <p class="text-sm text-red-700">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if ($permissions->isEmpty())
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        {{ __('vendor.staff_permissions_missing_seed') }}
    </div>
@endif

<div class="space-y-4">
    @forelse ($roles as $role)
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-900">{{ $role->name }}</h3>
                        @if ($role->is_system)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ __('vendor.staff_permissions_system_role') }}</span>
                        @endif
                    </div>
                    @if ($role->description)
                        <p class="mt-1 text-sm text-gray-600">{{ $role->description }}</p>
                    @endif
                    <p class="mt-2 text-xs text-gray-500">
                        {{ __('vendor.staff_permissions_permission_count', ['count' => $role->permissions_count]) }}
                        &middot;
                        {{ __('vendor.staff_permissions_staff_count', ['count' => $role->vendor_users_count]) }}
                    </p>
                    @if ($role->permissions->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($role->permissions->take(6) as $permission)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600">{{ $permission->label() }}</span>
                            @endforeach
                            @if ($role->permissions->count() > 6)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600">+{{ $role->permissions->count() - 6 }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button"
                            @click="$dispatch('open-role-modal', {
                                mode: 'edit',
                                id: {{ $role->id }},
                                name: @js($role->name),
                                description: @js($role->description ?? ''),
                                permissions: @js($role->permissions->pluck('id')->values()->all())
                            })"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-pen mr-1"></i>{{ __('vendor.edit') }}
                    </button>
                    @unless ($role->is_system)
                        <form action="{{ route('vendor.staff-permissions.destroy', $role) }}" method="POST"
                              onsubmit="return confirm(@js(__('vendor.staff_permissions_delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">
                                <i class="fas fa-trash mr-1"></i>{{ __('vendor.delete') }}
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
            <i class="fas fa-user-shield mb-3 text-3xl text-gray-400"></i>
            <p class="text-sm text-gray-600">{{ __('vendor.staff_permissions_no_roles') }}</p>
        </div>
    @endforelse
</div>

@push('modals')
{{-- Rendered outside scrollable <main> so the permission list is not clipped --}}
<div
    id="role-permission-modal"
    x-data="{
        visible: false,
        mode: 'create',
        roleId: null,
        name: '',
        description: '',
        selected: [],
        storeUrl: @js(route('vendor.staff-permissions.store')),
        updateUrlTemplate: @js(route('vendor.staff-permissions.update', ['staffPermission' => '__ID__'])),
        labels: {
            add: @js(__('vendor.staff_permissions_add_role')),
            edit: @js(__('vendor.staff_permissions_edit_role')),
        },
        get modalTitle() {
            return this.mode === 'edit' ? this.labels.edit : this.labels.add;
        },
        get formAction() {
            if (this.mode === 'edit' && this.roleId) {
                return this.updateUrlTemplate.replace('__ID__', this.roleId);
            }
            return this.storeUrl;
        },
        open(detail) {
            this.mode = detail.mode || 'create';
            this.roleId = detail.id ?? null;
            this.name = detail.name ?? '';
            this.description = detail.description ?? '';
            this.selected = Array.isArray(detail.permissions)
                ? detail.permissions.map(id => Number(id))
                : [];
            this.visible = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.visible = false;
            document.body.classList.remove('overflow-hidden');
        },
        isSelected(id) {
            return this.selected.includes(Number(id));
        },
        togglePermission(id) {
            id = Number(id);
            const index = this.selected.indexOf(id);
            if (index === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(index, 1);
            }
        },
        submitForm(event) {
            const form = event.target;
            let methodInput = form.querySelector('input[name=_method]');
            if (this.mode === 'edit') {
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    form.appendChild(methodInput);
                }
                methodInput.value = 'PUT';
            } else if (methodInput) {
                methodInput.remove();
            }
            form.querySelectorAll('input[data-sync-permission]').forEach(el => el.remove());
            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'permissions[]';
                input.value = id;
                input.setAttribute('data-sync-permission', '1');
                form.appendChild(input);
            });
        }
    }"
    x-cloak
    @open-role-modal.window="open($event.detail)"
    @keydown.escape.window="if (visible) close()"
>
    <div x-show="visible" x-transition.opacity class="fixed inset-0 z-[100] flex items-end justify-center p-2 sm:items-center sm:p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50" @click="close()"></div>
        <div class="relative z-10 flex h-[min(90dvh,720px)] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl sm:h-auto sm:max-h-[90dvh]">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                <h3 class="text-lg font-semibold text-gray-900" x-text="modalTitle"></h3>
                <button type="button" @click="close()" class="rounded-md p-1 text-gray-500 hover:bg-gray-100" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form :action="formAction" method="POST" class="flex min-h-0 flex-1 flex-col overflow-hidden" @submit="submitForm($event)">
                @csrf
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-y-contain px-4 py-4 [-webkit-overflow-scrolling:touch]">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.staff_permissions_role_name') }}</label>
                        <input type="text" name="name" x-model="name" required maxlength="100"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.staff_permissions_role_description') }}</label>
                        <input type="text" name="description" x-model="description" maxlength="255"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-700">{{ __('vendor.staff_permissions_permissions') }}</p>
                        @if ($permissions->isEmpty())
                            <p class="text-sm text-gray-500">{{ __('vendor.staff_permissions_no_permission_list') }}</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($permissions as $group => $groupPermissions)
                                    <div class="rounded-lg border border-gray-200 p-3">
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ \App\Models\VendorPermission::groupLabel($group) }}</p>
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            @foreach ($groupPermissions as $permission)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-md p-2 hover:bg-gray-50">
                                                    <input type="checkbox"
                                                           value="{{ $permission->id }}"
                                                           :checked="isSelected({{ $permission->id }})"
                                                           @change="togglePermission({{ $permission->id }})"
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
                <div class="flex shrink-0 justify-end gap-2 border-t border-gray-200 px-4 py-3">
                    <button type="button" @click="close()"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('vendor.staff_permissions_cancel') }}
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('vendor.staff_permissions_save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
