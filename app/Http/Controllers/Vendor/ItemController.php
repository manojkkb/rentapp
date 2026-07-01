<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\ManagesVendorItemPayload;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\Category;
use App\Models\ItemAttribute;
use App\Models\Items;
use App\Models\ItemVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    use ManagesVendorItemPayload;
    use RedirectsIfNumericRouteKey;

    protected function itemVendorId(): int
    {
        $vendor = Auth::user()->currentVendor();
        abort_if(! $vendor, 403);

        return $vendor->id;
    }

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

        return view('vendor.items.index');
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
        $variantFormState = $this->variantFormStateForView();

        return view('vendor.items.create', compact('vendor', 'categories', 'rentalPeriods', 'variantFormState'));
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

        try {
            $item = $this->createVendorItem($request, $vendor->id);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item created successfully!',
                    'item' => $item,
                ]);
            }

            return redirect()->route('vendor.items.index')
                ->with('success', __('vendor.item_added'));

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

            $item = Items::query()
                ->whereKey($item->id)
                ->withOrderStockBreakdown()
                ->with('category')
                ->firstOrFail();

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
            'stock' => $i->rentableAvailableStock(),
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

        $item = Items::query()
            ->whereKey($item->getKey())
            ->where('vendor_id', $vendor->id)
            ->withOrderStockBreakdown()
            ->with([
                'category',
                'images',
                'variantAttributes' => fn ($q) => $q->ordered(),
                'variants' => fn ($q) => $q->ordered()->withOrderStockBreakdown(),
            ])
            ->firstOrFail();

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

        $item->load('images');

        $categories = $this->categoriesForItemForm($vendor->id, $item->category_id);

        $rentalPeriods = $this->rentalPeriodOptions();
        $variantFormState = $this->variantFormStateForView($item);

        return view('vendor.items.edit', compact('vendor', 'item', 'categories', 'rentalPeriods', 'variantFormState'));
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

        try {
            $item = $this->updateVendorItem($request, $item, $vendor->id);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!',
                    'item' => $item,
                ]);
            }

            return redirect()->route('vendor.items.index')
                ->with('success', __('vendor.item_updated'));

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
        $this->deleteAllItemGalleryImages($item);

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
     * @return array{hasVariants: bool, attributes: list<array<string, mixed>>, variants: list<array<string, mixed>>}
     */
    private function variantFormStateForView(?Items $item = null): array
    {
        if (old('has_variants') !== null || old('variant_attributes') !== null || old('variants') !== null) {
            return $this->variantFormStateFromOld();
        }

        if ($item?->usesVariants()) {
            $item->load([
                'variantAttributes' => fn ($q) => $q->ordered(),
                'variants' => fn ($q) => $q->ordered(),
            ]);

            return [
                'hasVariants' => true,
                'itemManageStock' => (bool) $item->manage_stock,
                'itemIsAvailable' => (bool) $item->is_available,
                'itemIsActive' => (bool) $item->is_active,
                'attributes' => $item->variantAttributes->map(fn (ItemAttribute $a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'slug' => $a->slug,
                    'sort_order' => $a->sort_order,
                ])->values()->all(),
                'variants' => $item->variants->map(fn (ItemVariant $v) => [
                    'id' => $v->id,
                    'variant_code' => $v->variant_code,
                    'attributes' => $v->getAttribute('attributes') ?? [],
                    'price' => (float) $v->price,
                    'stock' => (int) $v->stock,
                    'damaged_stock' => (int) $v->damaged_stock,
                    'maintenance_stock' => (int) $v->maintenance_stock,
                    'manage_stock' => (bool) $v->manage_stock,
                    'is_available' => (bool) $v->is_available,
                    'is_active' => (bool) $v->is_active,
                ])->values()->all(),
            ];
        }

        return [
            'hasVariants' => false,
            'attributes' => [],
            'variants' => [],
            'itemManageStock' => (bool) ($item?->manage_stock ?? true),
            'itemIsAvailable' => (bool) ($item?->is_available ?? true),
            'itemIsActive' => (bool) ($item?->is_active ?? true),
        ];
    }

    /**
     * @return array{hasVariants: bool, attributes: list<array<string, mixed>>, variants: list<array<string, mixed>>}
     */
    private function variantFormStateFromOld(): array
    {
        $attributes = collect(old('variant_attributes', []))
            ->values()
            ->map(function ($row, $index) {
                if (! is_array($row)) {
                    return null;
                }
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'id' => ! empty($row['id']) ? (int) $row['id'] : null,
                    'name' => $name,
                    'slug' => trim((string) ($row['slug'] ?? '')) ?: ItemAttribute::slugFromName($name),
                    'sort_order' => (int) ($row['sort_order'] ?? $index),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $variants = collect(old('variants', []))
            ->values()
            ->map(function ($row) {
                if (! is_array($row)) {
                    return null;
                }

                return [
                    'id' => ! empty($row['id']) ? (int) $row['id'] : null,
                    'variant_code' => $row['variant_code'] ?? null,
                    'attributes' => is_array($row['attributes'] ?? null) ? $row['attributes'] : [],
                    'price' => (float) ($row['price'] ?? 0),
                    'stock' => (int) ($row['stock'] ?? 0),
                    'damaged_stock' => (int) ($row['damaged_stock'] ?? 0),
                    'maintenance_stock' => (int) ($row['maintenance_stock'] ?? 0),
                    'manage_stock' => filter_var($row['manage_stock'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'is_available' => filter_var($row['is_available'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'hasVariants' => (bool) old('has_variants'),
            'itemManageStock' => filter_var(old('manage_stock', true), FILTER_VALIDATE_BOOLEAN),
            'itemIsAvailable' => filter_var(old('is_available', true), FILTER_VALIDATE_BOOLEAN),
            'itemIsActive' => filter_var(old('is_active', true), FILTER_VALIDATE_BOOLEAN),
            'attributes' => $attributes,
            'variants' => $variants,
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
}
