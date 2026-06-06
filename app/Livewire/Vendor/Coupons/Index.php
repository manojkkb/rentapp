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

    public bool $showModal = false;

    public ?string $editingUuid = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'percent';

    public string $value = '';

    public string $minOrderAmount = '';

    public string $maxDiscountAmount = '';

    public string $usageLimit = '';

    public string $startDate = '';

    public string $endDate = '';

    public bool $isActive = true;

    public ?string $flashMessage = null;

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

    public function openCreateModal(): void
    {
        $this->editingUuid = null;
        $this->reset(['code', 'name', 'value', 'minOrderAmount', 'maxDiscountAmount', 'usageLimit', 'startDate', 'endDate']);
        $this->type = 'percent';
        $this->isActive = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(string $uuid): void
    {
        $coupon = $this->findCoupon($uuid);
        $this->editingUuid = $coupon->uuid;
        $this->code = $coupon->code;
        $this->name = (string) ($coupon->name ?? '');
        $this->type = $coupon->type;
        $this->value = (string) $coupon->value;
        $this->minOrderAmount = $coupon->min_order_amount > 0 ? (string) $coupon->min_order_amount : '';
        $this->maxDiscountAmount = $coupon->max_discount_amount ? (string) $coupon->max_discount_amount : '';
        $this->usageLimit = $coupon->usage_limit ? (string) $coupon->usage_limit : '';
        $this->startDate = $coupon->start_date?->format('Y-m-d') ?? '';
        $this->endDate = $coupon->end_date?->format('Y-m-d') ?? '';
        $this->isActive = (bool) $coupon->is_active;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingUuid = null;
        $this->resetErrorBag();
    }

    public function saveCoupon(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'minOrderAmount' => ['nullable', 'numeric', 'min:0'],
            'maxDiscountAmount' => ['nullable', 'numeric', 'min:0'],
            'usageLimit' => ['nullable', 'integer', 'min:1'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'isActive' => ['boolean'],
        ]);

        $code = strtoupper(trim($this->code));

        $duplicateQuery = Coupon::query()
            ->where('vendor_id', $this->vendorId())
            ->where('code', $code);

        if ($this->editingUuid) {
            $duplicateQuery->where('uuid', '!=', $this->editingUuid);
        }

        if ($duplicateQuery->exists()) {
            $this->addError('code', __('vendor.coupon_code_exists') ?? 'Coupon code already exists');

            return;
        }

        if ($this->type === 'percent' && (float) $this->value > 100) {
            $this->addError('value', __('vendor.coupon_percent_max') ?? 'Percentage cannot exceed 100%');

            return;
        }

        $payload = [
            'code' => $code,
            'name' => $this->name !== '' ? $this->name : null,
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->minOrderAmount !== '' ? $this->minOrderAmount : 0,
            'max_discount_amount' => $this->maxDiscountAmount !== '' ? $this->maxDiscountAmount : null,
            'usage_limit' => $this->usageLimit !== '' ? (int) $this->usageLimit : null,
            'start_date' => $this->startDate !== '' ? $this->startDate : null,
            'end_date' => $this->endDate !== '' ? $this->endDate : null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingUuid) {
            $this->findCoupon($this->editingUuid)->update($payload);
            $this->flashMessage = __('vendor.coupon_updated') ?? 'Coupon updated successfully!';
        } else {
            Coupon::create(array_merge($payload, [
                'vendor_id' => $this->vendorId(),
                'used_count' => 0,
            ]));
            $this->flashMessage = __('vendor.coupon_created') ?? 'Coupon created successfully!';
        }

        $this->closeModal();
    }

    public function toggleStatus(string $uuid): void
    {
        $coupon = $this->findCoupon($uuid);
        $coupon->update(['is_active' => ! $coupon->is_active]);
        $this->flashMessage = $coupon->is_active
            ? (__('vendor.coupon_activated') ?? 'Coupon activated')
            : (__('vendor.coupon_deactivated') ?? 'Coupon deactivated');
    }

    public function deleteCoupon(string $uuid): void
    {
        $this->findCoupon($uuid)->delete();
        $this->flashMessage = __('vendor.coupon_deleted') ?? 'Coupon deleted successfully!';
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

        return view('livewire.vendor.coupons.index', compact('coupons'));
    }

    private function findCoupon(string $uuid): Coupon
    {
        return Coupon::query()
            ->where('vendor_id', $this->vendorId())
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
