<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Language;

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
    }
}
