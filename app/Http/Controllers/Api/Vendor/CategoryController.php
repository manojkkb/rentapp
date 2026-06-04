<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('categories.manage');
        $vendor = $this->vendor();

        $query = Category::query()
            ->where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->with('subcategories');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        $categories = $query->orderBy('name')->paginate($request->integer('per_page', 20));

        return $this->ok([
            'categories' => $categories->items(),
            'meta' => $this->paginationMeta($categories),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('categories.manage');
        $vendor = $this->vendor();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['boolean'],
        ]);

        $category = Category::query()->create([
            ...$validated,
            'vendor_id' => $vendor->id,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->ok(['category' => $category], 'Category created.', 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->requirePermission('categories.manage');
        abort_if($category->vendor_id !== $this->vendor()->id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $category->update($validated);

        return $this->ok(['category' => $category->fresh()], 'Category updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->requirePermission('categories.manage');
        abort_if($category->vendor_id !== $this->vendor()->id, 404);

        $category->delete();

        return $this->ok(null, 'Category deleted.');
    }
}
