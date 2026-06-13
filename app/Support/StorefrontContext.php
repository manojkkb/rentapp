<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Vendor;
use App\Models\VendorStoreSetting;

final class StorefrontContext
{
    public function __construct(
        public readonly Vendor $vendor,
        public readonly VendorStoreSetting $store,
        public readonly array $theme,
    ) {}

    public static function resolve(string $slug): ?self
    {
        $vendor = Vendor::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['businessCategory', 'storeSettings'])
            ->first();

        if (! $vendor) {
            return null;
        }

        $store = $vendor->storeSettings;
        if (! $store || ! $store->is_published || ! StorefrontPublish::canPublish($vendor, $store)) {
            return null;
        }

        return new self(
            $vendor,
            $store,
            StorefrontTheme::resolve($store),
        );
    }

    public function slug(): string
    {
        return $this->vendor->slug;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function viewData(array $extra = []): array
    {
        $cart = StorefrontCart::forVendor($this->vendor->id);
        $booking = StorefrontBooking::forVendor($this->vendor->id);
        $rentalHint = $booking->isSet()
            ? ($booking->presentation()['start_label'].' → '.$booking->presentation()['end_label'])
            : null;

        return array_merge([
            'vendor' => $this->vendor,
            'store' => $this->store,
            'theme' => $this->theme,
            'cartCount' => $cart->count(),
            'storeUrl' => route('storefront.show', $this->slug()),
            'whatsappEnabled' => StorefrontWhatsApp::isEnabled($this->store),
            'whatsappNumber' => StorefrontWhatsApp::displayNumber($this->store),
            'whatsappContactUrl' => StorefrontWhatsApp::url(
                $this->store,
                StorefrontWhatsApp::contactMessage($this->vendor),
            ),
            'whatsappRentalHint' => $rentalHint,
            'categories' => Category::query()
                ->where('vendor_id', $this->vendor->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'booking' => $booking->presentation(),
            'hasBookingDates' => $booking->isSet(),
            'bookingDefaultsByPriceType' => $booking->billingDefaultsByPriceType(),
            'bookingSaveUrl' => route('storefront.booking.save', $this->slug()),
            'cartAddUrl' => route('storefront.cart.add', $this->slug()),
            'banner' => StorefrontBanner::resolve($this->store, $this->vendor),
        ], $extra);
    }
}
