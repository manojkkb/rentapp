@php
    $locationsJson = $locations->map(fn ($l) => [
        'id' => $l->id,
        'name' => $l->name,
        'address_line1' => $l->address_line1,
        'address_line2' => $l->address_line2,
        'city' => $l->city,
        'state' => $l->state,
        'postal_code' => $l->postal_code,
        'country' => $l->country,
        'phone' => $l->phone,
        'latitude' => $l->latitude,
        'longitude' => $l->longitude,
        'is_default' => $l->is_default,
        'is_active' => $l->is_active,
        'update_url' => route('vendor.store.locations.update', $l),
    ])->values();
@endphp

<div x-data="vendorStoreLocations({
    storeUrl: @js(route('vendor.store.locations.store')),
    locationCount: @js($locations->count()),
    defaultCountry: @js($vendor->country ?: 'India'),
    defaultPhone: @js($store->business_phone ?? ''),
    vendorAddress: @js([
        'address_line1' => $vendor->address_line1,
        'address_line2' => $vendor->address_line2,
        'city' => $vendor->city,
        'state' => $vendor->state,
        'postal_code' => $vendor->postal_code,
        'country' => $vendor->country ?: 'India',
        'latitude' => $vendor->latitude,
        'longitude' => $vendor->longitude,
    ]),
    labels: {
        geoUnsupported: @js(__('vendor.store_geo_unsupported')),
        geoDenied: @js(__('vendor.store_geo_denied')),
    },
})" class="relative space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600">{{ __('vendor.store_locations_help') }}</p>
        <button type="button" @click="openAdd()"
                class="inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:h-10 sm:w-auto">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.store_add_location') }}
        </button>
    </div>

    @if($locations->isNotEmpty())
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($locations as $location)
                <div class="rounded-xl border border-gray-200 p-4 {{ $location->is_default ? 'ring-2 ring-emerald-500/30' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate font-semibold text-gray-900">{{ $location->name }}</h3>
                                @if($location->is_default)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ __('vendor.store_default_location') }}</span>
                                @endif
                                @unless($location->is_active)
                                    <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ __('vendor.inactive') }}</span>
                                @endunless
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $location->full_address }}</p>
                            @if($location->phone)
                                <p class="mt-1 text-sm text-gray-500"><i class="fas fa-phone text-xs"></i> {{ $location->phone }}</p>
                            @endif
                            @if($location->latitude && $location->longitude)
                                <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                                   target="_blank" rel="noopener"
                                   class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:text-emerald-800">
                                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                    {{ __('vendor.store_view_on_map') }}
                                </a>
                            @else
                                <p class="mt-2 text-xs text-amber-700">{{ __('vendor.store_no_geo_pin') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-100 pt-3">
                        @unless($location->is_default)
                            <form action="{{ route('vendor.store.locations.default', $location) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    {{ __('vendor.store_set_default') }}
                                </button>
                            </form>
                        @endunless
                        <button type="button"
                                @click="openEdit(@js($locationsJson->firstWhere('id', $location->id)))"
                                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
                            {{ __('vendor.edit') }}
                        </button>
                        <form action="{{ route('vendor.store.locations.destroy', $location) }}" method="POST"
                              onsubmit="return confirm(@js(__('vendor.store_location_delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                {{ __('vendor.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <i class="fas fa-location-dot text-lg" aria-hidden="true"></i>
            </div>
            <p class="text-sm font-medium text-gray-800">{{ __('vendor.store_no_locations') }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_no_locations_cta') }}</p>
            <button type="button" @click="openAdd()"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                {{ __('vendor.store_add_location') }}
            </button>
        </div>
    @endif

    @include('vendor.store.partials.location-modal')
</div>
