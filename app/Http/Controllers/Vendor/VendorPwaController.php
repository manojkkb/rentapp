<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class VendorPwaController extends Controller
{
    /**
     * Web App Manifest for installable vendor PWA (public, no auth).
     */
    public function manifest(): JsonResponse
    {
        $startUrl = route('vendor.home', [], true);
        $homePath = parse_url($startUrl, PHP_URL_PATH) ?: '/vendor/home';
        $scopePath = str_replace('\\', '/', dirname($homePath));
        $scope = rtrim($scopePath, '/') . '/';

        return response()->json([
            'id' => $scope,
            'name' => config('app.name', 'RentApp') . ' — Vendor',
            'short_name' => config('app.name', 'RentApp'),
            'description' => __('vendor.pwa_description'),
            'lang' => str_replace('_', '-', app()->getLocale()),
            'dir' => 'auto',
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'portrait-primary',
            'background_color' => '#f9fafb',
            'theme_color' => '#059669',
            'icons' => [
                [
                    'src' => asset('vendor/icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('vendor/icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('vendor/icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
