<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    /**
     * Display the vendor dashboard home page
     */
    public function home()
    {
        return view('vendor.home.index');
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

        // Simulate delay for demo (remove in production)
        // sleep(1);

        // Get statistics
        $totalItems = $vendor->items()->count();
        $activeItems = $vendor->items()->where('is_active', true)->count();
        $totalOrders = $vendor->orders()->count();
        $monthlyOrders = $vendor->orders()->whereMonth('created_at', now()->month)->count();
        $totalRevenue = $vendor->orders()->where('status', 'completed')->sum('total_amount');
        $monthlyRevenue = $vendor->orders()
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        $averageRating = $vendor->reviews()->avg('rating') ?? 0;
        $totalReviews = $vendor->reviews()->count();

        // Get recent activities (latest orders)
        $recentActivities = $vendor->orders()
            ->with(['customer', 'items'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'items_count' => $order->items->count(),
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });

        // Get popular items
        $popularItems = $vendor->items()
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'orders_count' => $item->orders_count ?? 0,
                    'image' => $item->image_url ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_items' => $totalItems,
                    'active_items' => $activeItems,
                    'total_orders' => $totalOrders,
                    'monthly_orders' => $monthlyOrders,
                    'total_revenue' => $totalRevenue,
                    'monthly_revenue' => $monthlyRevenue,
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                ],
                'recent_activities' => $recentActivities,
                'popular_items' => $popularItems,
            ],
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
