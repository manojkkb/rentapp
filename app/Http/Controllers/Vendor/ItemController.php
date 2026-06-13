<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\Category;
use App\Models\ItemAttribute;
use App\Models\ItemImage;
use App\Models\Items;
use App\Models\ItemVariant;
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

        $request->validate($this->itemPayloadValidationRules());
        $this->validateGalleryImagePayload($request);
        $this->validateItemInventoryConsistency($request);
        $this->validateVariantPayload($request);

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

            $this->syncItemGalleryImages($item, $request, $vendor->id);
            $this->syncItemVariantsFromRequest($item, $request);

            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item created successfully!',
                    'item' => $item->fresh()->load(['category', 'variantAttributes', 'variants']),
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

        $request->validate($this->itemPayloadValidationRules($item));
        $this->validateGalleryImagePayload($request, $item);
        $this->validateItemInventoryConsistency($request);
        $this->validateVariantPayload($request);

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

            $this->syncItemGalleryImages($item, $request, $vendor->id);
            $this->syncItemVariantsFromRequest($item, $request);

            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!',
                    'item' => $item->fresh()->load(['category', 'variantAttributes', 'variants']),
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
     * @return array<string, mixed>
     */
    private function itemPayloadValidationRules(?Items $item = null): array
    {
        $vendor = Auth::user()->currentVendor();
        $usesVariants = request()->boolean('has_variants');

        $rules = [
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
            'price' => ($usesVariants ? 'nullable' : 'required').'|numeric|min:0',
            'rental_period' => ['required', Rule::in(Items::rentalPeriodKeys())],
            'security_deposit' => 'required|numeric|min:0',
            'replacement_cost' => 'required|numeric|min:0',
            'late_fee' => 'required|numeric|min:0',
            'min_rental_duration' => 'required|integer|min:1|max:3650',
            'max_rental_duration' => 'required|integer|min:1|max:3650',
            'condition_status' => ['required', Rule::in(Items::CONDITION_STATUSES)],
            'stock' => ($usesVariants ? 'nullable' : 'required').'|integer|min:0',
            'damaged_stock' => ($usesVariants ? 'nullable' : 'required').'|integer|min:0',
            'maintenance_stock' => ($usesVariants ? 'nullable' : 'required').'|integer|min:0',
            'manage_stock' => 'nullable',
            'has_variants' => 'nullable|boolean',
            'is_available' => 'nullable',
            'is_active' => 'nullable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        return $rules;
    }

    private function validateVariantPayload(Request $request): void
    {
        if (! $request->boolean('has_variants')) {
            return;
        }

        $request->validate([
            'variant_attributes' => ['required', 'array', 'min:1'],
            'variant_attributes.*.name' => ['required', 'string', 'max:64'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.damaged_stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.maintenance_stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
        ], [
            'variant_attributes.required' => __('vendor.item_variants_attributes_required'),
            'variants.required' => __('vendor.item_variants_rows_required'),
            'variants.min' => __('vendor.item_variants_rows_required'),
        ]);
    }

    private function validateGalleryImagePayload(Request $request, ?Items $item = null): void
    {
        $request->validate([
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);

        $max = 8;
        $keptExisting = 0;

        if ($item) {
            $removeIds = array_map('intval', (array) $request->input('remove_gallery_images', []));
            $validRemoveCount = $item->images()->whereIn('id', $removeIds)->count();
            $keptExisting = $item->images()->count() - $validRemoveCount;
        }

        $newCount = count(array_filter($request->file('gallery_images', []) ?? []));

        if ($keptExisting + $newCount > $max) {
            throw ValidationException::withMessages([
                'gallery_images' => [__('vendor.item_gallery_max_images', ['max' => $max])],
            ]);
        }
    }

    private function syncItemGalleryImages(Items $item, Request $request, int $vendorId): void
    {
        $removeIds = array_map('intval', (array) $request->input('remove_gallery_images', []));

        if ($removeIds !== []) {
            $images = ItemImage::query()
                ->where('item_id', $item->id)
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($images as $image) {
                $this->deleteItemPhotoFromS3($image->path);
                $image->delete();
            }
        }

        $files = $request->file('gallery_images', []);
        if ($files === [] || $files === null) {
            return;
        }

        $sortOrder = (int) ItemImage::query()
            ->where('item_id', $item->id)
            ->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $sortOrder++;
            $path = $this->storeItemGalleryImageOnS3($file, $vendorId, $item->id);

            ItemImage::create([
                'item_id' => $item->id,
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function deleteAllItemGalleryImages(Items $item): void
    {
        $item->loadMissing('images');

        foreach ($item->images as $image) {
            $this->deleteItemPhotoFromS3($image->path);
            $image->delete();
        }
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
        $usesVariants = $request->boolean('has_variants');
        $stock = (int) $request->input('stock', 0);

        return [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $usesVariants ? 0 : $request->price,
            'rental_period' => $request->rental_period,
            'security_deposit' => round((float) $request->security_deposit, 2),
            'replacement_cost' => round((float) $request->replacement_cost, 2),
            'late_fee' => round((float) $request->late_fee, 2),
            'min_rental_duration' => (int) $request->min_rental_duration,
            'max_rental_duration' => (int) $request->max_rental_duration,
            'condition_status' => $request->condition_status,
            'stock' => $usesVariants ? 0 : $stock,
            'damaged_stock' => $usesVariants ? 0 : (int) $request->input('damaged_stock'),
            'maintenance_stock' => $usesVariants ? 0 : (int) $request->input('maintenance_stock'),
            'manage_stock' => $request->boolean('manage_stock'),
            'has_variants' => $usesVariants,
            'is_available' => $request->boolean('is_available'),
            'is_active' => $request->boolean('is_active'),
        ];
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

    private function syncItemVariantsFromRequest(Items $item, Request $request): void
    {
        $usesVariants = $request->boolean('has_variants');
        $item->update(['has_variants' => $usesVariants]);

        if (! $usesVariants) {
            $item->variants()->delete();
            $item->variantAttributes()->delete();

            return;
        }

        $attributeRows = collect($request->input('variant_attributes', []))
            ->values()
            ->filter(fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== '');

        $keptAttributeIds = [];
        foreach ($attributeRows as $index => $row) {
            $name = trim((string) $row['name']);
            $slug = ItemAttribute::slugFromName(trim((string) ($row['slug'] ?? '')) ?: $name);
            $attribute = null;

            if (! empty($row['id'])) {
                $attribute = ItemAttribute::where('item_id', $item->id)->find((int) $row['id']);
            }

            if ($attribute) {
                $oldSlug = $attribute->slug;
                $attribute->update([
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => $index,
                ]);

                if ($oldSlug !== $slug) {
                    $this->renameVariantAttributeKey($item, $oldSlug, $slug);
                }
            } else {
                $attribute = ItemAttribute::create([
                    'item_id' => $item->id,
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => $index,
                ]);
            }

            $keptAttributeIds[] = $attribute->id;
        }

        $removedAttributes = ItemAttribute::where('item_id', $item->id)
            ->when($keptAttributeIds !== [], fn ($q) => $q->whereNotIn('id', $keptAttributeIds))
            ->get();

        foreach ($removedAttributes as $removed) {
            $this->removeVariantAttributeKey($item, $removed->slug);
            $removed->delete();
        }

        $definitions = $item->variantAttributes()->ordered()->get();
        $itemManageStock = $request->boolean('manage_stock');
        $itemIsAvailable = $request->boolean('is_available');
        $itemIsActive = $request->boolean('is_active');
        $variantRows = collect($request->input('variants', []))
            ->values()
            ->filter(fn ($row) => is_array($row));

        $keptVariantIds = [];
        foreach ($variantRows as $index => $row) {
            $normalizedAttributes = ItemVariant::normalizeAttributesForDefinitions(
                is_array($row['attributes'] ?? null) ? $row['attributes'] : [],
                $definitions
            );

            $stock = (int) ($row['stock'] ?? 0);
            $payload = [
                'attributes' => $normalizedAttributes,
                'price' => round((float) ($row['price'] ?? 0), 2),
                'stock' => $stock,
                'damaged_stock' => (int) ($row['damaged_stock'] ?? 0),
                'maintenance_stock' => (int) ($row['maintenance_stock'] ?? 0),
                'manage_stock' => $itemManageStock,
                'is_available' => $itemIsAvailable,
                'is_active' => $itemIsActive,
                'sort_order' => $index,
            ];

            $variant = null;
            if (! empty($row['id'])) {
                $variant = ItemVariant::where('item_id', $item->id)->find((int) $row['id']);
            }

            if ($variant) {
                $variant->update($payload);
            } else {
                $variant = ItemVariant::create(array_merge($payload, [
                    'item_id' => $item->id,
                ]));
            }

            $keptVariantIds[] = $variant->id;
        }

        if ($keptVariantIds !== []) {
            $item->variants()->whereNotIn('id', $keptVariantIds)->delete();
        } else {
            $item->variants()->delete();
        }

        $this->syncItemAggregatesFromVariants($item->fresh(['variants']));
    }

    private function renameVariantAttributeKey(Items $item, string $oldSlug, string $newSlug): void
    {
        foreach ($item->variants as $variant) {
            $values = $variant->getAttribute('attributes');
            if (! is_array($values) || ! array_key_exists($oldSlug, $values)) {
                continue;
            }

            $values[$newSlug] = $values[$oldSlug];
            unset($values[$oldSlug]);
            $variant->update(['attributes' => $values]);
        }
    }

    private function removeVariantAttributeKey(Items $item, string $slug): void
    {
        foreach ($item->variants as $variant) {
            $values = $variant->getAttribute('attributes');
            if (! is_array($values) || ! array_key_exists($slug, $values)) {
                continue;
            }

            unset($values[$slug]);
            $variant->update(['attributes' => $values]);
        }
    }

    private function syncItemAggregatesFromVariants(Items $item): void
    {
        if (! $item->usesVariants()) {
            return;
        }

        $variants = $item->variants;
        if ($variants->isEmpty()) {
            $item->updateQuietly([
                'price' => 0,
                'stock' => 0,
                'damaged_stock' => 0,
                'maintenance_stock' => 0,
                'is_available' => false,
            ]);

            return;
        }

        $item->updateQuietly([
            'price' => (float) $variants->min('price'),
            'stock' => (int) $variants->sum('stock'),
            'damaged_stock' => (int) $variants->sum('damaged_stock'),
            'maintenance_stock' => (int) $variants->sum('maintenance_stock'),
            'is_available' => $variants->contains(
                fn (ItemVariant $variant) => $variant->is_available && $variant->is_active && $variant->stock > 0
            ),
        ]);
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
        $filename = 'feature_'.time().'_'.Str::random(8).'.'.$file->extension();

        $path = $file->storeAs(
            'vendors/'.$vendorId.'/items/'.$itemId,
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

    private function storeItemGalleryImageOnS3(UploadedFile $file, int $vendorId, int $itemId): string
    {
        $filename = 'gallery_'.time().'_'.Str::random(8).'.'.$file->extension();

        $path = $file->storeAs(
            'vendors/'.$vendorId.'/items/'.$itemId.'/images',
            $filename,
            [
                'disk' => 's3',
                'visibility' => 'public',
            ]
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException(
                'Could not upload gallery image to storage. Check S3 credentials, bucket name, region, and IAM permissions (s3:PutObject).'
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
