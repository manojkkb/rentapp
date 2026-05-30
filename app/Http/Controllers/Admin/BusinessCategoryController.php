<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusinessCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $type = $request->query('type', 'all');

        $categories = BusinessCategory::query()
            ->with(['parent:id,name'])
            ->withCount(['vendors', 'children'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type === 'parent', fn ($q) => $q->whereNull('parent_id'))
            ->when($type === 'child', fn ($q) => $q->whereNotNull('parent_id'))
            ->when($type === 'active', fn ($q) => $q->where('is_active', true))
            ->when($type === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByRaw('parent_id IS NULL DESC')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.business-categories.index', [
            'categories' => $categories,
            'search' => $search,
            'type' => $type,
        ]);
    }

    public function create()
    {
        return view('admin.business-categories.form', [
            'category' => new BusinessCategory(['is_active' => true]),
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        BusinessCategory::create($data);

        return redirect()
            ->route('admin.business-categories.index')
            ->with('success', "Category \"{$data['name']}\" created.");
    }

    public function edit(BusinessCategory $businessCategory)
    {
        return view('admin.business-categories.form', [
            'category' => $businessCategory,
            'parents' => $this->parentOptions($businessCategory->id),
        ]);
    }

    public function update(Request $request, BusinessCategory $businessCategory)
    {
        $data = $this->validated($request, $businessCategory);
        $businessCategory->update($data);

        return redirect()
            ->route('admin.business-categories.index')
            ->with('success', "Category \"{$businessCategory->name}\" updated.");
    }

    public function destroy(BusinessCategory $businessCategory)
    {
        if ($businessCategory->vendors()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete a category assigned to vendors. Deactivate it instead.',
            ]);
        }

        if ($businessCategory->children()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete a category that has sub-categories. Remove or reassign them first.',
            ]);
        }

        $businessCategory->delete();

        return redirect()
            ->route('admin.business-categories.index')
            ->with('success', "Category \"{$businessCategory->name}\" deleted.");
    }

    private function validated(Request $request, ?BusinessCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('business_categories', 'slug')->ignore($category?->id),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('business_categories', 'id'),
                Rule::notIn($category ? [$category->id] : []),
            ],
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $parentId = $validated['parent_id'] ?? null;
        if ($category && $parentId) {
            $descendantIds = $this->descendantIds($category);
            if (in_array((int) $parentId, $descendantIds, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be nested under its own sub-category.',
                ]);
            }
        }

        $slug = $validated['slug'] ?? null;
        if (! $slug) {
            $slug = $this->uniqueSlug($validated['name'], $category?->id);
        } else {
            $slug = Str::slug($slug);
        }

        return [
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @return list<int>
     */
    private function descendantIds(BusinessCategory $category): array
    {
        $ids = [];
        $children = BusinessCategory::where('parent_id', $category->id)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = (int) $childId;
            $child = BusinessCategory::find($childId);
            if ($child) {
                $ids = array_merge($ids, $this->descendantIds($child));
            }
        }

        return $ids;
    }

    private function parentOptions(?int $excludeId = null)
    {
        return BusinessCategory::query()
            ->whereNull('parent_id')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            BusinessCategory::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
