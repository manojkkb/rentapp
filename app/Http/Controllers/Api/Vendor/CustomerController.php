<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\VendorCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('customers.view');
        $vendor = $this->vendor();

        $query = VendorCustomer::query()->where('vendor_id', $vendor->id);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->ok([
            'customers' => $customers->items(),
            'meta' => $this->paginationMeta($customers),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('customers.manage');
        $vendor = $this->vendor();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'digits:10', 'unique:vendor_customers,mobile,NULL,id,vendor_id,'.$vendor->id],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = VendorCustomer::query()->create([
            ...$validated,
            'vendor_id' => $vendor->id,
        ]);

        return $this->ok(['customer' => $customer], 'Customer created.', 201);
    }

    public function update(Request $request, VendorCustomer $customer): JsonResponse
    {
        $this->requirePermission('customers.manage');
        abort_if($customer->vendor_id !== $this->vendor()->id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'mobile' => ['sometimes', 'digits:10', 'unique:vendor_customers,mobile,'.$customer->id.',id,vendor_id,'.$customer->vendor_id],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($validated);

        return $this->ok(['customer' => $customer->fresh()], 'Customer updated.');
    }

    public function destroy(VendorCustomer $customer): JsonResponse
    {
        $this->requirePermission('customers.manage');
        abort_if($customer->vendor_id !== $this->vendor()->id, 404);

        $customer->delete();

        return $this->ok(null, 'Customer deleted.');
    }
}
