<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Items;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    /**
     * Display a listing of items
     */
    public function index()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get all categories for filter
        $categories = Category::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Price types for modals
        $priceTypes = [
            'per_day' => 'Per Day',
            'per_hour' => 'Per Hour',
            'fixed' => 'Fixed Price',
        ];
        
        $items = Items::where('vendor_id', $vendor->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('vendor.items.index', compact('items', 'vendor', 'categories', 'priceTypes'));
    }
    
    /**
     * Fetch items via AJAX with filters
     */
    public function fetchItems(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return response()->json(['error' => 'Please select a vendor'], 403);
        }
        
        $query = Items::where('vendor_id', $vendor->id)
            ->with('category');
        
        // Filter by category
        if ($request->filled('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by subcategory
        if ($request->filled('subcategory_id') && $request->subcategory_id != '') {
            $query->where('category_id', $request->subcategory_id);
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $items = $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'items' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ]
        ]);
    }
    
    /**
     * Get subcategories by category ID
     */
    public function getSubcategories(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return response()->json(['error' => 'Please select a vendor'], 403);
        }
        
        $categoryId = $request->get('category_id');
        
        $subcategories = Category::where('vendor_id', $vendor->id)
            ->where('parent_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'subcategories' => $subcategories
        ]);
    }
    
    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get active categories for dropdown
        $categories = Category::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Price types
        $priceTypes = [
            'per_day' => 'Per Day',
            'per_hour' => 'Per Hour',
            'fixed' => 'Fixed Price',
        ];
        
        return view('vendor.items.create', compact('vendor', 'categories', 'priceTypes'));
    }
    
    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|numeric|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_day,per_hour,fixed',
            'stock' => 'required|integer|min:0',
            'manage_stock' => 'nullable',
            'is_available' => 'nullable',
            'is_active' => 'nullable',
        ]);
        
        // Convert checkbox values to booleans
        $manageStock = filter_var($request->input('manage_stock', false), FILTER_VALIDATE_BOOLEAN);
        $isAvailable = filter_var($request->input('is_available', false), FILTER_VALIDATE_BOOLEAN);
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        
        // Generate unique slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Items::where('vendor_id', $vendor->id)
                ->where('slug', $slug)
                ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        try {
            $item = Items::create([
                'vendor_id' => $vendor->id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'price_type' => $request->price_type,
                'stock' => $request->stock,
                'manage_stock' => $manageStock,
                'is_available' => $isAvailable,
                'is_active' => $isActive,
            ]);
            
            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item created successfully!',
                    'item' => $item
                ]);
            }
            
            return redirect()->route('vendor.items.index')
                ->with('success', 'Item created successfully!');
                
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create item: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show the form for editing an item
     */
    public function edit(Items $item)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // If AJAX request, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category_id' => $item->category_id,
                    'description' => $item->description,
                    'price' => $item->price,
                    'price_type' => $item->price_type,
                    'stock' => $item->stock,
                    'manage_stock' => $item->manage_stock,
                    'is_available' => $item->is_available,
                    'is_active' => $item->is_active,
                ]
            ]);
        }
        
        // Get active categories for dropdown
        $categories = Category::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Price types
        $priceTypes = [
            'per_day' => 'Per Day',
            'per_hour' => 'Per Hour',
            'fixed' => 'Fixed Price',
        ];
        
        return view('vendor.items.edit', compact('vendor', 'item', 'categories', 'priceTypes'));
    }
    
    /**
     * Update the specified item
     */
    public function update(Request $request, Items $item)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|numeric|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_day,per_hour,fixed',
            'stock' => 'required|integer|min:0',
            'manage_stock' => 'nullable',
            'is_available' => 'nullable',
            'is_active' => 'nullable',
        ]);
        
        // Convert checkbox values to booleans
        $manageStock = filter_var($request->input('manage_stock', false), FILTER_VALIDATE_BOOLEAN);
        $isAvailable = filter_var($request->input('is_available', false), FILTER_VALIDATE_BOOLEAN);
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        
        // Generate unique slug if name changed
        $slug = Str::slug($request->name);
        if ($slug != $item->slug) {
            $originalSlug = $slug;
            $counter = 1;
            
            while (Items::where('vendor_id', $vendor->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $item->id)
                    ->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        } else {
            $slug = $item->slug;
        }
        
        try {
            $item->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'price_type' => $request->price_type,
                'stock' => $request->stock,
                'manage_stock' => $manageStock,
                'is_available' => $isAvailable,
                'is_active' => $isActive,
            ]);
            
            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!',
                    'item' => $item
                ]);
            }
            
            return redirect()->route('vendor.items.index')
                ->with('success', 'Item updated successfully!');
                
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update item: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update item: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Remove the specified item
     */
    public function destroy(Items $item)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $item->delete(); // Soft delete
        
        return redirect()->route('vendor.items.index')
            ->with('success', 'Item deleted successfully!');
    }
    
    /**
     * Toggle item active status
     */
    public function toggleStatus(Items $item)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $item->update([
            'is_active' => !$item->is_active
        ]);
        
        $status = $item->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Item {$status} successfully!");
    }
    
    /**
     * Toggle item availability
     */
    public function toggleAvailability(Items $item)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $item->update([
            'is_available' => !$item->is_available
        ]);
        
        $status = $item->is_available ? 'available' : 'unavailable';
        
        return back()->with('success', "Item marked as {$status}!");
    }
}
