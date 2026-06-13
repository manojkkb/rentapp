@if($store->description || $vendor->full_address)
    <section class="mt-10 sm:mt-12">
        <div class="{{ $theme['classes']['section'] }} overflow-hidden bg-white">
            <div class="grid md:grid-cols-2">
                <div class="p-5 sm:p-6">
                    <h2 class="mb-3 text-lg font-bold text-gray-900">{{ __('vendor.store_public_about') }}</h2>
                    @if($store->description)
                        <p class="text-sm leading-relaxed text-gray-600 sm:text-base">{{ $store->description }}</p>
                    @endif
                </div>
                <div class="store-accent-bg-soft border-t border-gray-100 p-5 sm:p-6 md:border-l md:border-t-0">
                    @if($vendor->full_address)
                        <p class="flex items-start gap-2 text-sm text-gray-700">
                            <i class="fas fa-map-marker-alt mt-0.5 shrink-0 store-accent-text" aria-hidden="true"></i>
                            <span>{{ $vendor->full_address }}</span>
                        </p>
                    @endif
                    @if($store->business_email)
                        <p class="mt-3 text-sm text-gray-700">
                            <i class="fas fa-envelope mr-2 text-gray-400" aria-hidden="true"></i>
                            <a href="mailto:{{ $store->business_email }}" class="store-accent-text hover:underline">{{ $store->business_email }}</a>
                        </p>
                    @endif
                    @if($store->website)
                        <p class="mt-3 text-sm">
                            <a href="{{ $store->website }}" target="_blank" rel="noopener" class="store-accent-text hover:underline">
                                <i class="fas fa-globe mr-2 text-gray-400" aria-hidden="true"></i>{{ parse_url($store->website, PHP_URL_HOST) ?: $store->website }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
