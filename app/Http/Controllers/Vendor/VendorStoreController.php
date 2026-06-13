<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Models\Vendor;
use App\Models\VendorLocation;
use App\Models\VendorStorePage;
use App\Models\VendorStoreSetting;
use App\Support\StorefrontRichText;
use App\Support\StorefrontPublish;
use App\Support\StorefrontTheme;
use App\Support\StorefrontGoogleFonts;
use App\Support\StorefrontThemePalette;
use App\Support\StorefrontThemeTemplate;
use App\Support\VendorStorePageCatalog;
use App\Support\VendorStoreSections;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $tab = $request->query('tab', 'general');
        if (VendorStoreSections::isValid($tab)) {
            return redirect()->route(VendorStoreSections::routeFor($tab));
        }

        return redirect()->route('vendor.store.general');
    }

    public function general(): View|RedirectResponse
    {
        return $this->showSection('general');
    }

    public function address(): View|RedirectResponse
    {
        return $this->showSection('address');
    }

    public function pricing(): View|RedirectResponse
    {
        return $this->showSection('pricing');
    }

    public function delivery(): View|RedirectResponse
    {
        return $this->showSection('delivery');
    }

    public function locations(): View|RedirectResponse
    {
        return $this->showSection('locations');
    }

    public function banner(): View|RedirectResponse
    {
        return $this->showSection('banner');
    }

    public function theme(): View|RedirectResponse
    {
        return $this->showSection('theme');
    }

    public function seo(): View|RedirectResponse
    {
        return $this->showSection('seo');
    }

    public function pages(): View|RedirectResponse
    {
        return $this->showSection('pages');
    }

    public function editPage(string $pageKey): View|RedirectResponse
    {
        if (! VendorStorePageCatalog::isValidKey($pageKey)) {
            abort(404);
        }

        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);
        $vendor->load(['businessCategory', 'storeSettings']);
        $store->refresh();

        return view('vendor.store.page-edit', array_merge($this->sharedPageData($vendor, $store), [
            'section' => 'pages',
            'sectionTitle' => VendorStorePageCatalog::label($pageKey),
            'storeSections' => VendorStoreSections::all(),
            'pageForm' => VendorStorePageCatalog::editFormFor($vendor, $store, $pageKey),
        ]));
    }

    public function updatePage(Request $request, string $pageKey): RedirectResponse
    {
        if (! VendorStorePageCatalog::isValidKey($pageKey)) {
            abort(404);
        }

        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:50000'],
        ]);

        VendorStorePage::saveContent(
            $vendor->id,
            $pageKey,
            StorefrontRichText::sanitize($validated['content'] ?? null)
        );

        return redirect()
            ->route('vendor.store.pages.edit', $pageKey)
            ->with('success', __('vendor.store_page_saved', ['page' => VendorStorePageCatalog::label($pageKey)]));
    }

    private function showSection(string $section): View|RedirectResponse
    {
        if (! VendorStoreSections::isValid($section)) {
            abort(404);
        }

        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);
        $vendor->load(['businessCategory', 'storeSettings']);
        $store->refresh();

        if ($store->is_published && ! StorefrontPublish::canPublish($vendor, $store)) {
            $store->update(['is_published' => false]);
            $store->refresh();
            session()->flash('warning', __('vendor.store_auto_unpublished', [
                'items' => implode(', ', StorefrontPublish::missingRequirements($vendor, $store)),
            ]));
        }

        $sections = VendorStoreSections::all();
        $sectionConfig = $sections[$section];

        return view('vendor.store.section', array_merge($this->sharedPageData($vendor, $store), $this->sectionPageData($vendor, $store, $section), [
            'section' => $section,
            'sectionPartial' => $sectionConfig['partial'],
            'sectionTitle' => VendorStoreSections::label($section),
            'storeSections' => $sections,
        ]));
    }

    /** @return array<string, mixed> */
    private function sharedPageData(Vendor $vendor, VendorStoreSetting $store): array
    {
        return [
            'vendor' => $vendor,
            'store' => $store,
            'depositTypes' => VendorStoreSetting::SECURITY_DEPOSIT_TYPES,
            'storeUrl' => route('storefront.show', $vendor->slug),
            'storeIsLive' => StorefrontPublish::isLive($vendor, $store),
            'storeLiveBlockers' => StorefrontPublish::liveBlockers($vendor, $store),
        ];
    }

    /** @return array<string, mixed> */
    private function sectionPageData(Vendor $vendor, VendorStoreSetting $store, string $section): array
    {
        return match ($section) {
            'general' => [
                'categories' => BusinessCategory::query()->orderBy('name')->get(),
            ],
            'locations' => [
                'locations' => VendorLocation::query()
                    ->where('vendor_id', $vendor->id)
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(),
            ],
            'pages' => [
                'storePagesList' => VendorStorePageCatalog::listFor($vendor, $store),
            ],
            'theme' => [
                'themeTemplates' => VendorStoreSetting::THEME_TEMPLATES,
                'palettePresetsByTemplate' => collect(VendorStoreSetting::THEME_TEMPLATES)->mapWithKeys(function (string $template) {
                    $accent = StorefrontThemeTemplate::defaultAccent($template);

                    return [$template => collect(StorefrontThemePalette::MODES)->mapWithKeys(
                        fn (string $mode) => [$mode => StorefrontThemePalette::modePreset($mode, $template, $accent)]
                    )->all()];
                })->all(),
                'templateDefaultAccents' => StorefrontThemeTemplate::defaultAccents(),
                'templatePreviewGradients' => collect(VendorStoreSetting::THEME_TEMPLATES)->mapWithKeys(
                    fn (string $template) => [$template => StorefrontThemeTemplate::previewGradient($template)]
                )->all(),
                'paletteLabels' => StorefrontThemePalette::labels(),
                'paletteGroups' => StorefrontThemePalette::groups(),
                'paletteKeys' => StorefrontThemePalette::KEYS,
                'themeModes' => StorefrontThemePalette::MODES,
                'googleFonts' => StorefrontGoogleFonts::forPicker(),
                'googleFontLabels' => StorefrontGoogleFonts::labels(),
                'resolvedTheme' => StorefrontTheme::resolve($store),
            ],
            'banner' => [
                'bannerHeights' => VendorStoreSetting::BANNER_HEIGHTS,
                'bannerOverlays' => VendorStoreSetting::BANNER_OVERLAYS,
            ],
            default => [],
        };
    }

    private function redirectToSection(string $section, string $message): RedirectResponse
    {
        return redirect()
            ->route(VendorStoreSections::routeFor($section))
            ->with('success', $message);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_category_id' => ['required', 'exists:business_categories,id'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'business_phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'whatsapp_enabled' => ['sometimes', 'boolean'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'is_published' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $vendorData = [
            'name' => $validated['name'],
            'business_category_id' => $validated['business_category_id'],
            'gst_number' => $validated['gst_number'] ?? null,
        ];

        if ($vendor->name !== $validated['name']) {
            $vendorData['slug'] = Str::slug($validated['name']).'-'.$vendor->id;
        }

        if ($request->hasFile('logo')) {
            $this->deleteStoredImage($vendor->logo);
            $vendorData['logo'] = $this->storeImage($request->file('logo'), $vendor->id, 'brand', 'logo');
        }

        $vendor->update($vendorData);
        $vendor->refresh();

        $storePayload = [
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'] ?? null,
            'business_phone' => $validated['business_phone'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'business_email' => $validated['business_email'] ?? null,
            'website' => $validated['website'] ?? null,
        ];

        $wantsPublish = $request->boolean('is_published');
        $store->fill($storePayload);

        if ($wantsPublish) {
            $this->assertStoreReady($vendor, $store);
            $storePayload['is_published'] = true;
        } else {
            $storePayload['is_published'] = false;
        }

        $store->update($storePayload);

        return $this->redirectToSection('general', __('vendor.store_general_saved'));
    }

    public function updateAddress(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $validated = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $vendor->update($validated);

        return $this->redirectToSection('address', __('vendor.store_address_saved'));
    }

    public function updatePricing(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'default_security_deposit_type' => ['required', Rule::in(VendorStoreSetting::SECURITY_DEPOSIT_TYPES)],
            'default_security_deposit_value' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'show_prices_online' => ['sometimes', 'boolean'],
            'show_gst_online' => ['sometimes', 'boolean'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        $depositType = $validated['default_security_deposit_type'];
        $depositValue = $validated['default_security_deposit_value'] ?? null;

        if ($depositType !== 'none') {
            if ($depositValue === null || (float) $depositValue <= 0) {
                return back()
                    ->withInput()
                    ->withErrors(['default_security_deposit_value' => __('vendor.order_wizard_security_deposit_value_required')]);
            }
            if (in_array($depositType, ['order_amount', 'product_security_deposit'], true) && (float) $depositValue > 100) {
                return back()
                    ->withInput()
                    ->withErrors(['default_security_deposit_value' => __('vendor.order_wizard_security_deposit_pct_max')]);
            }
        } else {
            $depositValue = null;
        }

        $store->update([
            'default_security_deposit_type' => $depositType,
            'default_security_deposit_value' => $depositValue,
            'show_prices_online' => $request->boolean('show_prices_online'),
            'show_gst_online' => $request->boolean('show_gst_online'),
            'min_order_amount' => $validated['min_order_amount'] ?? null,
        ]);

        return $this->redirectToSection('pricing', __('vendor.store_pricing_saved'));
    }

    public function updateDelivery(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'pickup_enabled' => ['sometimes', 'boolean'],
            'delivery_enabled' => ['sometimes', 'boolean'],
            'default_delivery_charge' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'free_delivery_min_amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        if (! $request->boolean('pickup_enabled') && ! $request->boolean('delivery_enabled')) {
            return back()
                ->withInput()
                ->withErrors(['fulfillment' => __('vendor.store_fulfillment_required')]);
        }

        $store->update([
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'delivery_enabled' => $request->boolean('delivery_enabled'),
            'default_delivery_charge' => $validated['default_delivery_charge'] ?? 0,
            'free_delivery_min_amount' => $validated['free_delivery_min_amount'] ?? null,
        ]);

        return $this->redirectToSection('delivery', __('vendor.store_delivery_saved'));
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
        ]);

        $store->update($validated);

        return $this->redirectToSection('seo', __('vendor.store_seo_saved'));
    }

    public function updateBanner(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate([
            'banner_enabled' => ['sometimes', 'boolean'],
            'banner_title' => ['nullable', 'string', 'max:255'],
            'banner_subtitle' => ['nullable', 'string', 'max:500'],
            'banner_height' => ['required', Rule::in(VendorStoreSetting::BANNER_HEIGHTS)],
            'banner_overlay' => ['required', Rule::in(VendorStoreSetting::BANNER_OVERLAYS)],
            'banner_show_cta' => ['sometimes', 'boolean'],
            'banner_cta_text' => ['nullable', 'string', 'max:120'],
            'banner_cta_url' => ['nullable', 'url', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'remove_hero_image' => ['sometimes', 'boolean'],
        ]);

        $data = [
            'banner_enabled' => $request->boolean('banner_enabled'),
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_subtitle' => $validated['banner_subtitle'] ?? null,
            'banner_height' => $validated['banner_height'],
            'banner_overlay' => $validated['banner_overlay'],
            'banner_show_cta' => $request->boolean('banner_show_cta'),
            'banner_cta_text' => $validated['banner_cta_text'] ?? null,
            'banner_cta_url' => $validated['banner_cta_url'] ?? null,
        ];

        if ($request->boolean('remove_hero_image')) {
            $this->deleteStoredImage($store->hero_image);
            $data['hero_image'] = null;
        } elseif ($request->hasFile('hero_image')) {
            $this->deleteStoredImage($store->hero_image);
            $data['hero_image'] = $this->storeImage($request->file('hero_image'), $vendor->id, 'store', 'hero');
        }

        $store->update($data);

        return $this->redirectToSection('banner', __('vendor.store_banner_saved'));
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $store = $this->ensureStoreSettings($vendor);

        $validated = $request->validate(array_merge([
            'theme_template' => ['required', Rule::in(VendorStoreSetting::THEME_TEMPLATES)],
            'theme_mode' => ['required', Rule::in(StorefrontThemePalette::MODES)],
            'theme_font' => ['required', Rule::in(StorefrontGoogleFonts::keys())],
            'theme_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'reset_theme_colors' => ['sometimes', 'boolean'],
        ], $this->themeColorRules()));

        $data = [
            'theme_template' => $validated['theme_template'],
            'theme_mode' => $validated['theme_mode'],
            'theme_font' => $validated['theme_font'],
            'theme_accent_color' => $validated['theme_accent_color'],
        ];

        if ($request->boolean('reset_theme_colors')) {
            $data['theme_colors'] = null;
            foreach ([
                'theme_background_color', 'theme_surface_color', 'theme_header_color',
                'theme_button_color', 'theme_button_text_color', 'theme_link_color', 'theme_footer_color',
            ] as $legacyField) {
                $data[$legacyField] = null;
            }
        } elseif ($validated['theme_mode'] === 'custom') {
            $custom = [];
            foreach (StorefrontThemePalette::KEYS as $key) {
                $value = $validated['theme_colors'][$key] ?? null;
                if (is_string($value) && $value !== '') {
                    $custom[$key] = $value;
                }
            }
            $data['theme_colors'] = $custom !== [] ? $custom : null;
        } else {
            $data['theme_colors'] = null;
        }

        $store->update($data);

        return $this->redirectToSection('theme', __('vendor.store_theme_saved'));
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $validated = $this->validateLocation($request);
        $isFirst = ! VendorLocation::query()->where('vendor_id', $vendor->id)->exists();

        $location = VendorLocation::query()->create([
            ...$validated,
            'vendor_id' => $vendor->id,
            'is_default' => $isFirst || $request->boolean('is_default'),
        ]);

        if ($location->is_default) {
            $this->syncDefaultLocation($vendor, $location);
        }

        return $this->redirectToSection('locations', __('vendor.store_location_created'));
    }

    public function updateLocation(Request $request, VendorLocation $location): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor || $location->vendor_id !== $vendor->id) {
            abort(404);
        }

        $validated = $this->validateLocation($request);

        $location->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('is_default')) {
            $this->syncDefaultLocation($vendor, $location);
        }

        return $this->redirectToSection('locations', __('vendor.store_location_updated'));
    }

    public function destroyLocation(VendorLocation $location): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor || $location->vendor_id !== $vendor->id) {
            abort(404);
        }

        $locationId = $location->id;
        $wasDefault = $location->is_default;
        $location->delete();

        $store = $this->ensureStoreSettings($vendor);
        if ($store->primary_location_id === $locationId) {
            $store->update(['primary_location_id' => null]);
        }

        if ($wasDefault) {
            $next = VendorLocation::query()->where('vendor_id', $vendor->id)->orderBy('id')->first();
            if ($next) {
                $this->syncDefaultLocation($vendor, $next);
            }
        }

        return $this->redirectToSection('locations', __('vendor.store_location_deleted'));
    }

    public function setDefaultLocation(VendorLocation $location): RedirectResponse
    {
        $vendor = $this->currentVendor();
        if (! $vendor || $location->vendor_id !== $vendor->id) {
            abort(404);
        }

        $this->syncDefaultLocation($vendor, $location);

        return $this->redirectToSection('locations', __('vendor.store_location_default_set'));
    }

    private function currentVendor(): ?Vendor
    {
        return Auth::user()?->currentVendor();
    }

    private function ensureStoreSettings(Vendor $vendor): VendorStoreSetting
    {
        return VendorStoreSetting::query()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'theme_template' => 'classic',
                'theme_mode' => 'light',
                'theme_font' => 'inter',
                'theme_accent_color' => '#059669',
                'banner_enabled' => true,
                'banner_height' => 'medium',
                'banner_overlay' => 'gradient',
            ]
        );
    }

    /** @return array<string, mixed> */
    private function validateLocation(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function syncDefaultLocation(Vendor $vendor, VendorLocation $location): void
    {
        VendorLocation::query()
            ->where('vendor_id', $vendor->id)
            ->where('id', '!=', $location->id)
            ->update(['is_default' => false]);

        $location->update(['is_default' => true, 'is_active' => true]);

        $store = $this->ensureStoreSettings($vendor);
        $store->update(['primary_location_id' => $location->id]);
    }

    private function assertStoreReady(Vendor $vendor, VendorStoreSetting $store): void
    {
        $missing = StorefrontPublish::missingRequirements($vendor, $store);

        if ($missing !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'is_published' => [__('vendor.store_publish_requirements', ['items' => implode(', ', $missing)])],
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function themeColorRules(): array
    {
        $rules = ['theme_colors' => ['nullable', 'array']];
        foreach (StorefrontThemePalette::KEYS as $key) {
            $rules['theme_colors.'.$key] = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        return $rules;
    }

    private function storeImage(\Illuminate\Http\UploadedFile $file, int $vendorId, string $folder, string $prefix): string
    {
        $filename = $prefix.'_'.time().'_'.Str::random(8).'.'.$file->extension();
        $path = $file->storeAs(
            'vendors/'.$vendorId.'/'.$folder,
            $filename,
            ['disk' => 's3', 'visibility' => 'public']
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Could not upload image.');
        }

        return $path;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('s3')->delete($path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }
}
