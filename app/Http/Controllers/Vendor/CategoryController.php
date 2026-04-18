<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $query = Category::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->with('subcategories');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'ILIKE', '%'.$search.'%');
        }

        $categories = $query->orderBy('name')->paginate(15);

        // Handle AJAX requests
        if ($request->ajax()) {
            return view('vendor.categories.partials.categories-list', compact('categories'))->render();
        }

        return view('vendor.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Get parent categories for dropdown
        $parentCategories = Category::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('vendor.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a vendor',
                ], 403);
            }

            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|numeric|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Ensure empty parent_id is converted to null
        $parentId = $request->parent_id ? (int) $request->parent_id : null;

        // Generate unique slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        $query = Category::where('vendor_id', $vendor->id)
            ->where('slug', $slug);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;

            $query = Category::where('vendor_id', $vendor->id)
                ->where('slug', $slug);

            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }
        }

        try {
            $category = Category::create([
                'vendor_id' => $vendor->id,
                'parent_id' => $parentId,
                'name' => $request->name,
                'slug' => $slug,
                'icon' => $request->icon,
                'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            ]);

            if ($request->hasFile('image')) {
                $path = $this->storeCategoryImageOnS3($request->file('image'), $vendor->id, $category->id);
                $category->update(['image' => $path]);
                
            }

            dd($_FILES);

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully!',
                    'category' => $category->load('subcategories', 'items'),
                ]);
            }

            return redirect()->route('vendor.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create category: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to create category: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a category
     */
    public function edit(Category $category)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify category belongs to this vendor
        if ($category->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get parent categories for dropdown (excluding this category and its children)
        $parentCategories = Category::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('vendor.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify category belongs to this vendor
        if ($category->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Generate unique slug if name changed
        $slug = Str::slug($request->name);
        if ($slug != $category->slug) {
            $originalSlug = $slug;
            $counter = 1;

            $query = Category::where('vendor_id', $vendor->id)
                ->where('slug', $slug)
                ->where('id', '!=', $category->id);

            if ($category->parent_id) {
                $query->where('parent_id', $category->parent_id);
            } else {
                $query->whereNull('parent_id');
            }

            while ($query->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;

                $query = Category::where('vendor_id', $vendor->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $category->id);

                if ($category->parent_id) {
                    $query->where('parent_id', $category->parent_id);
                } else {
                    $query->whereNull('parent_id');
                }
            }
        } else {
            $slug = $category->slug;
        }

        try {
            $data = [
                'name' => $request->name,
                'slug' => $slug,
                'icon' => $request->icon,
                'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            ];

            if ($request->hasFile('image')) {
                $this->deleteCategoryImageFromS3($category->image);
                $data['image'] = $this->storeCategoryImageOnS3($request->file('image'), $vendor->id, $category->id);
            }

            $category->update($data);

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully!',
                    'category' => $category->fresh()->load('subcategories', 'items'),
                ]);
            }

            return redirect()->route('vendor.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update category: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update category: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify category belongs to this vendor
        if ($category->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if category has items
        if ($category->items()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing items.',
                ], 400);
            }

            return back()->withErrors(['error' => 'Cannot delete category with existing items.']);
        }

        // Check if category has subcategories
        if ($category->subcategories()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with subcategories.',
                ], 400);
            }

            return back()->withErrors(['error' => 'Cannot delete category with subcategories.']);
        }

        $this->deleteCategoryImageFromS3($category->image);

        $category->delete();

        // Handle AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!',
            ]);
        }

        return redirect()->route('vendor.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Toggle category active status
     */
    public function toggleStatus(Category $category)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please select a vendor'], 403);
            }

            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify category belongs to this vendor
        if ($category->vendor_id != $vendor->id) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        // Return JSON response for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $category->is_active,
                'message' => "Category {$status} successfully!",
            ]);
        }

        return back()->with('success', "Category {$status} successfully!");
    }

    /**
     * Display subcategories of a parent category
     */
    public function subcategories(Category $category)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify category belongs to this vendor
        if ($category->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get subcategories with pagination
        $subcategories = Category::where('vendor_id', $vendor->id)
            ->where('parent_id', $category->id)
            ->orderBy('name')
            ->paginate(15);

        return view('vendor.categories.subcategories', compact('category', 'subcategories'));
    }

    private function storeCategoryImageOnS3(UploadedFile $file, int $vendorId, int $categoryId): string
    {
        $filename = 'cat_'.$categoryId.'_'.time().'_'.Str::random(8).'.'.$file->extension();

        return $file->storeAs(
            'vendors/'.$vendorId.'/categories',
            $filename,
            [
                'disk' => 's3',
                'visibility' => 'public',
            ]
        );
    }

    private function deleteCategoryImageFromS3(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
