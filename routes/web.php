<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Vendor\AuthVendorCtrl;
use App\Http\Controllers\Vendor\CategoryController;
use App\Http\Controllers\Vendor\ItemController;
use App\Http\Controllers\Vendor\ReviewController;
use App\Http\Controllers\Vendor\StaffController;
use App\Http\Controllers\Vendor\SubscriptionVendorController;
use App\Http\Controllers\Vendor\VendorCartController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorCouponController;
use App\Http\Controllers\Vendor\VendorCalendarController;
use App\Http\Controllers\Vendor\VendorCustomerController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorPwaController;
use App\Http\Controllers\WelcomeCtrl;
use App\Http\Middleware\AdminAuthMidddleware;
use App\Http\Middleware\VendorAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeCtrl::class, 'index'])->name('welcome');

// Customer Routes
Route::get('/home', [HomeController::class, 'index'])->name('customer.home');

Route::prefix('admin')->name('admin.')->group(function () 
{
    
    Route::get('login',[AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login',[AuthController::class, 'login'])->name('login.submit');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');

    Route::middleware([AdminAuthMidddleware::class])->group(function () {

        Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    });
});

Route::prefix('vendor')->name('vendor.')->group(function () 
{
    Route::get('manifest.webmanifest', [VendorPwaController::class, 'manifest'])->name('manifest');

    Route::get('login',[AuthVendorCtrl::class, 'loginForm'])->name('login');
    Route::post('login',[AuthVendorCtrl::class, 'login'])->name('login.submit');
    Route::post('otp/send',[AuthVendorCtrl::class, 'sendOTP'])->name('otp.send');
    Route::post('otp/verify',[AuthVendorCtrl::class, 'verifyOTP'])->name('otp.verify');
    
    // Vendor selection and creation (after OTP/login, before vendor selection)
    Route::middleware(['auth'])->group(function () {
        Route::get('select',[AuthVendorCtrl::class, 'selectVendorForm'])->name('select');
        Route::post('select',[AuthVendorCtrl::class, 'selectVendor'])->name('select.submit');
        Route::get('create',[AuthVendorCtrl::class, 'createVendorForm'])->name('create');
        Route::post('create',[AuthVendorCtrl::class, 'storeVendor'])->name('create.submit');
    });
    
    Route::post('logout',[AuthVendorCtrl::class, 'logout'])->name('logout');

    Route::middleware([VendorAuthMiddleware::class])->group(function () {

        Route::get('/home',[VendorController::class, 'home'])->name('home');
        Route::get('/dashboard/stats',[VendorController::class, 'getDashboardStats'])->name('dashboard.stats');

        // Profile
        Route::get('/profile',[VendorController::class, 'profile'])->name('profile');
        Route::put('/profile',[VendorController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/personal',[VendorController::class, 'updatePersonalProfile'])->name('profile.update.personal');
        Route::put('/profile/business',[VendorController::class, 'updateBusinessProfile'])->name('profile.update.business');

        // Language Switcher
        Route::post('/language/switch',[VendorController::class, 'switchLanguage'])->name('language.switch');

        // Staff Management
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::post('staff/{id}/toggle', [StaffController::class, 'toggleStatus'])->name('staff.toggle');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::post('categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'subcategories'])->name('categories.subcategories');

        // Items
        Route::get('items/fetch', [ItemController::class, 'fetchItems'])->name('items.fetch');
        Route::resource('items', ItemController::class)->except(['show']);
        Route::post('items/{item}/toggle', [ItemController::class, 'toggleStatus'])->name('items.toggle');
        Route::post('items/{item}/availability', [ItemController::class, 'toggleAvailability'])->name('items.availability');

        // Customers
        Route::resource('customers', VendorCustomerController::class)->except(['show']);

        // Carts
        Route::resource('carts', VendorCartController::class);

        Route::resource('coupons', VendorCouponController::class);
        Route::post('coupons/{coupon}/toggle', [VendorCouponController::class, 'toggleStatus'])->name('coupons.toggle');

        // Calendar
        Route::get('calendar', [VendorCalendarController::class, 'index'])->name('calendar');
        Route::get('calendar/events', [VendorCalendarController::class, 'events'])->name('calendar.events');
        
        // Cart Items Management
        Route::post('carts/{cart}/items', [VendorCartController::class, 'addItem'])->name('carts.items.add');
        Route::put('carts/{cart}/items/{item}', [VendorCartController::class, 'updateItem'])->name('carts.items.update');
        Route::delete('carts/{cart}/items/{item}', [VendorCartController::class, 'removeItem'])->name('carts.items.remove');
        Route::delete('carts/{cart}/empty', [VendorCartController::class, 'emptyCart'])->name('carts.empty');
        Route::post('carts/{cart}/discount', [VendorCartController::class, 'applyDiscount'])->name('carts.discount');
        Route::delete('carts/{cart}/discount', [VendorCartController::class, 'removeDiscount'])->name('carts.discount.remove');
        Route::post('carts/{cart}/coupon', [VendorCartController::class, 'applyCoupon'])->name('carts.coupon.apply');
        Route::delete('carts/{cart}/coupon', [VendorCartController::class, 'removeCoupon'])->name('carts.coupon.remove');
        Route::get('carts/{cart}/coupons', [VendorCartController::class, 'listCoupons'])->name('carts.coupons.list');
        
        // Place Order from Cart
        Route::patch('carts/{cart}/fulfillment', [VendorCartController::class, 'updateFulfillment'])->name('carts.fulfillment');
        Route::post('carts/{cart}/payment', [VendorCartController::class, 'recordPayment'])->name('carts.payment');
        Route::delete('carts/{cart}/payments/{paymentIndex}', [VendorCartController::class, 'removePayment'])
            ->name('carts.payments.destroy')
            ->whereNumber('paymentIndex');
        Route::post('carts/{cart}/security-deposit', [VendorCartController::class, 'applySecurityDeposit'])->name('carts.security-deposit');
        Route::post('carts/{cart}/place-order', [VendorCartController::class, 'placeOrder'])->name('carts.place-order');
        Route::get('carts/{cart}/quote', [VendorCartController::class, 'quote'])->name('carts.quote');
        Route::get('carts/{cart}/quote/download', [VendorCartController::class, 'downloadQuote'])->name('carts.quote.download');
        Route::get('carts/{cart}/print', [VendorCartController::class, 'printCart'])->name('carts.print');
        
        // Orders
        Route::get('orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.update-status');
        
        // Reviews
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');

         Route::get('subscription/plans', [SubscriptionVendorController::class, 'subscriptionPlans'])->name('subscription.plans');
         Route::post('subscription/create-order', [SubscriptionVendorController::class, 'createOrder'])->name('subscription.create-order');
         Route::post('subscription/verify-payment', [SubscriptionVendorController::class, 'verifyPayment'])->name('subscription.verify-payment');        
    });
});
