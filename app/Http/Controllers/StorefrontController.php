<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Items;
use App\Models\Vendor;
use App\Models\VendorLocation;
use App\Support\StorefrontCart;
use App\Support\StorefrontCatalog;
use App\Support\StorefrontContext;
use App\Support\StorefrontHome;
use App\Support\StorefrontPages;
use App\Support\StorefrontSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();

            return view('storefront.unavailable', ['vendor' => $vendor]);
        }

        if ($request->query('category')) {
            $category = Category::query()
                ->where('vendor_id', $ctx->vendor->id)
                ->where('id', (int) $request->query('category'))
                ->where('is_active', true)
                ->first();

            if ($category) {
                return redirect()->route('storefront.category', [$slug, $category->slug], 301);
            }
        }

        $vendor = $ctx->vendor;
        $store = $ctx->store;

        $categories = $this->storeCategories($vendor->id);
        $items = $this->storeItems($vendor->id);
        $locations = $this->storeLocations($vendor->id);

        return view('storefront.show', $ctx->viewData([
            'seo' => StorefrontSeo::forHome($vendor, $store),
            'isHomePage' => true,
            'isCategoryPage' => false,
            'categories' => $categories,
            'items' => $items,
            'allItems' => $items,
            'homeSections' => StorefrontHome::sections($vendor, $categories, $items),
            'locations' => $locations,
            'activeCategory' => null,
            'catalogItemsForJs' => StorefrontCatalog::itemsPayload($items),
        ]));
    }

    public function showCategory(string $slug, string $categorySlug): View|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();

            return view('storefront.unavailable', ['vendor' => $vendor]);
        }

        $vendor = $ctx->vendor;
        $store = $ctx->store;

        $pageCategory = Category::query()
            ->where('vendor_id', $vendor->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->whereHas('items', fn ($q) => $q->active()->available())
            ->first();

        if (! $pageCategory) {
            abort(404);
        }

        $categories = $this->storeCategories($vendor->id);
        $items = $this->storeItems($vendor->id)
            ->where('category_id', $pageCategory->id)
            ->values();

        return view('storefront.show', $ctx->viewData([
            'seo' => StorefrontSeo::forCategory($vendor, $store, $pageCategory, $items->count()),
            'isHomePage' => false,
            'isCategoryPage' => true,
            'pageCategory' => $pageCategory,
            'categories' => $categories,
            'items' => $items,
            'allItems' => $items,
            'homeSections' => [],
            'locations' => $this->storeLocations($vendor->id),
            'activeCategory' => $pageCategory->id,
            'catalogItemsForJs' => StorefrontCatalog::itemsPayload($items),
        ]));
    }

    public function showItem(Request $request, string $slug, Items $item): View
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();

            return view('storefront.unavailable', ['vendor' => $vendor]);
        }

        $vendor = $ctx->vendor;

        if ($item->vendor_id !== $vendor->id || ! $item->is_active || ! $item->is_available) {
            abort(404);
        }

        $item->load(['category', 'variantAttributes', 'variants']);

        return view('storefront.item-show', $ctx->viewData([
            'seo' => StorefrontSeo::forItem($vendor, $ctx->store, $item),
            'item' => $item,
            'catalogItemsForJs' => [StorefrontCatalog::itemPayload($item)],
        ]));
    }

    public function privacy(string $slug): View
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();

            return view('storefront.unavailable', ['vendor' => $vendor]);
        }

        if (! StorefrontPages::hasPrivacy($ctx->vendor)) {
            abort(404);
        }

        $content = StorefrontPages::content($ctx->vendor, \App\Models\VendorStorePage::KEY_PRIVACY);

        return view('storefront.privacy', $ctx->viewData([
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_privacy_policy'),
                $content,
                route('storefront.privacy', $slug),
            ),
        ]));
    }

    public function terms(string $slug): View
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();

            return view('storefront.unavailable', ['vendor' => $vendor]);
        }

        if (! StorefrontPages::hasTerms($ctx->vendor)) {
            abort(404);
        }

        $content = StorefrontPages::content($ctx->vendor, \App\Models\VendorStorePage::KEY_TERMS);

        return view('storefront.terms', $ctx->viewData([
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_terms_conditions'),
                $content,
                route('storefront.terms', $slug),
            ),
        ]));
    }

    public function about(string $slug): View
    {
        $ctx = $this->resolvePublished($slug);
        if (! StorefrontPages::hasAbout($ctx->vendor, $ctx->store)) {
            abort(404);
        }

        $aboutContent = StorefrontPages::aboutContent($ctx->vendor, $ctx->store);

        return view('storefront.about', $ctx->viewData([
            'aboutContent' => $aboutContent,
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_about_us'),
                $aboutContent,
                route('storefront.about', $slug),
            ),
        ]));
    }

    public function contact(string $slug): View
    {
        $ctx = $this->resolvePublished($slug);
        if (! StorefrontPages::hasContact($ctx->vendor, $ctx->store)) {
            abort(404);
        }

        $locations = $this->storeLocations($ctx->vendor->id);

        return view('storefront.contact', $ctx->viewData([
            'locations' => $locations,
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_contact_us'),
                StorefrontPages::content($ctx->vendor, \App\Models\VendorStorePage::KEY_CONTACT),
                route('storefront.contact', $slug),
            ),
        ]));
    }

    public function faq(string $slug): View
    {
        $ctx = $this->resolvePublished($slug);
        if (! StorefrontPages::hasFaq($ctx->vendor)) {
            abort(404);
        }

        $content = StorefrontPages::content($ctx->vendor, \App\Models\VendorStorePage::KEY_FAQ);

        return view('storefront.faq', $ctx->viewData([
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_faq'),
                $content,
                route('storefront.faq', $slug),
            ),
        ]));
    }

    public function returns(string $slug): View
    {
        $ctx = $this->resolvePublished($slug);
        if (! StorefrontPages::hasReturnPolicy($ctx->vendor)) {
            abort(404);
        }

        $content = StorefrontPages::content($ctx->vendor, \App\Models\VendorStorePage::KEY_RETURNS);

        return view('storefront.returns', $ctx->viewData([
            'seo' => StorefrontSeo::forPage(
                $ctx->vendor,
                $ctx->store,
                __('vendor.store_return_policy'),
                $content,
                route('storefront.returns', $slug),
            ),
        ]));
    }

    private function resolvePublished(string $slug): StorefrontContext
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            $vendor = Vendor::query()->where('slug', $slug)->first();
            if ($vendor) {
                abort(404);
            }

            abort(404);
        }

        return $ctx;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Category> */
    private function storeCategories(int $vendorId)
    {
        return Category::query()
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->whereHas('items', fn ($q) => $q->active()->available())
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Support\Collection<int, Items> */
    private function storeItems(int $vendorId)
    {
        return Items::query()
            ->where('vendor_id', $vendorId)
            ->active()
            ->available()
            ->with(['category', 'variantAttributes', 'variants'])
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, VendorLocation> */
    private function storeLocations(int $vendorId)
    {
        return VendorLocation::query()
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
