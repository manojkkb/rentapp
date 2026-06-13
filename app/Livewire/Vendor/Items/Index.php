<?php

namespace App\Livewire\Vendor\Items;

use App\Livewire\Vendor\VendorComponent;
use App\Models\Category;
use App\Models\Items;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoryId = '';

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

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryId = '';
        $this->resetPage();
    }

    public function toggleStatus(string $uuid): void
    {
        $item = Items::query()
            ->where('vendor_id', $this->vendorId())
            ->where('uuid', $uuid)
            ->firstOrFail();

        $item->update(['is_active' => ! $item->is_active]);
        $this->flashMessage = $item->is_active
            ? (__('vendor.item_activated') ?? 'Item activated')
            : (__('vendor.item_deactivated') ?? 'Item deactivated');
    }

    public function render()
    {
        $query = Items::query()
            ->where('vendor_id', $this->vendorId())
            ->with([
                'category',
                'variants' => fn ($q) => $q->withOrderStockBreakdown(),
            ])
            ->withOrderStockBreakdown();

        if ($this->categoryId !== '') {
            $categoryId = (int) $this->categoryId;
            $childIds = Category::where('vendor_id', $this->vendorId())
                ->where('parent_id', $categoryId)
                ->pluck('id');
            $categoryIds = $childIds->push($categoryId)->unique()->values()->all();
            $query->whereIn('category_id', $categoryIds);
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $items = $query->orderByDesc('created_at')->paginate(15);
        $categories = Category::where('vendor_id', $this->vendorId())
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $rentalPeriods = Items::rentalPeriodSelectOptions();

        return view('livewire.vendor.items.index', compact('items', 'categories', 'rentalPeriods'));
    }
}
