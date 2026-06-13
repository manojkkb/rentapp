@if($locations->isNotEmpty())
    <section class="mt-8 sm:mt-10">
        <h2 class="mb-4 text-lg font-bold text-gray-900">{{ __('vendor.store_tab_locations') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($locations as $location)
                <div class="{{ $theme['classes']['section'] }} bg-white p-5 transition hover:shadow-md">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-gray-900">{{ $location->name }}</h3>
                        @if($location->is_default)
                            <span class="shrink-0 rounded-full store-accent-bg-soft px-2 py-0.5 text-[10px] font-bold store-accent-text-dark">{{ __('vendor.store_default_location') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $location->full_address }}</p>
                    @if($location->phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $location->phone) }}" class="mt-3 inline-block text-sm font-medium store-accent-text">{{ $location->phone }}</a>
                    @endif
                    @if($location->latitude && $location->longitude)
                        <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                           target="_blank" rel="noopener"
                           class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold store-accent-text hover:underline">
                            <i class="fas fa-directions" aria-hidden="true"></i>
                            {{ __('vendor.store_view_on_map') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
