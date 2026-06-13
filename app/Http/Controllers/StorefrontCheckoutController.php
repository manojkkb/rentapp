<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VendorCustomer;
use App\Services\OtpService;
use App\Services\PlaceStorefrontOrder;
use App\Support\StorefrontBooking;
use App\Support\StorefrontCart;
use App\Support\StorefrontContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontCheckoutController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly PlaceStorefrontOrder $placeOrder,
    ) {}

    public function cart(string $slug): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        $cart = StorefrontCart::forVendor($ctx->vendor->id);
        $lines = $cart->enrichedLines($ctx->vendor->id);

        return view('storefront.cart', $ctx->viewData([
            'lines' => $lines,
            'cartSubtotal' => round($lines->sum('line_total'), 2),
        ]));
    }

    public function checkout(string $slug): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        $cart = StorefrontCart::forVendor($ctx->vendor->id);
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.cart', $slug)
                ->withErrors(['cart' => __('vendor.store_cart_empty')]);
        }

        $booking = StorefrontBooking::forVendor($ctx->vendor->id);
        if (! $booking->isSet()) {
            return redirect()->route('storefront.show', $slug)
                ->withErrors(['booking' => __('vendor.store_booking_required')]);
        }

        $lines = $cart->enrichedLines($ctx->vendor->id);
        $customer = $this->currentVendorCustomer($ctx->vendor->id);
        $cartSubtotal = round($lines->sum('line_total'), 2);
        $defaultDeliveryCharge = (float) ($ctx->store->default_delivery_charge ?? 0);
        $freeDeliveryMin = $ctx->store->free_delivery_min_amount;
        $bookingStart = $booking->startAt();
        $minDatetime = ($bookingStart && $bookingStart->isFuture())
            ? $bookingStart->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i');

        return view('storefront.checkout', $ctx->viewData([
            'lines' => $lines,
            'cartSubtotal' => $cartSubtotal,
            'customer' => $customer,
            'isLoggedIn' => Auth::check(),
            'defaultDeliveryCharge' => $defaultDeliveryCharge,
            'freeDeliveryMin' => $freeDeliveryMin,
            'deliveryChargePreview' => $ctx->store->resolveDeliveryCharge($cartSubtotal, 'delivery'),
            'minOrderAmount' => $ctx->store->min_order_amount,
            'minDatetime' => $minDatetime,
        ]));
    }

    public function place(Request $request, string $slug): RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        $cart = StorefrontCart::forVendor($ctx->vendor->id);
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.cart', $slug)
                ->withErrors(['cart' => __('vendor.store_cart_empty')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
            'fulfillment_type' => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:fulfillment_type,delivery', 'nullable', 'string', 'max:5000'],
            'pickup_at' => ['nullable', 'date', 'after_or_equal:now'],
            'delivery_at' => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        $booking = StorefrontBooking::forVendor($ctx->vendor->id);
        if (! $booking->isSet()) {
            return back()->withInput()->withErrors(['booking' => __('vendor.store_booking_required')]);
        }

        $checkoutPayload = array_merge($validated, [
            'start_at' => $booking->startAt()->toDateTimeString(),
            'end_at' => $booking->endAt()->toDateTimeString(),
        ]);

        if (! $this->otpService->verifyOtp($validated['mobile'], $validated['otp'])) {
            return back()->withInput()->withErrors(['otp' => __('vendor.store_otp_invalid')]);
        }

        $user = User::query()->where('mobile', $validated['mobile'])->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'mobile' => $validated['mobile'],
                'email' => $validated['mobile'].'@rentkia.temp',
                'password' => Hash::make(Str::random(16)),
            ]);
        } else {
            $user->update(['name' => $validated['name']]);
        }

        Auth::login($user);

        $vendorCustomer = VendorCustomer::query()->firstOrCreate(
            ['vendor_id' => $ctx->vendor->id, 'mobile' => $validated['mobile']],
            [
                'user_id' => $user->id,
                'name' => $validated['name'],
                'address' => $validated['delivery_address'] ?? null,
                'is_active' => true,
            ]
        );

        $vendorCustomer->update([
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);

        try {
            $order = $this->placeOrder->place(
                $ctx->vendor,
                $ctx->store,
                $vendorCustomer,
                $cart->toOrderLines(),
                $checkoutPayload,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        $cart->clear();

        return redirect()
            ->route('storefront.orders.show', [$slug, $order->uuid])
            ->with('success', __('vendor.store_order_placed'));
    }

    public function orders(string $slug): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        if (! Auth::check()) {
            return redirect()->route('storefront.checkout', $slug);
        }

        $customer = $this->currentVendorCustomer($ctx->vendor->id);
        $orders = $customer
            ? $customer->orders()->where('vendor_id', $ctx->vendor->id)->latest()->limit(20)->get()
            : collect();

        return view('storefront.orders', $ctx->viewData([
            'orders' => $orders,
            'customer' => $customer,
        ]));
    }

    public function orderShow(string $slug, string $orderUuid): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        $customer = $this->currentVendorCustomer($ctx->vendor->id);
        if (! $customer) {
            return redirect()->route('storefront.checkout', $slug);
        }

        $order = $customer->orders()
            ->where('vendor_id', $ctx->vendor->id)
            ->where('uuid', $orderUuid)
            ->with('items')
            ->firstOrFail();

        return view('storefront.order-show', $ctx->viewData([
            'order' => $order,
        ]));
    }

    public function sendOtp(Request $request, string $slug): JsonResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        $request->validate(['mobile' => ['required', 'digits:10']]);
        $otp = $this->otpService->sendOtp($request->mobile, 'phone');
        $response = ['success' => true, 'message' => __('vendor.store_otp_sent')];
        if (config('app.debug')) {
            $response['otp'] = $otp;
        }

        return response()->json($response);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     * @deprecated Use StorefrontContext::viewData()
     */
    private function viewData(StorefrontContext $ctx, array $extra = []): array
    {
        return $ctx->viewData($extra);
    }

    private function currentVendorCustomer(int $vendorId): ?VendorCustomer
    {
        if (! Auth::check()) {
            return null;
        }

        return VendorCustomer::query()
            ->where('vendor_id', $vendorId)
            ->where('user_id', Auth::id())
            ->first();
    }
}
