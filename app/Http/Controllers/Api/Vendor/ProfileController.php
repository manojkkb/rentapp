<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\BusinessCategory;
use App\Support\ApiUserPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    use ResolvesApiVendor;

    public function show(): JsonResponse
    {
        $user = $this->user();
        $vendor = $this->vendor();

        return $this->ok([
            'user' => ApiUserPresenter::user($user),
            'vendor' => ApiUserPresenter::vendor($vendor),
            'business_categories' => BusinessCategory::query()->active()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updatePersonal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'language' => ['nullable', 'string', 'in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa'],
        ]);

        $this->user()->update($validated);

        return $this->ok(['user' => ApiUserPresenter::user($this->user()->fresh())], 'Profile updated.');
    }

    public function updateBusiness(Request $request): JsonResponse
    {
        $vendor = $this->vendor();
        $this->requirePermission('settings.edit');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'business_category_id' => ['sometimes', 'integer', 'exists:business_categories,id'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'language' => ['nullable', 'string', 'in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa'],
        ]);

        $vendor->update($validated);

        return $this->ok(['vendor' => ApiUserPresenter::vendor($vendor->fresh())], 'Business profile updated.');
    }
}
