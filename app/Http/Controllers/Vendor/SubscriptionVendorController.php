<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Support\VendorSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionVendorController extends Controller
{
    public function subscriptionPlans()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select');
        }

        $typeOrder = ['silver' => 1, 'gold' => 2, 'diamond' => 3];

        $plansByType = SubscriptionPlan::query()
            ->purchasable()
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->sortBy(fn ($_, $type) => $typeOrder[$type] ?? 99);

        $subscriptionStatus = VendorSubscription::status($vendor);
        $trialEndsAt = VendorSubscription::trialEndsAt($vendor);
        $trialDaysRemaining = VendorSubscription::trialDaysRemaining($vendor);
        $activeSubscription = VendorSubscription::activeSubscription($vendor);

        return view('vendor.subscription.plans', compact(
            'plansByType',
            'subscriptionStatus',
            'trialEndsAt',
            'trialDaysRemaining',
            'activeSubscription',
            'vendor',
        ));
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return response()->json(['message' => __('vendor.select_vendor_first')], 422);
        }

        if (VendorSubscription::hasActiveSubscription($vendor)) {
            return response()->json(['message' => __('vendor.subscription_already_active')], 422);
        }

        $plan = SubscriptionPlan::query()
            ->purchasable()
            ->findOrFail($request->plan_id);

        if (! config('services.razorpay.key') && ! env('RAZORPAY_KEY')) {
            return response()->json(['message' => __('vendor.subscription_payment_unconfigured')], 503);
        }

        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key', env('RAZORPAY_KEY')),
            config('services.razorpay.secret', env('RAZORPAY_SECRET')),
        );

        $amount = (float) ($plan->discount_price ?? $plan->price);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'subscription_plan_id' => $plan->id,
            'vendor_id' => $vendor->id,
            'start_date' => now(),
            'expiry_date' => now()->addDays($plan->duration_days),
            'status' => 'pending',
            'amount' => $amount,
            'auto_renew' => false,
        ]);

        $order = $api->order->create([
            'receipt' => 'sub_' . $subscription->id . '_' . time(),
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
        ]);

        SubscriptionPayment::create([
            'user_id' => Auth::id(),
            'vendor_id' => $vendor->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'payment_gateway' => 'razorpay',
            'payment_id' => uniqid('pay_'),
            'status' => 'pending',
            'order_id' => $order['id'],
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'plan_name' => $plan->name,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key', env('RAZORPAY_KEY')),
            config('services.razorpay.secret', env('RAZORPAY_SECRET')),
        );

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            $payment = SubscriptionPayment::where('order_id', $request->razorpay_order_id)->first();

            if (! $payment) {
                return response()->json([
                    'status' => 'payment_not_found',
                    'order_id' => $request->razorpay_order_id,
                ], 404);
            }

            $payment->update([
                'payment_id' => $request->razorpay_payment_id,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $subscription = Subscription::with('subscriptionPlan')->findOrFail($payment->subscription_id);

            Subscription::query()
                ->where('vendor_id', $subscription->vendor_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            $subscription->update([
                'status' => 'active',
                'start_date' => now(),
                'expiry_date' => now()->addDays($subscription->subscriptionPlan->duration_days),
                'payment_gateway' => 'razorpay',
                'payment_id' => $request->razorpay_payment_id,
                'auto_renew' => false,
            ]);

            return response()->json([
                'status' => 'success',
                'redirect' => route('vendor.home'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed'], 400);
        }
    }
}
