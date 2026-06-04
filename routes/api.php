<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Vendor\CalendarController as ApiCalendarController;
use App\Http\Controllers\Api\Vendor\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\Vendor\CouponController as ApiCouponController;
use App\Http\Controllers\Api\Vendor\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\Vendor\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\Vendor\ItemController as ApiItemController;
use App\Http\Controllers\Api\Vendor\OrderController as ApiOrderController;
use App\Http\Controllers\Api\Vendor\ProfileController as ApiProfileController;
use App\Http\Controllers\Api\Vendor\ReviewController as ApiReviewController;
use App\Http\Controllers\Api\Vendor\StaffController as ApiStaffController;
use App\Http\Controllers\Api\Vendor\SubscriptionController as ApiSubscriptionController;
use App\Http\Controllers\Api\Vendor\SupportController as ApiSupportController;
use App\Http\Middleware\EnsureApiVendor;
use App\Http\Middleware\EnforceVendorSubscription;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public — onboarding
    Route::get('business-categories', [AuthController::class, 'businessCategories']);

    // Auth — vendor mobile app (OTP only)
    Route::prefix('auth')->group(function () {
        Route::post('otp/send', [AuthController::class, 'sendOtp']);
        Route::post('otp/resend', [AuthController::class, 'resendOtp']);
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('vendors', [AuthController::class, 'vendors']);
            Route::post('vendors/select', [AuthController::class, 'selectVendor']);
            Route::post('vendors', [AuthController::class, 'createVendor']);
        });
    });

    // Protected APIs (token + selected vendor)
    Route::middleware(['auth:sanctum', EnsureApiVendor::class, EnforceVendorSubscription::class])
        ->group(function () {
            Route::get('dashboard', [ApiDashboardController::class, 'index']);

            Route::get('profile', [ApiProfileController::class, 'show']);
            Route::put('profile/personal', [ApiProfileController::class, 'updatePersonal']);
            Route::put('profile/business', [ApiProfileController::class, 'updateBusiness']);

            Route::apiResource('categories', ApiCategoryController::class)->except(['show']);
            Route::apiResource('items', ApiItemController::class);
            Route::apiResource('customers', ApiCustomerController::class)->except(['show']);

            Route::get('orders', [ApiOrderController::class, 'index']);
            Route::get('orders/deliveries', [ApiOrderController::class, 'deliveries']);
            Route::get('orders/returns', [ApiOrderController::class, 'returns']);
            Route::get('orders/{order}', [ApiOrderController::class, 'show']);
            Route::patch('orders/{order}/status', [ApiOrderController::class, 'updateStatus']);

            Route::get('calendar/events', [ApiCalendarController::class, 'events']);

            Route::apiResource('coupons', ApiCouponController::class)->except(['show']);

            Route::get('reviews', [ApiReviewController::class, 'index']);
            Route::post('reviews/{review}/reply', [ApiReviewController::class, 'reply']);

            Route::get('staff', [ApiStaffController::class, 'index']);
            Route::patch('staff/{staff}/toggle', [ApiStaffController::class, 'toggle']);

            Route::get('support', [ApiSupportController::class, 'show']);
            Route::post('support/messages', [ApiSupportController::class, 'storeMessage']);
            Route::get('support/socket-token', [ApiSupportController::class, 'socketToken']);

            Route::get('subscription/plans', [ApiSubscriptionController::class, 'plans']);
            Route::post('subscription/create-order', [ApiSubscriptionController::class, 'createOrder']);
            Route::post('subscription/verify-payment', [ApiSubscriptionController::class, 'verifyPayment']);
        });
});
