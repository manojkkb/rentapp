<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Models\VendorCustomer;
use App\Services\VendorRoleProvisioner;
use App\Support\VendorSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    private const LANGUAGES = ['en', 'hi', 'bn', 'mr', 'te', 'ta', 'gu', 'ur', 'kn', 'or', 'ml', 'pa'];

    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $status = $request->query('status', 'all');

        $vendors = Vendor::query()
            ->with(['user:id,name,email,mobile', 'businessCategory:id,name'])
            ->withCount('orders')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'verified', fn ($q) => $q->where('is_verified', true))
            ->when($status === 'pending', fn ($q) => $q->where('is_verified', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.vendors.index', [
            'vendors' => $vendors,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(Vendor $vendor)
    {
        $vendor->load([
            'user:id,name,email,mobile,language,created_at',
            'businessCategory:id,name,slug',
            'users' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.mobile')
                ->orderByPivot('is_owner', 'desc')
                ->orderBy('users.name'),
        ]);

        $stats = [
            'orders_total' => $vendor->orders()->count(),
            'orders_completed' => $vendor->orders()->where('status', 'completed')->count(),
            'gmv' => (float) $vendor->orders()->where('status', 'completed')->sum('grand_total'),
            'items' => $vendor->items()->count(),
            'categories' => $vendor->categories()->count(),
            'customers' => VendorCustomer::where('vendor_id', $vendor->id)->count(),
            'staff' => $vendor->users()->count(),
            'reviews' => $vendor->reviews()->count(),
        ];

        $recentOrders = $vendor->orders()
            ->with(['customer:id,name,mobile'])
            ->latest()
            ->limit(10)
            ->get(['id', 'order_number', 'customer_id', 'status', 'grand_total', 'start_at', 'created_at']);

        $activeSubscription = VendorSubscription::activeSubscription($vendor);
        $subscriptionStatus = VendorSubscription::status($vendor);
        $trialEndsAt = VendorSubscription::trialEndsAt($vendor);
        $subscriptionPlans = SubscriptionPlan::query()
            ->purchasable()
            ->orderBy('type')
            ->orderBy('billing_cycle')
            ->get(['id', 'name', 'type', 'billing_cycle', 'price', 'duration_days']);
        $subscriptionHistory = $vendor->subscriptions()
            ->with('subscriptionPlan:id,name,type,billing_cycle')
            ->latest()
            ->limit(5)
            ->get(['id', 'subscription_plan_id', 'status', 'amount', 'start_date', 'expiry_date', 'payment_gateway', 'created_at']);

        return view('admin.vendors.show', [
            'vendor' => $vendor,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'activeSubscription' => $activeSubscription,
            'subscriptionStatus' => $subscriptionStatus,
            'trialEndsAt' => $trialEndsAt,
            'subscriptionPlans' => $subscriptionPlans,
            'subscriptionHistory' => $subscriptionHistory,
        ]);
    }

    public function upgradeSubscription(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $plan = SubscriptionPlan::query()
            ->purchasable()
            ->findOrFail($validated['subscription_plan_id']);

        VendorSubscription::grantPlanManually(
            $vendor,
            $plan,
            (int) auth()->guard('admin')->id(),
        );

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', "Membership upgraded to \"{$plan->name}\".");
    }

    public function create()
    {
        return view('admin.vendors.form', [
            'vendor' => new Vendor([
                'is_active' => true,
                'is_verified' => false,
                'language' => 'en',
                'country' => 'India',
            ]),
            'categories' => $this->categories(),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $vendor = DB::transaction(function () use ($request, $data) {
            $vendor = Vendor::create($data);

            if ($request->hasFile('logo')) {
                $vendor->update(['logo' => $this->storeLogo($request->file('logo'), $vendor->id)]);
            }

            $this->syncOwnerPivot($vendor, (int) $data['user_id']);

            return $vendor;
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', "Vendor \"{$vendor->name}\" created.");
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('user:id,name,email,mobile');

        return view('admin.vendors.form', [
            'vendor' => $vendor,
            'categories' => $this->categories(),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $this->validated($request, $vendor);

        DB::transaction(function () use ($request, $vendor, $data) {
            if ($request->hasFile('logo')) {
                $this->deleteLogo($vendor->logo);
                $data['logo'] = $this->storeLogo($request->file('logo'), $vendor->id);
            }

            $ownerChanged = (int) $vendor->user_id !== (int) $data['user_id'];
            $vendor->update($data);

            if ($ownerChanged) {
                $this->syncOwnerPivot($vendor, (int) $data['user_id']);
            }
        });

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', "Vendor \"{$vendor->name}\" updated.");
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->orders()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete a vendor with orders. Deactivate the store instead.',
            ]);
        }

        $this->deleteLogo($vendor->logo);
        $vendor->delete();

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', "Vendor \"{$vendor->name}\" deleted.");
    }

    private function validated(Request $request, ?Vendor $vendor = null): array
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vendors', 'slug')->ignore($vendor?->id),
            ],
            'business_category_id' => 'nullable|exists:business_categories,id',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:15',
            'language' => ['required', 'string', Rule::in(self::LANGUAGES)],
            'is_verified' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slug = $validated['slug'] ?? null;
        if (! $slug) {
            $slug = $this->uniqueSlug($validated['name'], $vendor?->id);
        } else {
            $slug = Str::slug($slug);
        }

        return [
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'owner_name' => $validated['owner_name'] ?? null,
            'slug' => $slug,
            'business_category_id' => $validated['business_category_id'] ?? null,
            'address_line1' => $validated['address_line1'] ?? null,
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'language' => $validated['language'],
            'is_verified' => $request->boolean('is_verified'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncOwnerPivot(Vendor $vendor, int $userId): void
    {
        $user = User::findOrFail($userId);

        $existing = DB::table('vendor_users')
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            DB::table('vendor_users')
                ->where('id', $existing->id)
                ->update([
                    'is_owner' => true,
                    'role' => 'owner',
                    'is_active' => true,
                ]);
        } else {
            VendorUser::link($vendor->id, $userId, [
                'is_owner' => true,
                'role' => 'owner',
                'last_login_at' => now(),
            ]);

            app(VendorRoleProvisioner::class)->ensureDefaultRoles($vendor, $userId);
        }

        if (! $user->vendor_id) {
            $user->setCurrentVendorId($vendor->id);
        }
    }

    private function categories()
    {
        return BusinessCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function ownerOptions()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'mobile']);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            Vendor::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function storeLogo($file, int $vendorId): string
    {
        return $file->store("vendors/{$vendorId}", 'public');
    }

    private function deleteLogo(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
