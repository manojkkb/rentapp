<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\SupportConversation;
use App\Models\Vendor;
use App\Support\VendorAccess;
use App\Support\VendorSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('local') && ! $this->app->runningInConsole()) {
            URL::forceRootUrl($this->app['request']->getSchemeAndHttpHost());
        }

        // Share languages with all views
        View::composer('*', function ($view) {
            $languages = Language::active()->orderBy('sort_order')->get();
            
            // Get current language from session, user, or default
            $currentLanguage = session('language');
            
            if (!$currentLanguage && Auth::check()) {
                $currentLanguage = Auth::user()->language ?? 'en';
            }
            
            if (!$currentLanguage) {
                $currentLanguage = 'en';
            }
            
            // Set app locale
            app()->setLocale($currentLanguage);
            
            $view->with([
                'availableLanguages' => $languages,
                'currentLanguage' => $currentLanguage,
            ]);
        });

        View::composer(['admin.layouts.sidebar'], function ($view) {
            $view->with('pendingKycCount', Vendor::where('is_verified', false)->count());
            $view->with(
                'openSupportTicketsCount',
                SupportConversation::query()->where('status', SupportConversation::STATUS_OPEN)->count()
            );
        });

        View::composer(['vendor.layouts.*', 'vendor.*'], function ($view) {
            $access = VendorAccess::current();
            $view->with('vendorAccess', $access);
            $view->with('vendorCan', fn (string $permission) => $access?->can($permission) ?? false);
            $view->with('vendorCanAny', fn (array $permissions) => $access?->canAny($permissions) ?? false);
            $view->with('vendorIsOwner', $access?->isOwner() ?? false);

            $vendor = Auth::user()?->currentVendor();
            if ($vendor) {
                $view->with('vendorSubscriptionStatus', VendorSubscription::status($vendor));
                $view->with('vendorTrialDaysRemaining', VendorSubscription::trialDaysRemaining($vendor));
                $view->with('vendorTrialEndsAt', VendorSubscription::trialEndsAt($vendor));
            }
        });

        Blade::if('vendorCan', fn (string $permission) => VendorAccess::current()?->can($permission) ?? false);
        Blade::if('vendorCanAny', fn (...$permissions) => VendorAccess::current()?->canAny($permissions) ?? false);
    }
}
