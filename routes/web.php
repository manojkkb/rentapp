<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\BusinessCategoryController as AdminBusinessCategoryController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Vendor\AuthVendorCtrl;
use App\Http\Controllers\Vendor\CategoryController;
use App\Http\Controllers\Vendor\ItemController;
use App\Http\Controllers\Vendor\ReviewController;
use App\Http\Controllers\Vendor\StaffController;
use App\Http\Controllers\Vendor\StaffPermissionController;
use App\Http\Controllers\Vendor\SubscriptionVendorController;
use App\Http\Controllers\Vendor\VendorCalendarController;
use App\Http\Controllers\Vendor\GlobalSearchController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorCouponController;
use App\Http\Controllers\Vendor\VendorCustomerController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Vendor\VendorPwaController;
use App\Http\Controllers\Vendor\VendorSupportController;
use App\Http\Controllers\WelcomeCtrl;
use App\Http\Middleware\AdminAuthMidddleware;
use App\Http\Middleware\VendorAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeCtrl::class, 'index'])->name('welcome');

// Customer Routes
Route::get('/home', [HomeController::class, 'index'])->name('customer.home');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware([AdminAuthMidddleware::class])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/revenue', [DashboardController::class, 'revenue'])->name('dashboard.revenue');
        Route::get('/dashboard/bookings', [DashboardController::class, 'bookings'])->name('dashboard.bookings');
        Route::get('/dashboard/vendors', [DashboardController::class, 'vendors'])->name('dashboard.vendors');
        Route::get('/dashboard/cities', [DashboardController::class, 'cities'])->name('dashboard.cities');

        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/customers', [UserManagementController::class, 'customers'])->name('customers');
            Route::get('/vendor-accounts', [UserManagementController::class, 'vendorAccounts'])->name('vendor-accounts');
            Route::get('/admins', [UserManagementController::class, 'admins'])->name('admins');
            Route::get('/kyc', [UserManagementController::class, 'kyc'])->name('kyc');
            Route::patch('/kyc/{vendor}/approve', [UserManagementController::class, 'approveKyc'])->name('kyc.approve');
            Route::get('/suspended', [UserManagementController::class, 'suspended'])->name('suspended');
            Route::patch('/vendors/{vendor}/toggle-active', [UserManagementController::class, 'toggleVendorActive'])->name('vendors.toggle-active');
            Route::patch('/staff/{vendorUser}/toggle-active', [UserManagementController::class, 'toggleStaffActive'])->name('staff.toggle-active');
            Route::get('/login-activity', [UserManagementController::class, 'loginActivity'])->name('login-activity');
        });

        Route::post('vendors/{vendor}/subscription/upgrade', [AdminVendorController::class, 'upgradeSubscription'])->name('vendors.subscription.upgrade');
        Route::resource('vendors', AdminVendorController::class);

        Route::resource('business-categories', AdminBusinessCategoryController::class)->except(['show']);

        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [AdminSubscriptionController::class, 'index'])->name('index');
            Route::resource('plans', AdminSubscriptionPlanController::class)->except(['show']);
        });

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [AdminSupportController::class, 'index'])->name('index');
            Route::get('/{conversation}', [AdminSupportController::class, 'show'])->name('show');
            Route::post('/{conversation}/messages', [AdminSupportController::class, 'storeMessage'])->name('messages.store');
        });
    });
});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('manifest.webmanifest', [VendorPwaController::class, 'manifest'])->name('manifest');

    Route::get('login', [AuthVendorCtrl::class, 'loginForm'])->name('login');
    Route::post('login', [AuthVendorCtrl::class, 'login'])->name('login.submit');
    Route::post('otp/send', [AuthVendorCtrl::class, 'sendOTP'])->name('otp.send');
    Route::post('otp/verify', [AuthVendorCtrl::class, 'verifyOTP'])->name('otp.verify');

    // Vendor selection and creation (after OTP/login, before vendor selection)
    Route::middleware(['auth'])->group(function () {
        Route::get('select', [AuthVendorCtrl::class, 'selectVendorForm'])->name('select');
        Route::post('select', [AuthVendorCtrl::class, 'selectVendor'])->name('select.submit');
        Route::get('create', [AuthVendorCtrl::class, 'createVendorForm'])->name('create');
        Route::post('create', [AuthVendorCtrl::class, 'storeVendor'])->name('create.submit');
    });

    Route::post('logout', [AuthVendorCtrl::class, 'logout'])->name('logout');

    Route::middleware([VendorAuthMiddleware::class, 'vendor.subscription', 'vendor.permission'])->group(function () {

        Route::get('/home', [VendorController::class, 'home'])->name('home');
        Route::get('/dashboard/stats', [VendorController::class, 'getDashboardStats'])->name('dashboard.stats');
        Route::get('/search', GlobalSearchController::class)->name('search');

        // Profile
        Route::get('/profile', [VendorController::class, 'profile'])->name('profile');
        Route::put('/profile', [VendorController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/personal', [VendorController::class, 'updatePersonalProfile'])->name('profile.update.personal');
        Route::put('/profile/business', [VendorController::class, 'updateBusinessProfile'])->name('profile.update.business');

        // Language Switcher
        Route::post('/language/switch', [VendorController::class, 'switchLanguage'])->name('language.switch');

        // Staff Management
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::post('staff/{staff}/toggle', [StaffController::class, 'toggleStatus'])->name('staff.toggle');

        // Staff roles & permissions
        Route::get('staff-permissions', [StaffPermissionController::class, 'index'])->name('staff-permissions.index');
        Route::post('staff-permissions', [StaffPermissionController::class, 'store'])->name('staff-permissions.store');
        Route::put('staff-permissions/{staffPermission}', [StaffPermissionController::class, 'update'])->name('staff-permissions.update');
        Route::delete('staff-permissions/{staffPermission}', [StaffPermissionController::class, 'destroy'])->name('staff-permissions.destroy');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::post('categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'subcategories'])->name('categories.subcategories');

        // Items
        Route::get('items/fetch', [ItemController::class, 'fetchItems'])->name('items.fetch');
        Route::post('items/quick-store', [ItemController::class, 'storeQuickForOrderWizard'])->name('items.quick-store');
        Route::resource('items', ItemController::class);
        Route::post('items/{item}/toggle', [ItemController::class, 'toggleStatus'])->name('items.toggle');
        Route::post('items/{item}/availability', [ItemController::class, 'toggleAvailability'])->name('items.availability');

        // Customers
        Route::resource('customers', VendorCustomerController::class)->except(['show', 'destroy']);
        Route::post('customers/{customer}/toggle', [VendorCustomerController::class, 'toggleStatus'])->name('customers.toggle');

        Route::resource('coupons', VendorCouponController::class);
        Route::post('coupons/{coupon}/toggle', [VendorCouponController::class, 'toggleStatus'])->name('coupons.toggle');

        // Calendar
        Route::get('calendar', [VendorCalendarController::class, 'index'])->name('calendar');
        Route::get('calendar/events', [VendorCalendarController::class, 'events'])->name('calendar.events');

        // Orders
        Route::get('deliveries', [VendorOrderController::class, 'deliveries'])->name('deliveries.index');
        Route::get('returns', [VendorOrderController::class, 'returns'])->name('returns.index');
        Route::get('orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/new', [VendorOrderController::class, 'createNew'])->name('orders.new');
        Route::get('orders/create', fn () => redirect()->route('vendor.orders.new'))->name('orders.create');
        Route::post('orders/create/step-1', [VendorOrderController::class, 'storeWizardStep1'])->name('orders.create.step1');
        Route::get('orders/create/items', fn () => redirect()->route('vendor.orders.new', ['step' => 2]))->name('orders.create.items');
        Route::post('orders/create/step-2', [VendorOrderController::class, 'storeWizardStep2'])->name('orders.create.step2');
        Route::get('orders/create/checkout', fn () => redirect()->route('vendor.orders.new', ['step' => 3]))->name('orders.create.checkout');
        Route::get('orders/create/summary', fn () => redirect()->route('vendor.orders.new', ['step' => 3]))->name('orders.create.summary');
        Route::post('orders/create/summary/line', [VendorOrderController::class, 'updateWizardSummaryLine'])->name('orders.create.summary.update-line');
        Route::post('orders/create/summary/line-remove', [VendorOrderController::class, 'removeWizardSummaryLine'])->name('orders.create.summary.remove-line');
        Route::get('orders/create/fulfillment', fn () => redirect()->route('vendor.orders.new', ['step' => 4]))->name('orders.create.fulfillment');
        Route::post('orders/create/fulfillment', [VendorOrderController::class, 'storeWizardFulfillment'])->name('orders.create.fulfillment.store');
        Route::get('orders/create/payment', fn () => redirect()->route('vendor.orders.new', ['step' => 5]))->name('orders.create.payment');
        Route::post('orders/create/complete', [VendorOrderController::class, 'storeWizardComplete'])->name('orders.create.complete');
        Route::get('orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/print', [VendorOrderController::class, 'printOrder'])->name('orders.print');
        Route::get('orders/{order}/invoice/download', [VendorOrderController::class, 'downloadInvoice'])->name('orders.invoice.download');
        Route::put('orders/{order}', [VendorOrderController::class, 'update'])->name('orders.update');
        Route::put('orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('orders/{order}/rental-status', [VendorOrderController::class, 'updateOrderRentalStatus'])->name('orders.rental-status');

        Route::patch('orders/{order}/fulfillment', [VendorOrderController::class, 'updateOrderFulfillment'])->name('orders.fulfillment');
        Route::patch('orders/{order}/booking', [VendorOrderController::class, 'updateOrderBooking'])->name('orders.booking');
        Route::post('orders/{order}/items', [VendorOrderController::class, 'addOrderLine'])->name('orders.items.add');
        Route::put('orders/{order}/items/{item}', [VendorOrderController::class, 'updateOrderLine'])->name('orders.items.update');
        Route::delete('orders/{order}/items/{item}', [VendorOrderController::class, 'removeOrderLine'])->name('orders.items.remove');
        Route::post('orders/{order}/discount', [VendorOrderController::class, 'applyOrderDiscount'])->name('orders.discount');
        Route::delete('orders/{order}/discount', [VendorOrderController::class, 'removeOrderDiscount'])->name('orders.discount.remove');
        Route::post('orders/{order}/coupon', [VendorOrderController::class, 'applyOrderCoupon'])->name('orders.coupon.apply');
        Route::delete('orders/{order}/coupon', [VendorOrderController::class, 'removeOrderCoupon'])->name('orders.coupon.remove');
        Route::get('orders/{order}/coupons', [VendorOrderController::class, 'listOrderCoupons'])->name('orders.coupons.list');
        Route::post('orders/{order}/payment', [VendorOrderController::class, 'recordOrderPayment'])->name('orders.payment');
        Route::delete('orders/{order}/payments/{paymentIndex}', [VendorOrderController::class, 'removeOrderPayment'])
            ->name('orders.payments.destroy')
            ->whereNumber('paymentIndex');
        Route::post('orders/{order}/extra-charges', [VendorOrderController::class, 'addOrderExtraCharge'])->name('orders.extra-charges');
        Route::delete('orders/{order}/extra-charges/{lineIndex}', [VendorOrderController::class, 'removeOrderExtraCharge'])
            ->name('orders.extra-charges.destroy')
            ->whereNumber('lineIndex');
        Route::post('orders/{order}/security-deposit', [VendorOrderController::class, 'applyOrderSecurityDeposit'])->name('orders.security-deposit');

        // Reviews
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');

        Route::get('subscription/plans', [SubscriptionVendorController::class, 'subscriptionPlans'])->name('subscription.plans');
        Route::post('subscription/create-order', [SubscriptionVendorController::class, 'createOrder'])->name('subscription.create-order');
        Route::post('subscription/verify-payment', [SubscriptionVendorController::class, 'verifyPayment'])->name('subscription.verify-payment');

        Route::get('support', [VendorSupportController::class, 'index'])->name('support');
        Route::post('support/messages', [VendorSupportController::class, 'storeMessage'])->name('support.messages.store');
        Route::get('support/socket-token', [VendorSupportController::class, 'socketToken'])->name('support.socket-token');
    });
});
