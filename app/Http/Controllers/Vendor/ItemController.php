<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\Category;
use App\Models\Items;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    use RedirectsIfNumericRouteKey;

    /**
     * Price billing periods + fixed (labels are translated for the current locale).
     *
     * @return array<string, string>
     */
    private function rentalPeriodOptions(): array
    {
        return Items::rentalPeriodSelectOptions();
    }

    /**
     * Active categories for item forms; includes the item's current category if inactive.
     */
    private function categoriesForItemForm(int $vendorId, ?int $includeCategoryId = null): \Illuminate\Support\Collection
    {
        $categories = Category::where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($includeCategoryId && ! $categories->contains('id', $includeCategoryId)) {
            $current = Category::where('vendor_id', $vendorId)->find($includeCategoryId);
            if ($current) {
                $categories = $categories->push($current)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }
        }

        return $categories;
    }

    /**
     * Display a listing of items
     */
    public function index()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Get all categories for filter
        $categories = Category::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rentalPeriods = $this->rentalPeriodOptions();

        $items = Items::where('vendor_id', $vendor->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.items.index', compact('items', 'vendor', 'categories', 'rentalPeriods'));
    }

    /**
     * Fetch items via AJAX with filters
     */
    public function fetchItems(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return response()->json(['error' => 'Please select a vendor'], 403);
        }

        $query = Items::where('vendor_id', $vendor->id)
            ->with('category');

        // Filter by category (include direct subcategories so parent selection matches their items)
        if ($request->filled('category_id') && $request->category_id != '') {
            $categoryId = (int) $request->category_id;
            $childIds = Category::where('vendor_id', $vendor->id)
                ->where('parent_id', $categoryId)
                ->pluck('id');
            $categoryIds = $childIds->push($categoryId)->unique()->values()->all();
            $query->whereIn('category_id', $categoryIds);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
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
            ],
        ]);
    }

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $categories = $this->categoriesForItemForm($vendor->id);

        $rentalPeriods = $this->rentalPeriodOptions();

        return view('vendor.items.create', compact('vendor', 'categories', 'rentalPeriods'));
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $request->validate($this->itemPayloadValidationRules());
        $this->validateItemInventoryConsistency($request);

        // Generate unique slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Items::where('vendor_id', $vendor->id)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        try {
            $item = Items::create(array_merge([
                'vendor_id' => $vendor->id,
                'slug' => $slug,
            ], $this->itemAttributesFromRequest($request), $this->itemCodeFromRequest($request)));

            if (! $item->item_code) {
                $item->update(['item_code' => Items::codeFromId($item->id)]);
            }

            if ($request->hasFile('photo')) {
                $path = $this->storeItemPhotoOnS3($request->file('photo'), $vendor->id, $item->id);
                $item->update(['photo' => $path]);
            }

            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item created successfully!',
                    'item' => $item->fresh()->load('category'),
                ]);
            }

            return redirect()->route('vendor.items.index')
                ->with('success', 'Item created successfully!');

        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create item: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to create item: '.$e->getMessage()]);
        }
    }

    /**
     * Minimal item create from order wizard (JSON).
     */
    public function storeQuickForOrderWizard(Request $request): JsonResponse
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a vendor',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('vendor_id', $vendor->id)),
            ],
            'price' => 'required|numeric|min:0',
            'rental_period' => ['nullable', Rule::in(Items::rentalPeriodKeys())],
        ]);

        $rentalPeriod = $validated['rental_period'] ?? 'per_day';
        if (! in_array($rentalPeriod, Items::rentalPeriodKeys(), true)) {
            $rentalPeriod = 'per_day';
        }

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Items::where('vendor_id', $vendor->id)->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        try {
            $item = Items::create([
                'vendor_id' => $vendor->id,
                'slug' => $slug,
                'category_id' => (int) $validated['category_id'],
                'name' => $validated['name'],
                'price' => round((float) $validated['price'], 2),
                'rental_period' => $rentalPeriod,
                'security_deposit' => 0,
                'replacement_cost' => 0,
                'late_fee' => 0,
                'min_rental_duration' => 1,
                'max_rental_duration' => 90,
                'condition_status' => 'good',
                'stock' => 1,
                'damaged_stock' => 0,
                'maintenance_stock' => 0,
                'manage_stock' => true,
                'is_available' => true,
                'is_active' => true,
            ]);

            if (! $item->item_code) {
                $item->update(['item_code' => Items::codeFromId($item->id)]);
            }

            $item->load('category');

            return response()->json([
                'success' => true,
                'message' => __('vendor.order_wizard_item_created'),
                'item' => $this->catalogRowForOrderWizard($item),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('vendor.order_wizard_item_create_failed'),
                'errors' => ['error' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogRowForOrderWizard(Items $i): array
    {
        $pt = in_array($i->rental_period ?? '', Items::rentalPeriodKeys(), true) ? $i->rental_period : 'per_day';

        return [
            'id' => $i->id,
            'uuid' => $i->uuid,
            'item_code' => $i->item_code,
            'slug' => $i->slug,
            'name' => $i->name,
            'price' => (float) $i->price,
            'photo_url' => $i->photo_url,
            'category_id' => $i->category_id,
            'category' => $i->category ? ['id' => $i->category->id, 'name' => $i->category->name] : null,
            'stock' => (int) ($i->stock ?? 0),
            'manage_stock' => (bool) ($i->manage_stock ?? false),
            'rental_period' => $pt,
            'uses_billing_units' => Items::rentalPeriodUsesBillingUnits($pt),
        ];
    }

    /**
     * Display the specified item.
     */
    public function show(Request $request, Items $item)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($redirect = $this->redirectIfNumericRouteKey($request, $item, 'vendor.items.show')) {
            return $redirect;
        }

        $item->load('category');
        $rentalPeriods = $this->rentalPeriodOptions();
        $conditionLabels = Items::conditionStatusOptions();

        return view('vendor.items.show', compact('vendor', 'item', 'rentalPeriods', 'conditionLabels'));
    }

    /**
     * Show the form for editing an item
     */
    public function edit(Request $request, Items $item)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($redirect = $this->redirectIfNumericRouteKey($request, $item, 'vendor.items.edit')) {
            return $redirect;
        }

        // If AJAX request, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'item' => $this->itemJsonForEditor($item),
            ]);
        }

        $categories = $this->categoriesForItemForm($vendor->id, $item->category_id);

        $rentalPeriods = $this->rentalPeriodOptions();

        return view('vendor.items.edit', compact('vendor', 'item', 'categories', 'rentalPeriods'));
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, Items $item)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate($this->itemPayloadValidationRules($item));
        $this->validateItemInventoryConsistency($request);

        // Generate unique slug if name changed
        $slug = Str::slug($request->name);
        if ($slug != $item->slug) {
            $originalSlug = $slug;
            $counter = 1;

            while (Items::where('vendor_id', $vendor->id)
                ->where('slug', $slug)
                ->where('id', '!=', $item->id)
                ->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }
        } else {
            $slug = $item->slug;
        }

        try {
            $data = array_merge([
                'slug' => $slug,
                'item_code' => $this->resolveItemCodeForUpdate($request, $item),
            ], $this->itemAttributesFromRequest($request));

            if ($request->hasFile('photo')) {
                $this->deleteItemPhotoFromS3($item->photo);
                $data['photo'] = $this->storeItemPhotoOnS3($request->file('photo'), $vendor->id, $item->id);
            }

            $item->update($data);

            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!',
                    'item' => $item->fresh()->load('category'),
                ]);
            }

            return redirect()->route('vendor.items.index')
                ->with('success', 'Item updated successfully!');

        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update item: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update item: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified item
     */
    public function destroy(Items $item)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteItemPhotoFromS3($item->photo);

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

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $item->update([
            'is_active' => ! $item->is_active,
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

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Verify item belongs to this vendor
        if ($item->vendor_id != $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $item->update([
            'is_available' => ! $item->is_available,
        ]);

        $status = $item->is_available ? 'available' : 'unavailable';

        return back()->with('success', "Item marked as {$status}!");
    }

    /**
     * @return array<string, mixed>
     */
    private function itemPayloadValidationRules(?Items $item = null): array
    {
        $vendor = Auth::user()->currentVendor();

        return [
            'item_code' => [
                'nullable',
                'string',
                'max:32',
                'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::unique('items', 'item_code')
                    ->where(fn ($q) => $q->where('vendor_id', $vendor?->id))
                    ->ignore($item?->id),
            ],
            'name' => 'required|string|max:255',
            'category_id' => 'required|numeric|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'rental_period' => ['required', Rule::in(Items::rentalPeriodKeys())],
            'security_deposit' => 'required|numeric|min:0',
            'replacement_cost' => 'required|numeric|min:0',
            'late_fee' => 'required|numeric|min:0',
            'min_rental_duration' => 'required|integer|min:1|max:3650',
            'max_rental_duration' => 'required|integer|min:1|max:3650',
            'condition_status' => ['required', Rule::in(Items::CONDITION_STATUSES)],
            'stock' => 'required|integer|min:0',
            'damaged_stock' => 'required|integer|min:0',
            'maintenance_stock' => 'required|integer|min:0',
            'manage_stock' => 'nullable',
            'is_available' => 'nullable',
            'is_active' => 'nullable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    private function validateItemInventoryConsistency(Request $request): void
    {
        $min = (int) $request->input('min_rental_duration');
        $max = (int) $request->input('max_rental_duration');
        if ($max < $min) {
            throw ValidationException::withMessages([
                'max_rental_duration' => [__('vendor.maximum_rental_below_minimum')],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function itemAttributesFromRequest(Request $request): array
    {
        $stock = (int) $request->input('stock');

        return [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'rental_period' => $request->rental_period,
            'security_deposit' => round((float) $request->security_deposit, 2),
            'replacement_cost' => round((float) $request->replacement_cost, 2),
            'late_fee' => round((float) $request->late_fee, 2),
            'min_rental_duration' => (int) $request->min_rental_duration,
            'max_rental_duration' => (int) $request->max_rental_duration,
            'condition_status' => $request->condition_status,
            'stock' => $stock,
            'damaged_stock' => (int) $request->input('damaged_stock'),
            'maintenance_stock' => (int) $request->input('maintenance_stock'),
            'manage_stock' => $request->boolean('manage_stock', true),
            'is_available' => $request->boolean('is_available', $stock > 0),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemJsonForEditor(Items $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'item_code' => $item->item_code,
            'slug' => $item->slug,
            'name' => $item->name,
            'category_id' => $item->category_id,
            'description' => $item->description,
            'price' => (float) $item->price,
            'rental_period' => $item->rental_period,
            'stock' => (int) $item->stock,
            'security_deposit' => (float) $item->security_deposit,
            'replacement_cost' => (float) $item->replacement_cost,
            'late_fee' => (float) $item->late_fee,
            'min_rental_duration' => (int) $item->min_rental_duration,
            'max_rental_duration' => (int) $item->max_rental_duration,
            'condition_status' => $item->condition_status,
            'damaged_stock' => (int) $item->damaged_stock,
            'maintenance_stock' => (int) $item->maintenance_stock,
            'manage_stock' => (bool) $item->manage_stock,
            'is_available' => (bool) $item->is_available,
            'is_active' => (bool) $item->is_active,
            'photo_url' => $item->photo_url,
        ];
    }

    private function storeItemPhotoOnS3(UploadedFile $file, int $vendorId, int $itemId): string
    {
        $filename = 'item_'.$itemId.'_'.time().'_'.Str::random(8).'.'.$file->extension();

        $path = $file->storeAs(
            'vendors/'.$vendorId.'/items',
            $filename,
            [
                'disk' => 's3',
                'visibility' => 'public',
            ]
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException(
                'Could not upload the image to storage. Check S3 credentials, bucket name, region, and IAM permissions (s3:PutObject).'
            );
        }

        return $path;
    }

    /**
     * @return array<string, string>
     */
    private function itemCodeFromRequest(Request $request): array
    {
        $raw = trim((string) $request->input('item_code', ''));
        if ($raw === '') {
            return [];
        }

        return ['item_code' => Items::normalizeItemCode($raw)];
    }

    private function resolveItemCodeForUpdate(Request $request, Items $item): string
    {
        $raw = trim((string) $request->input('item_code', ''));
        if ($raw === '') {
            return $item->item_code;
        }

        return Items::normalizeItemCode($raw);
    }

    private function deleteItemPhotoFromS3(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
