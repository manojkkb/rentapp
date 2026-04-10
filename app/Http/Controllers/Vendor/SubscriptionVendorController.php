<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Faker\Provider\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionVendorController extends Controller
{
    //
    public function subscriptionPlans()
    {
        $typeOrder = ['silver' => 1, 'gold' => 2, 'diamond' => 3];

        $plansByType = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->sortBy(fn ($_, $type) => $typeOrder[$type] ?? 99);

        return view('vendor.subscription.plans', compact('plansByType'));
    }
    public function createOrder(Request $request)
    {
         $vendor = Auth::user()->currentVendor();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $api = new \Razorpay\Api\Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'subscription_plan_id' => $plan->id,
            'vendor_id' => $vendor->id,
            'start_date' => now(),
            'expiry_date' => now()->addDays($plan->duration_days),
            'status' => 'pending',
            'amount' => $plan->price,
            'auto_renew' => false
        ]);

        $order = $api->order->create([
            'receipt' => trim('order_' . time()),
            'amount' => $plan->price * 100,
            'currency' => 'INR'
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => Auth::id(),
            'vendor_id' => $vendor->id,
            'subscription_id' => $subscription->id,
            'amount' => $plan->price,
            'payment_gateway' => 'razorpay',
            'payment_id' => uniqid('pay_'),
            'status' => 'pending',
            'order_id' => $order['id']
        ]);
       

        return response()->json([
            'order_id' =>  $order['id'],
            'amount' => $order['amount'],
            'plan_name' => $plan->name
        ]);
    }  
    
    public function verifyPayment(Request $request)
    {
        $api = new \Razorpay\Api\Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ]);

            try{

            $payment = SubscriptionPayment::where('order_id', $request->razorpay_order_id)->first();
            if(!$payment){
                return response()->json(['status' => 'payment_not_found', 'order_id' => $request->razorpay_order_id]);

            }
           
            $payment->update([
                'payment_id' => $request->razorpay_payment_id,
                'status' => 'completed',
                'paid_at' => now()
            ]);
            $subscription = Subscription::findOrFail($payment->subscription_id);
            $subscription->update([
                'status' => 'active',
                'payment_gateway' => 'razorpay',
                'payment_id' => $request->razorpay_payment_id,
                'auto_renew' => false
            ]); 
            }
            catch(\Exception $e){
                // Log the error for debugging
                // \Log::error('Payment verification failed: ' . $e->getMessage());
                return response()->json(['status' =>  $e->getMessage()]);
            }

          

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'failed']);
        }
    }
}
