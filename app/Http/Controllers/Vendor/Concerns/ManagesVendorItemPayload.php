<?php

namespace App\Http\Controllers\Vendor\Concerns;

use App\Models\ItemAttribute;
use App\Models\ItemImage;
use App\Models\Items;
use App\Models\ItemVariant;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ManagesVendorItemPayload
{
    abstract protected function itemVendorId(): int;

    protected function itemPayloadValidationRules(?Items $item = null): array
    {
        $vendorId = $this->itemVendorId();
        $usesVariants = $item
            ? (request()->has('has_variants') ? request()->boolean('has_variants') : (bool) $item->has_variants)
            : request()->boolean('has_variants');
        $isUpdate = $item !== null;
        $required = $isUpdate ? 'sometimes|required' : 'required';
        $optionalNumeric = $isUpdate ? 'sometimes|nullable|numeric|min:0' : 'nullable|numeric|min:0';
        $optionalInteger = $isUpdate ? 'sometimes|nullable|integer|min:0' : 'nullable|integer|min:0';
        $optionalDuration = $isUpdate ? 'sometimes|nullable|integer|min:1|max:3650' : 'nullable|integer|min:1|max:3650';

        return [
            'item_code' => [
                'nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::unique('items', 'item_code')->where(fn ($q) => $q->where('vendor_id', $vendorId))->ignore($item?->id),
            ],
            'name' => "{$required}|string|max:255",
            'category_id' => array_merge(
                $isUpdate ? ['sometimes'] : ['required'],
                ['numeric', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('vendor_id', $vendorId))]
            ),
            'description' => 'nullable|string',
            'price' => ($usesVariants ? 'nullable' : $required).'|numeric|min:0',
            'rental_period' => array_merge(
                $isUpdate ? ['sometimes'] : ['required'],
                [Rule::in(Items::rentalPeriodKeys())]
            ),
            'security_deposit' => $optionalNumeric,
            'replacement_cost' => $optionalNumeric,
            'late_fee' => $optionalNumeric,
            'min_rental_duration' => $optionalDuration,
            'max_rental_duration' => $optionalDuration,
            'condition_status' => array_merge(
                $isUpdate ? ['sometimes'] : ['nullable'],
                ['nullable', Rule::in(Items::CONDITION_STATUSES)]
            ),
            'stock' => ($usesVariants ? 'nullable' : ($isUpdate ? 'sometimes|nullable' : 'nullable')).'|integer|min:0',
            'damaged_stock' => ($usesVariants ? 'nullable' : ($isUpdate ? 'sometimes|nullable' : 'nullable')).'|integer|min:0',
            'maintenance_stock' => ($usesVariants ? 'nullable' : ($isUpdate ? 'sometimes|nullable' : 'nullable')).'|integer|min:0',
            'manage_stock' => 'nullable',
            'has_variants' => 'nullable|boolean',
            'is_available' => 'nullable',
            'is_active' => 'nullable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    protected function mergeItemCreateDefaults(Request $request): void
    {
        if (! $request->filled('rental_period')) {
            $request->merge(['rental_period' => 'per_day']);
        }

        $usesVariants = $request->boolean('has_variants');

        $defaults = [
            'security_deposit' => 0,
            'replacement_cost' => 0,
            'late_fee' => 0,
            'min_rental_duration' => 1,
            'max_rental_duration' => 90,
            'condition_status' => 'good',
            'damaged_stock' => 0,
            'maintenance_stock' => 0,
            'manage_stock' => true,
            'is_active' => true,
            'has_variants' => false,
        ];

        if (! $usesVariants && ! $request->exists('stock')) {
            $defaults['stock'] = 1;
        }

        if (! $request->exists('is_available')) {
            $stock = (int) $request->input('stock', $defaults['stock'] ?? 0);
            $defaults['is_available'] = $stock > 0;
        }

        foreach ($defaults as $key => $value) {
            if (! $request->exists($key)) {
                $request->merge([$key => $value]);
            }
        }
    }

    protected function validateVariantPayload(Request $request): void
    {
        if (! $request->boolean('has_variants')) {
            return;
        }

        $request->validate([
            'variant_attributes' => ['required', 'array', 'min:1'],
            'variant_attributes.*.id' => ['nullable', 'integer'],
            'variant_attributes.*.name' => ['required', 'string', 'max:64'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
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

    protected function validateGalleryImagePayload(Request $request, ?Items $item = null): void
    {
        $request->validate([
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);

        $max = 8;
        $keptExisting = $item ? $item->images()->count() - $item->images()->whereIn('id', array_map('intval', (array) $request->input('remove_gallery_images', [])))->count() : 0;
        $newCount = count(array_filter($request->file('gallery_images', []) ?? []));

        if ($keptExisting + $newCount > $max) {
            throw ValidationException::withMessages(['gallery_images' => [__('vendor.item_gallery_max_images', ['max' => $max])]]);
        }
    }

    protected function validateItemInventoryConsistency(Request $request, ?Items $item = null): void
    {
        $min = (int) $request->input('min_rental_duration', $item?->min_rental_duration ?? 1);
        $max = (int) $request->input('max_rental_duration', $item?->max_rental_duration ?? 90);

        if ($max < $min) {
            throw ValidationException::withMessages(['max_rental_duration' => [__('vendor.maximum_rental_below_minimum')]]);
        }
    }

    protected function createVendorItem(Request $request, int $vendorId): Items
    {
        $this->mergeItemCreateDefaults($request);
        $request->validate($this->itemPayloadValidationRules());
        $this->validateGalleryImagePayload($request);
        $this->validateItemInventoryConsistency($request);
        $this->validateVariantPayload($request);

        $item = Items::query()->create(array_merge([
            'vendor_id' => $vendorId,
            'slug' => $this->generateUniqueItemSlug($request->name, $vendorId),
        ], $this->itemAttributesFromRequest($request), $this->itemCodeFromRequest($request)));

        if (! $item->item_code) {
            $item->update(['item_code' => Items::codeFromId($item->id)]);
        }

        if ($request->hasFile('photo')) {
            $item->update(['photo' => $this->storeItemPhotoOnS3($request->file('photo'), $vendorId, $item->id)]);
        }

        $this->syncItemGalleryImages($item, $request, $vendorId);
        $this->syncItemVariantsFromRequest($item, $request);

        return $this->loadItemWithRelations($item->fresh());
    }

    protected function updateVendorItem(Request $request, Items $item, int $vendorId): Items
    {
        $request->validate($this->itemPayloadValidationRules($item));
        $this->validateGalleryImagePayload($request, $item);
        $this->validateItemInventoryConsistency($request, $item);
        $this->validateVariantPayload($request);

        $name = $request->input('name', $item->name);
        $slug = Str::slug((string) $name);
        if ($slug !== $item->slug) {
            $slug = $this->generateUniqueItemSlug((string) $name, $vendorId, $item->id);
        }

        $data = array_merge(['slug' => $slug, 'item_code' => $this->resolveItemCodeForUpdate($request, $item)], $this->itemAttributesFromRequest($request, $item));

        if ($request->hasFile('photo')) {
            $this->deleteItemPhotoFromS3($item->photo);
            $data['photo'] = $this->storeItemPhotoOnS3($request->file('photo'), $vendorId, $item->id);
        }

        $item->update($data);
        $this->syncItemGalleryImages($item, $request, $vendorId);
        $this->syncItemVariantsFromRequest($item, $request);

        return $this->loadItemWithRelations($item->fresh());
    }

    protected function loadItemWithRelations(Items $item): Items
    {
        return $item->load([
            'category:id,name',
            'variantAttributes' => fn ($q) => $q->ordered(),
            'variants' => fn ($q) => $q->ordered(),
            'images' => fn ($q) => $q->orderBy('sort_order'),
        ]);
    }

    protected function presentItemForApi(Items $item): array
    {
        $item = $this->loadItemWithRelations($item);

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
            'security_deposit' => (float) $item->security_deposit,
            'replacement_cost' => (float) $item->replacement_cost,
            'late_fee' => (float) $item->late_fee,
            'min_rental_duration' => (int) $item->min_rental_duration,
            'max_rental_duration' => (int) $item->max_rental_duration,
            'condition_status' => $item->condition_status,
            'stock' => (int) $item->stock,
            'damaged_stock' => (int) $item->damaged_stock,
            'maintenance_stock' => (int) $item->maintenance_stock,
            'manage_stock' => (bool) $item->manage_stock,
            'has_variants' => (bool) $item->has_variants,
            'is_available' => (bool) $item->is_available,
            'is_active' => (bool) $item->is_active,
            'photo_url' => $item->photo_url,
            'gallery_images' => $item->images->map(fn (ItemImage $image) => $this->presentGalleryImageForApi($image))->values()->all(),
            'category' => $item->category,
            'variant_attributes' => $item->variantAttributes->map(fn (ItemAttribute $a) => [
                'id' => $a->id, 'name' => $a->name, 'slug' => $a->slug, 'sort_order' => (int) $a->sort_order,
            ])->values()->all(),
            'variants' => $item->variants->map(fn (ItemVariant $v) => [
                'id' => $v->id, 'uuid' => $v->uuid, 'variant_code' => $v->variant_code,
                'attributes' => $v->getAttribute('attributes') ?? [],
                'price' => (float) $v->price, 'stock' => (int) $v->stock,
                'damaged_stock' => (int) $v->damaged_stock, 'maintenance_stock' => (int) $v->maintenance_stock,
                'manage_stock' => (bool) $v->manage_stock, 'is_available' => (bool) $v->is_available,
                'is_active' => (bool) $v->is_active, 'photo_url' => $v->photo_url,
            ])->values()->all(),
        ];
    }

    protected function generateUniqueItemSlug(string $name, int $vendorId, ?int $ignoreItemId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Items::query()->where('vendor_id', $vendorId)->where('slug', $slug)->when($ignoreItemId, fn ($q) => $q->where('id', '!=', $ignoreItemId))->exists()) {
            $slug = $originalSlug.'-'.($counter++);
        }

        return $slug;
    }

    protected function itemAttributesFromRequest(Request $request, ?Items $item = null): array
    {
        $usesVariants = $request->has('has_variants')
            ? $request->boolean('has_variants')
            : (bool) ($item?->has_variants ?? false);
        $stock = (int) $request->input('stock', $item?->stock ?? 0);

        return [
            'category_id' => $request->input('category_id', $item?->category_id),
            'name' => $request->input('name', $item?->name),
            'description' => $request->input('description', $item?->description),
            'price' => $usesVariants ? 0 : (float) $request->input('price', $item?->price ?? 0),
            'rental_period' => $request->input('rental_period', $item?->rental_period ?? 'per_day'),
            'security_deposit' => round((float) $request->input('security_deposit', $item?->security_deposit ?? 0), 2),
            'replacement_cost' => round((float) $request->input('replacement_cost', $item?->replacement_cost ?? 0), 2),
            'late_fee' => round((float) $request->input('late_fee', $item?->late_fee ?? 0), 2),
            'min_rental_duration' => (int) $request->input('min_rental_duration', $item?->min_rental_duration ?? 1),
            'max_rental_duration' => (int) $request->input('max_rental_duration', $item?->max_rental_duration ?? 90),
            'condition_status' => $request->input('condition_status', $item?->condition_status ?? 'good'),
            'stock' => $usesVariants ? 0 : $stock,
            'damaged_stock' => $usesVariants ? 0 : (int) $request->input('damaged_stock', $item?->damaged_stock ?? 0),
            'maintenance_stock' => $usesVariants ? 0 : (int) $request->input('maintenance_stock', $item?->maintenance_stock ?? 0),
            'manage_stock' => $request->has('manage_stock') ? $request->boolean('manage_stock') : (bool) ($item?->manage_stock ?? true),
            'has_variants' => $usesVariants,
            'is_available' => $request->has('is_available') ? $request->boolean('is_available') : (bool) ($item?->is_available ?? true),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : (bool) ($item?->is_active ?? true),
        ];
    }

    protected function syncItemVariantsFromRequest(Items $item, Request $request): void
    {
        $usesVariants = $request->boolean('has_variants');
        $item->update(['has_variants' => $usesVariants]);

        if (! $usesVariants) {
            $item->variants()->delete();
            $item->variantAttributes()->delete();

            return;
        }

        $keptAttributeIds = [];
        foreach (collect($request->input('variant_attributes', []))->values()->filter(fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== '') as $index => $row) {
            $name = trim((string) $row['name']);
            $slug = ItemAttribute::slugFromName(trim((string) ($row['slug'] ?? '')) ?: $name);
            $attribute = ! empty($row['id']) ? ItemAttribute::where('item_id', $item->id)->find((int) $row['id']) : null;

            if ($attribute) {
                $oldSlug = $attribute->slug;
                $attribute->update(['name' => $name, 'slug' => $slug, 'sort_order' => $index]);
                if ($oldSlug !== $slug) {
                    $this->renameVariantAttributeKey($item, $oldSlug, $slug);
                }
            } else {
                $attribute = ItemAttribute::create(['item_id' => $item->id, 'name' => $name, 'slug' => $slug, 'sort_order' => $index]);
            }

            $keptAttributeIds[] = $attribute->id;
        }

        foreach (ItemAttribute::where('item_id', $item->id)->when($keptAttributeIds !== [], fn ($q) => $q->whereNotIn('id', $keptAttributeIds))->get() as $removed) {
            $this->removeVariantAttributeKey($item, $removed->slug);
            $removed->delete();
        }

        $definitions = $item->variantAttributes()->ordered()->get();
        $itemManageStock = $request->boolean('manage_stock');
        $itemIsAvailable = $request->boolean('is_available');
        $itemIsActive = $request->boolean('is_active');
        $keptVariantIds = [];

        foreach (collect($request->input('variants', []))->values()->filter(fn ($row) => is_array($row)) as $index => $row) {
            $payload = [
                'attributes' => ItemVariant::normalizeAttributesForDefinitions(is_array($row['attributes'] ?? null) ? $row['attributes'] : [], $definitions),
                'price' => round((float) ($row['price'] ?? 0), 2),
                'stock' => (int) ($row['stock'] ?? 0),
                'damaged_stock' => (int) ($row['damaged_stock'] ?? 0),
                'maintenance_stock' => (int) ($row['maintenance_stock'] ?? 0),
                'manage_stock' => $itemManageStock,
                'is_available' => $itemIsAvailable,
                'is_active' => $itemIsActive,
                'sort_order' => $index,
            ];

            $variant = ! empty($row['id']) ? ItemVariant::where('item_id', $item->id)->find((int) $row['id']) : null;
            $variant = $variant ? tap($variant, fn ($v) => $v->update($payload)) : ItemVariant::create(array_merge($payload, ['item_id' => $item->id]));
            $keptVariantIds[] = $variant->id;
        }

        $keptVariantIds === [] ? $item->variants()->delete() : $item->variants()->whereNotIn('id', $keptVariantIds)->delete();
        $this->syncItemAggregatesFromVariants($item->fresh(['variants']));
    }

    protected function syncItemGalleryImages(Items $item, Request $request, int $vendorId): void
    {
        $removeIds = array_map('intval', (array) $request->input('remove_gallery_images', []));
        if ($removeIds !== []) {
            foreach (ItemImage::query()->where('item_id', $item->id)->whereIn('id', $removeIds)->get() as $image) {
                $this->deleteItemPhotoFromS3($image->path);
                $image->delete();
            }
        }

        $sortOrder = (int) ItemImage::query()->where('item_id', $item->id)->max('sort_order');
        foreach ($request->file('gallery_images', []) ?? [] as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            ItemImage::create(['item_id' => $item->id, 'path' => $this->storeItemGalleryImageOnS3($file, $vendorId, $item->id), 'sort_order' => ++$sortOrder]);
        }
    }

    protected function deleteAllItemGalleryImages(Items $item): void
    {
        $item->loadMissing('images');
        foreach ($item->images as $image) {
            $this->deleteItemPhotoFromS3($image->path);
            $image->delete();
        }
    }

    protected function renameVariantAttributeKey(Items $item, string $oldSlug, string $newSlug): void
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

    protected function removeVariantAttributeKey(Items $item, string $slug): void
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

    protected function syncItemAggregatesFromVariants(Items $item): void
    {
        if (! $item->usesVariants()) {
            return;
        }

        $variants = $item->variants;
        if ($variants->isEmpty()) {
            $item->updateQuietly(['price' => 0, 'stock' => 0, 'damaged_stock' => 0, 'maintenance_stock' => 0, 'is_available' => false]);

            return;
        }

        $item->updateQuietly([
            'price' => (float) $variants->min('price'),
            'stock' => (int) $variants->sum('stock'),
            'damaged_stock' => (int) $variants->sum('damaged_stock'),
            'maintenance_stock' => (int) $variants->sum('maintenance_stock'),
            'is_available' => $variants->contains(fn (ItemVariant $variant) => $variant->is_available && $variant->is_active && $variant->stock > 0),
        ]);
    }

    protected function itemCodeFromRequest(Request $request): array
    {
        $raw = trim((string) $request->input('item_code', ''));

        return $raw === '' ? [] : ['item_code' => Items::normalizeItemCode($raw)];
    }

    protected function resolveItemCodeForUpdate(Request $request, Items $item): string
    {
        $raw = trim((string) $request->input('item_code', ''));

        return $raw === '' ? $item->item_code : Items::normalizeItemCode($raw);
    }

    protected function storeItemPhotoOnS3(UploadedFile $file, int $vendorId, int $itemId): string
    {
        $path = $file->storeAs('vendors/'.$vendorId.'/items/'.$itemId, 'feature_'.time().'_'.Str::random(8).'.'.$file->extension(), ['disk' => 's3', 'visibility' => 'public']);
        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Could not upload the image to storage.');
        }

        return $path;
    }

    protected function storeItemGalleryImageOnS3(UploadedFile $file, int $vendorId, int $itemId): string
    {
        $path = $file->storeAs('vendors/'.$vendorId.'/items/'.$itemId.'/images', 'gallery_'.time().'_'.Str::random(8).'.'.$file->extension(), ['disk' => 's3', 'visibility' => 'public']);
        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Could not upload gallery image to storage.');
        }

        return $path;
    }

    protected function deleteItemPhotoFromS3(?string $path): void
    {
        if ($path && Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }

    protected function itemImageValidationRules(): array
    {
        return ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'];
    }

    protected function itemGalleryMaxImages(): int
    {
        return 8;
    }

    protected function assertItemGalleryHasRoom(Items $item, int $adding = 1): void
    {
        $max = $this->itemGalleryMaxImages();
        if ($item->images()->count() + $adding > $max) {
            throw ValidationException::withMessages([
                'image' => [__('vendor.item_gallery_max_images', ['max' => $max])],
            ]);
        }
    }

    protected function uploadItemMainPhoto(Request $request, Items $item, int $vendorId): Items
    {
        $request->validate(['photo' => $this->itemImageValidationRules()]);

        $this->deleteItemPhotoFromS3($item->photo);
        $item->update([
            'photo' => $this->storeItemPhotoOnS3($request->file('photo'), $vendorId, $item->id),
        ]);

        return $item->fresh();
    }

    protected function deleteItemMainPhoto(Items $item): Items
    {
        $this->deleteItemPhotoFromS3($item->photo);
        $item->update(['photo' => null]);

        return $item->fresh();
    }

    protected function uploadItemGalleryImage(Request $request, Items $item, int $vendorId): ItemImage
    {
        $request->validate(['image' => $this->itemImageValidationRules()]);
        $this->assertItemGalleryHasRoom($item);

        $sortOrder = (int) ItemImage::query()->where('item_id', $item->id)->max('sort_order');

        return ItemImage::create([
            'item_id' => $item->id,
            'path' => $this->storeItemGalleryImageOnS3($request->file('image'), $vendorId, $item->id),
            'sort_order' => $sortOrder + 1,
        ]);
    }

    protected function deleteItemGalleryImage(Items $item, ItemImage $image): void
    {
        abort_if($image->item_id !== $item->id, 404);

        $this->deleteItemPhotoFromS3($image->path);
        $image->delete();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentGalleryImageForApi(ItemImage $image): array
    {
        return [
            'id' => $image->id,
            'uuid' => $image->uuid,
            'url' => $image->url,
            'sort_order' => (int) $image->sort_order,
        ];
    }
}
