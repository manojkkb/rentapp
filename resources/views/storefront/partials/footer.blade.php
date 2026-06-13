@php
    use App\Support\StorefrontPages;
    $footerLinks = StorefrontPages::footerLinks($vendor, $store);
@endphp
<footer class="store-footer-bg mt-10 border-t border-gray-200 py-8 sm:py-10 {{ $store->business_phone ? 'mb-0 sm:mb-0' : '' }}">
    <div class="store-site-container">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="text-center sm:text-left">
                <p class="font-semibold text-gray-900">{{ $vendor->name }}</p>
                @if($store->tagline)
                    <p class="mt-1 text-sm text-gray-500">{{ $store->tagline }}</p>
                @endif
                @if($vendor->city)
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-1 text-xs" aria-hidden="true"></i>
                        {{ $vendor->city }}{{ $vendor->state ? ', '.$vendor->state : '' }}
                    </p>
                @endif
            </div>

            <nav class="text-center sm:text-left" aria-label="{{ __('vendor.store_footer_links') }}">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.store_footer_links') }}</p>
                <ul class="space-y-2 text-sm">
                    @foreach($footerLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="store-link inline-flex items-center gap-1.5 hover:underline {{ ($link['active'] ?? false) ? 'font-semibold' : '' }}">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="text-center sm:text-left lg:text-right">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.store_footer_contact') }}</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    @if(($whatsappEnabled ?? false) && ($whatsappContactUrl ?? null))
                        <li>
                            <a href="{{ $whatsappContactUrl }}" target="_blank" rel="noopener noreferrer"
                               class="store-link inline-flex items-center gap-1.5 font-medium text-[#128C7E] hover:underline">
                                <i class="fab fa-whatsapp text-xs" aria-hidden="true"></i>
                                {{ __('vendor.store_whatsapp_chat') }}
                            </a>
                        </li>
                    @endif
                    @if($store->business_phone)
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" class="store-link inline-flex items-center gap-1.5 hover:underline">
                                <i class="fas fa-phone text-xs" aria-hidden="true"></i>
                                {{ $store->business_phone }}
                            </a>
                        </li>
                    @endif
                    @if($store->business_email)
                        <li>
                            <a href="mailto:{{ $store->business_email }}" class="store-link inline-flex items-center gap-1.5 hover:underline">
                                <i class="fas fa-envelope text-xs" aria-hidden="true"></i>
                                {{ $store->business_email }}
                            </a>
                        </li>
                    @endif
                    @if(StorefrontPages::hasContact($vendor, $store))
                        <li>
                            <a href="{{ route('storefront.contact', $vendor->slug) }}" class="store-link inline-flex items-center gap-1.5 font-medium hover:underline">
                                <i class="fas fa-headset text-xs" aria-hidden="true"></i>
                                {{ __('vendor.store_contact_us') }}
                            </a>
                        </li>
                    @endif
                    @if($store->website)
                        <li>
                            <a href="{{ $store->website }}" target="_blank" rel="noopener noreferrer" class="store-link inline-flex items-center gap-1.5 hover:underline">
                                <i class="fas fa-globe text-xs" aria-hidden="true"></i>
                                {{ __('vendor.store_website') }}
                            </a>
                        </li>
                    @endif
                    @if(! $store->business_phone && ! $store->business_email && ! $store->website && ! StorefrontPages::hasContact($vendor, $store))
                        <li class="text-gray-400">{{ __('vendor.store_footer_no_contact') }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-8 flex flex-col items-center gap-2 border-t border-gray-200/80 pt-6 text-center text-xs text-gray-400 sm:flex-row sm:justify-between sm:text-left">
            <p>&copy; {{ date('Y') }} {{ $vendor->name }}</p>
            <p>
                {{ __('vendor.store_public_powered_by') }}
                <a href="{{ route('welcome') }}" class="store-link hover:underline">Rentkia</a>
            </p>
        </div>
    </div>
</footer>
