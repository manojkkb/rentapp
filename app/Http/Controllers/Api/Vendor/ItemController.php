<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\Concerns\ManagesVendorItemPayload;
use App\Http\Controllers\Vendor\ItemController as WebItemController;
use App\Models\ItemImage;
use App\Models\Items;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends ApiController
{
    use ManagesVendorItemPayload;
    use ResolvesApiVendor;

    protected function itemVendorId(): int
    {
        return $this->vendor()->id;
    }

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('items.view');

        return app(WebItemController::class)->fetchItems($request);
    }

    public function show(Items $item): JsonResponse
    {
        $this->requirePermission('items.view');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        return $this->ok(['item' => $this->presentItemForApi($item)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('items.create');

        $item = $this->createVendorItem($request, $this->vendor()->id);

        return $this->ok(['item' => $this->presentItemForApi($item)], 'Item created.', 201);
    }

    public function update(Request $request, Items $item): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $item = $this->updateVendorItem($request, $item, $this->vendor()->id);

        return $this->ok(['item' => $this->presentItemForApi($item)], 'Item updated.');
    }

    public function destroy(Items $item): JsonResponse
    {
        $this->requirePermission('items.delete');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $item->delete();

        return $this->ok(null, 'Item deleted.');
    }

    public function uploadPhoto(Request $request, Items $item): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $item = $this->uploadItemMainPhoto($request, $item, $this->vendor()->id);

        return $this->ok(['photo_url' => $item->photo_url], 'Photo uploaded.');
    }

    public function deletePhoto(Items $item): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $this->deleteItemMainPhoto($item);

        return $this->ok(null, 'Photo removed.');
    }

    public function uploadGalleryImage(Request $request, Items $item): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $image = $this->uploadItemGalleryImage($request, $item, $this->vendor()->id);

        return $this->ok(['image' => $this->presentGalleryImageForApi($image)], 'Gallery image uploaded.', 201);
    }

    public function deleteGalleryImage(Items $item, ItemImage $image): JsonResponse
    {
        $this->requirePermission('items.edit');
        abort_if($item->vendor_id !== $this->vendor()->id, 404);

        $this->deleteItemGalleryImage($item, $image);

        return $this->ok(null, 'Gallery image removed.');
    }
}
