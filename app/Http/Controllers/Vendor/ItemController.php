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
        
        $items = Items::where('vendor_id', $vendor->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('vendor.items.index', compact('items', 'vendor'));
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
            'manage_stock' => 'boolean',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        // Ensure empty values are converted to proper types
        $manageStock = $request->has('manage_stock') ? true : false;
        $isAvailable = $request->has('is_available') ? true : false;
        $isActive = $request->has('is_active') ? true : false;
        
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
            
            return redirect()->route('vendor.items.index')
                ->with('success', 'Item created successfully!');
                
        } catch (\Exception $e) {
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
            'manage_stock' => 'boolean',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        // Ensure empty values are converted to proper types
        $manageStock = $request->has('manage_stock') ? true : false;
        $isAvailable = $request->has('is_available') ? true : false;
        $isActive = $request->has('is_active') ? true : false;
        
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
            
            return redirect()->route('vendor.items.index')
                ->with('success', 'Item updated successfully!');
                
        } catch (\Exception $e) {
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
