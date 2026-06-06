<?php

namespace App\Livewire\Vendor\Orders;

use App\Livewire\Vendor\VendorComponent;
use App\Models\Order;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Order::query()
            ->where('vendor_id', $this->vendorId())
            ->with([
                'customer' => fn ($q) => $q->withTrashed(),
                'items',
            ]);

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->search !== '') {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                    ->orWhere('event_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(15);

        $statusCounts = [
            'all' => Order::where('vendor_id', $this->vendorId())->count(),
            'pending' => Order::where('vendor_id', $this->vendorId())->where('status', 'pending')->count(),
            'confirmed' => Order::where('vendor_id', $this->vendorId())->where('status', 'confirmed')->count(),
            'completed' => Order::where('vendor_id', $this->vendorId())->where('status', 'completed')->count(),
            'cancelled' => Order::where('vendor_id', $this->vendorId())->where('status', 'cancelled')->count(),
        ];

        $statusMeta = [
            'pending' => ['label' => __('vendor.pending'), 'icon' => 'fa-clock', 'tab' => 'border-amber-400/80 bg-amber-50 text-amber-900 shadow-sm', 'pill' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200/70'],
            'confirmed' => ['label' => __('vendor.confirmed'), 'icon' => 'fa-check', 'tab' => 'border-teal-400/80 bg-teal-50 text-teal-900 shadow-sm', 'pill' => 'bg-teal-100 text-teal-800 ring-1 ring-teal-200/70'],
            'completed' => ['label' => __('vendor.completed'), 'icon' => 'fa-circle-check', 'tab' => 'border-emerald-400/80 bg-emerald-50 text-emerald-900 shadow-sm', 'pill' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200/70'],
            'cancelled' => ['label' => __('vendor.cancelled'), 'icon' => 'fa-ban', 'tab' => 'border-rose-400/80 bg-rose-50 text-rose-900 shadow-sm', 'pill' => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200/70'],
        ];

        return view('livewire.vendor.orders.index', compact('orders', 'statusCounts', 'statusMeta'));
    }
}
