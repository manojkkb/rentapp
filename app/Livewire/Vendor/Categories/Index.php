<?php

namespace App\Livewire\Vendor\Categories;

use App\Livewire\Vendor\VendorComponent;
use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public ?string $flashMessage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $categoryId): void
    {
        $category = Category::query()
            ->where('vendor_id', $this->vendorId())
            ->whereNull('parent_id')
            ->whereKey($categoryId)
            ->firstOrFail();

        $category->update(['is_active' => ! $category->is_active]);
        $this->flashMessage = $category->is_active
            ? (__('vendor.category_activated') ?? 'Category activated')
            : (__('vendor.category_deactivated') ?? 'Category deactivated');
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::query()
            ->where('vendor_id', $this->vendorId())
            ->whereNull('parent_id')
            ->whereKey($categoryId)
            ->firstOrFail();

        $category->delete();
        $this->flashMessage = __('vendor.category_deleted') ?? 'Category deleted successfully!';
    }

    public function render()
    {
        $query = Category::query()
            ->where('vendor_id', $this->vendorId())
            ->whereNull('parent_id')
            ->with(['subcategories', 'items']);

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('livewire.vendor.categories.index', compact('categories'));
    }
}
