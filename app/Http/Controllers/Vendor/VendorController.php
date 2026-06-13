<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\ListsVendorLogistics;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    use ListsVendorLogistics;
    /**
     * Display the vendor dashboard home page
     */
    public function home()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        return view('vendor.home.index', [
            'dashboard' => $this->buildDashboardPayload($vendor),
        ]);
    }

    /**
     * @return array{stats: array<string, mixed>, order_status_counts: array<string, int>, resource_counts: array{items: int, staff: int, customers: int}, logistics: array<string, mixed>, recent_activities: list<array<string, mixed>>, popular_items: list<array<string, mixed>>}
     */
    private function buildDashboardPayload(Vendor $vendor): array
    {
        $totalItems = $vendor->items()->count();
        $activeItems = $vendor->items()->where('is_active', true)->count();
        $availableItems = $vendor->items()
            ->where('is_active', true)
            ->where('is_available', true)
            ->count();
        $totalOrders = $vendor->orders()->count();
        $monthlyOrders = $vendor->orders()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $totalRevenue = (float) $vendor->orders()->where('status', 'completed')->sum('grand_total');
        $monthlyRevenue = (float) $vendor->orders()
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $statusCountRows = $vendor->orders()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
        $orderStatusCounts = [];
        foreach (Order::STATUSES as $status) {
            $orderStatusCounts[$status] = (int) ($statusCountRows[$status] ?? 0);
        }

        $pendingOrders = (int) ($orderStatusCounts['pending'] ?? 0);

        $outstandingBalance = (float) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->selectRaw('COALESCE(SUM(GREATEST(0, COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0))), 0) as balance')
            ->value('balance');

        $ordersWithBalanceDue = (int) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereRaw('(COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0)) > 0.009')
            ->count();

        $outOnRent = $vendor->orders()
            ->where('status', 'confirmed')
            ->whereNotNull('delivered_at')
            ->whereNull('returned_at')
            ->count();

        $staffCount = $vendor->activeUsers()->count();
        $customerCount = VendorCustomer::where('vendor_id', $vendor->id)->count();
        $categoryCount = $vendor->categories()->count();
        $couponCount = Coupon::where('vendor_id', $vendor->id)->count();

        $recentActivities = $vendor->orders()
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->uuid,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'items_count' => $order->items->count(),
                    'total_amount' => (float) $order->grand_total,
                    'paid_amount' => (float) ($order->paid_amount ?? 0),
                    'balance_due' => max(0, (float) $order->grand_total + (float) ($order->security_deposit ?? 0) - (float) ($order->paid_amount ?? 0)),
                    'status' => $order->status,
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        $popularItems = $vendor->items()
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->uuid,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'orders_count' => (int) ($item->order_items_count ?? 0),
                    'image' => $item->photo_url,
                ];
            })
            ->values()
            ->all();

        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        $outgoingQuery = $vendor->orders()
            ->whereNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $outgoingCount = (clone $outgoingQuery)->count();

        $outgoingOrders = (clone $outgoingQuery)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items.item'])
            ->orderByRaw('case
                when (start_at is not null and date(start_at) = ?)
                    or (pickup_at is not null and date(pickup_at) = ?)
                    or (delivery_at is not null and date(delivery_at) = ?)
                then 0
                when coalesce(delivery_at, start_at, pickup_at, created_at) < ?
                then 1
                else 2
            end asc, coalesce(delivery_at, start_at, pickup_at, created_at) asc', [$today, $today, $today, $now])
            ->take(5)
            ->get()
            ->map(fn (Order $order) => $this->mapDeliveryRow($order))
            ->values()
            ->all();

        $returnsQuery = $vendor->orders()
            ->whereNull('returned_at')
            ->whereNotNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $returnCount = (clone $returnsQuery)->count();

        $returnOrders = (clone $returnsQuery)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items.item'])
            ->orderByRaw('case
                when end_at is not null and date(end_at) = ? then 0
                when coalesce(end_at, created_at) < ? then 1
                else 2
            end asc, coalesce(end_at, created_at) asc', [$today, $now])
            ->take(5)
            ->get()
            ->map(fn (Order $order) => $this->mapReturnRow($order))
            ->values()
            ->all();

        return [
            'stats' => [
                'total_items' => $totalItems,
                'active_items' => $activeItems,
                'available_items' => $availableItems,
                'total_orders' => $totalOrders,
                'monthly_orders' => $monthlyOrders,
                'pending_orders' => $pendingOrders,
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'average_rating' => round((float) ($vendor->rating ?? 0), 1),
                'total_reviews' => (int) ($vendor->total_reviews ?? 0),
            ],
            'attention' => [
                'pending_orders' => $pendingOrders,
                'outstanding_balance' => $outstandingBalance,
                'orders_with_balance_due' => $ordersWithBalanceDue,
                'out_on_rent' => $outOnRent,
            ],
            'order_status_counts' => $orderStatusCounts,
            'resource_counts' => [
                'items' => $totalItems,
                'available_items' => $availableItems,
                'categories' => $categoryCount,
                'coupons' => $couponCount,
                'staff' => $staffCount,
                'customers' => $customerCount,
            ],
            'logistics' => [
                'outgoing_count' => $outgoingCount,
                'outgoing_orders' => $outgoingOrders,
                'return_count' => $returnCount,
                'return_orders' => $returnOrders,
            ],
            'recent_activities' => $recentActivities,
            'popular_items' => $popularItems,
        ];
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getDashboardStats(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildDashboardPayload($vendor),
        ]);
    }

    /**
     * Display the vendor profile
     */
    public function profile()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Get all business categories
        $categories = \App\Models\BusinessCategory::active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('vendor.profile.index', compact('vendor', 'categories'));
    }

    /**
     * Update the vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'business_category_id' => 'nullable|exists:business_categories,id',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only([
            'name', 'business_category_id', 'address_line1', 'address_line2',
            'city', 'state', 'postal_code', 'country', 'gst_number',
        ]);

        if ($request->hasFile('logo')) {
            $this->deleteVendorLogoFromStorage($vendor->logo);
            $data['logo'] = $this->storeVendorLogoOnS3($request->file('logo'), $vendor->id);
        }

        // Update slug if name changed
        if ($vendor->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']).'-'.$vendor->id;
        }

        $vendor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the personal profile
     */
    public function updatePersonalProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->hasFile('avatar')) {
            $this->deleteUserAvatarFromS3($user->avatar);
            $data['avatar'] = $this->storeUserAvatarOnS3($request->file('avatar'), $user->id);
        }

        $user->update($data);

        return back()->with('success', 'Personal profile updated successfully!');
    }

    /**
     * Update the business profile
     */
    public function updateBusinessProfile(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Check if user is owner
        $vendorUser = $user->vendors()->where('vendors.id', $vendor->id)->first();
        if (! $vendorUser || ! $vendorUser->pivot->is_owner) {
            return back()->withErrors(['error' => 'Only the business owner can update the business profile.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'business_category_id' => 'required|exists:business_categories,id',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only([
            'name', 'business_category_id', 'address_line1', 'address_line2',
            'city', 'state', 'postal_code', 'country', 'gst_number',
        ]);

        if ($request->hasFile('logo')) {
            $this->deleteVendorLogoFromStorage($vendor->logo);
            $data['logo'] = $this->storeVendorLogoOnS3($request->file('logo'), $vendor->id);
        }

        // Update slug if name changed
        if ($vendor->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']).'-'.$vendor->id;
        }

        $vendor->update($data);

        return back()->with('success', 'Business profile updated successfully!');
    }

    /**
     * Switch language
     */
    public function switchLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa',
        ]);

        // Store language in session
        session(['language' => $request->language]);

        // Update user's language preference
        $user = Auth::user();
        if ($user) {
            $user->update(['language' => $request->language]);
        }

        // Update vendor's language if exists
        $vendor = $user->currentVendor();
        if ($vendor) {
            $vendor->update(['language' => $request->language]);
        }

        return back()->with('success', 'Language changed successfully!');
    }

    private function storeVendorLogoOnS3(UploadedFile $file, int $vendorId): string
    {
        $filename = 'logo_'.time().'_'.Str::random(8).'.'.$file->extension();

        $path = $file->storeAs(
            'vendors/'.$vendorId.'/brand',
            $filename,
            [
                'disk' => 's3',
                'visibility' => 'public',
            ]
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException(
                'Could not upload the logo. Check S3 credentials and permissions (s3:PutObject).'
            );
        }

        return $path;
    }

    private function deleteVendorLogoFromStorage(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storeUserAvatarOnS3(UploadedFile $file, int $userId): string
    {
        $filename = 'avatar_'.time().'_'.Str::random(8).'.'.$file->extension();

        $path = $file->storeAs(
            'users/'.$userId.'/avatar',
            $filename,
            [
                'disk' => 's3',
                'visibility' => 'public',
            ]
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException(
                'Could not upload the profile image. Check S3 credentials and permissions (s3:PutObject).'
            );
        }

        return $path;
    }

    private function deleteUserAvatarFromS3(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
