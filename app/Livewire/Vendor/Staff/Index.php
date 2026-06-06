<?php

namespace App\Livewire\Vendor\Staff;

use App\Livewire\Vendor\VendorComponent;
use App\Models\User;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public bool $showCreateModal = false;

    public string $newName = '';

    public string $newMobile = '';

    public string $newEmail = '';

    public ?int $newRoleId = null;

    public bool $newIsActive = true;

    public ?string $flashMessage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['newName', 'newMobile', 'newEmail', 'newRoleId']);
        $this->newIsActive = true;
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['newName', 'newMobile', 'newEmail', 'newRoleId']);
        $this->resetErrorBag();
    }

    public function saveStaff(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newMobile' => ['required', 'digits:10'],
            'newEmail' => ['nullable', 'email', 'max:255'],
            'newRoleId' => [
                'required',
                Rule::exists('vendor_roles', 'id')->where('vendor_id', $this->vendorId()),
            ],
        ], [], [
            'newName' => __('vendor.customer_name'),
            'newMobile' => __('vendor.mobile'),
            'newEmail' => __('vendor.email'),
            'newRoleId' => __('vendor.staff_role'),
        ]);

        $vendorRole = VendorRole::query()
            ->where('vendor_id', $this->vendorId())
            ->findOrFail($this->newRoleId);

        DB::beginTransaction();

        try {
            $user = User::where('mobile', $this->newMobile)->first();

            if ($user) {
                if ($this->vendor->users()->where('user_id', $user->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'newMobile' => [__('vendor.staff_already_exists')],
                    ]);
                }

                VendorUser::link($this->vendorId(), $user->id, [
                    'is_owner' => false,
                    'vendor_role_id' => $vendorRole->id,
                    'role' => $vendorRole->slug,
                    'is_active' => $this->newIsActive,
                    'permissions' => [],
                ]);

                $message = __('vendor.staff_existing_user_added');
            } else {
                $user = User::create([
                    'name' => $this->newName,
                    'mobile' => $this->newMobile,
                    'email' => $this->newEmail !== '' ? $this->newEmail : $this->newMobile.'@staff.temp',
                    'password' => Hash::make('password123'),
                ]);

                VendorUser::link($this->vendorId(), $user->id, [
                    'is_owner' => false,
                    'vendor_role_id' => $vendorRole->id,
                    'role' => $vendorRole->slug,
                    'is_active' => $this->newIsActive,
                    'permissions' => [],
                ]);

                $message = __('vendor.staff_added_with_password');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->flashMessage = $message;
        $this->closeCreateModal();
        $this->resetPage();
    }

    public function toggleStatus(int $staffId): void
    {
        $vendorUser = VendorUser::query()
            ->where('vendor_id', $this->vendorId())
            ->whereKey($staffId)
            ->firstOrFail();

        if ($vendorUser->is_owner) {
            return;
        }

        $vendorUser->update(['is_active' => ! $vendorUser->is_active]);

        $status = $vendorUser->is_active ? __('vendor.activated') : __('vendor.deactivated');
        $this->flashMessage = __('vendor.staff_status_changed', ['status' => $status]);
    }

    public function deleteStaff(int $staffId): void
    {
        $vendorUser = VendorUser::query()
            ->where('vendor_id', $this->vendorId())
            ->whereKey($staffId)
            ->firstOrFail();

        if ($vendorUser->is_owner) {
            return;
        }

        $vendorUser->delete();
        $this->flashMessage = __('vendor.staff_deleted');
    }

    public function render()
    {
        $query = VendorUser::query()
            ->where('vendor_id', $this->vendorId())
            ->with(['user', 'vendorRole']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('mobile', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('vendorRole', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhere('role', 'like', '%'.$search.'%');
            });
        }

        $staff = $query->orderByDesc('created_at')->paginate(15);
        $roles = VendorRole::query()
            ->where('vendor_id', $this->vendorId())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.vendor.staff.index', [
            'staff' => $staff,
            'roles' => $roles,
        ]);
    }
}
