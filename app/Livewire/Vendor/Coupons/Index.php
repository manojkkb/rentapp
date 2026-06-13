<?php

namespace App\Livewire\Vendor\Coupons;

use App\Livewire\Vendor\VendorComponent;
use App\Models\Coupon;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $typeFilter = '';

    #[Url(except: '')]
    public string $statusFilter = '';

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

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function toggleStatus(string $uuid): void
    {
        $coupon = $this->findCoupon($uuid);
        $coupon->update(['is_active' => ! $coupon->is_active]);
        $this->flashMessage = $coupon->is_active
            ? __('vendor.coupon_activated')
            : __('vendor.coupon_deactivated');
    }

    public function render()
    {
        $query = Coupon::query()->where('vendor_id', $this->vendorId());

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $coupons = $query->orderByDesc('created_at')->paginate(15);

        $vendorQuery = Coupon::query()->where('vendor_id', $this->vendorId());
        $stats = [
            'total' => (clone $vendorQuery)->count(),
            'active' => (clone $vendorQuery)->where('is_active', true)->count(),
        ];

        return view('livewire.vendor.coupons.index', compact('coupons', 'stats'));
    }

    private function findCoupon(string $uuid): Coupon
    {
        return Coupon::query()
            ->where('vendor_id', $this->vendorId())
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
