<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\ItemController as WebItemController;
use App\Models\Items;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('items.view');

        return app(WebItemController::class)->fetchItems($request);
    }

    public function show(Items $item): JsonResponse
    {
        $this->requirePermission('items.view');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $item->load('category:id,name');

        return $this->ok(['item' => $item]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('items.create');
        $vendor = $this->vendor();

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'item_code' => [
                'nullable',
                'string',
                'max:32',
                'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::unique('items', 'item_code')->where(fn ($q) => $q->where('vendor_id', $vendor->id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'rental_period' => ['required', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
        ]);

        $stock = (int) ($validated['stock'] ?? 1);

        $itemCode = isset($validated['item_code'])
            ? Items::normalizeItemCode($validated['item_code'])
            : null;
        unset($validated['item_code']);

        $item = Items::query()->create([
            ...$validated,
            'vendor_id' => $vendor->id,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'item_code' => $itemCode,
            'is_active' => $validated['is_active'] ?? true,
            'is_available' => $validated['is_available'] ?? ($stock > 0),
            'stock' => $stock,
        ]);

        if (! $item->item_code) {
            $item->update(['item_code' => Items::codeFromId($item->id)]);
        }

        return $this->ok(['item' => $item->fresh()], 'Item created.', 201);
    }

    public function update(Request $request, Items $item): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $validated = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'item_code' => [
                'sometimes',
                'string',
                'max:32',
                'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::unique('items', 'item_code')->where(fn ($q) => $q->where('vendor_id', $this->vendor()->id))->ignore($item->id),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'rental_period' => ['sometimes', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
        ]);

        if (array_key_exists('stock', $validated)) {
            $validated['is_available'] = $validated['is_available'] ?? ((int) $validated['stock'] > 0);
        }

        if (isset($validated['item_code'])) {
            $validated['item_code'] = Items::normalizeItemCode($validated['item_code']);
        }

        $item->update($validated);

        return $this->ok(['item' => $item->fresh()], 'Item updated.');
    }

    public function destroy(Items $item): JsonResponse
    {
        $this->requirePermission('items.delete');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $item->delete();

        return $this->ok(null, 'Item deleted.');
    }
}
