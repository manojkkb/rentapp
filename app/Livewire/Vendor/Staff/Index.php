<?php

namespace App\Livewire\Vendor\Staff;

use App\Livewire\Vendor\VendorComponent;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public ?string $flashMessage = null;

    public function mount(): void
    {
        if (session()->has('success')) {
            $this->flashMessage = session('success');
        }
    }

    public function updatingSearch(): void
    {
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

        return view('livewire.vendor.staff.index', [
            'staff' => $staff,
        ]);
    }
}
