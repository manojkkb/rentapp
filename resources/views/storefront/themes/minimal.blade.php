<div class="mx-auto flex min-h-dvh max-w-5xl flex-col bg-white">
    @include('storefront.partials.header', ['minimal' => true])

    <main class="flex-1 px-4 pb-24 pt-6 sm:px-6 sm:pb-10 md:px-8">
        <header class="mb-8 border-b border-gray-100 pb-6">
            @if($store->tagline)
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-gray-400">{{ $store->tagline }}</p>
            @endif
            <h1 class="mt-1 text-2xl font-light tracking-tight text-gray-900 sm:text-3xl">{{ $vendor->name }}</h1>
            @if($store->description)
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600">{{ $store->description }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
                @if($store->business_phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" class="hover:store-accent-text">{{ $store->business_phone }}</a>
                @endif
                @if($vendor->city)
                    <span>{{ $vendor->city }}{{ $vendor->state ? ', '.$vendor->state : '' }}</span>
                @endif
            </div>
        </header>

        @include('storefront.partials.categories', ['minimal' => true])

        <section class="mt-8">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">{{ __('vendor.store_public_catalog') }}</h2>

            @if($items->isEmpty())
                <p class="text-sm text-gray-500">{{ __('vendor.store_public_no_items') }}</p>
            @else
                <div class="divide-y divide-gray-100 border-t border-gray-100">
                    @foreach($items as $item)
                        @include('storefront.partials.item-row', ['item' => $item])
                    @endforeach
                </div>
            @endif
        </section>

        @if($locations->isNotEmpty())
            <section class="mt-10 border-t border-gray-100 pt-8">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-900">{{ __('vendor.store_tab_locations') }}</h2>
                <ul class="space-y-2 text-sm text-gray-600">
                    @foreach($locations as $location)
                        <li>{{ $location->name }} — {{ $location->full_address }}</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>

    @include('storefront.partials.mobile-bar')
</div>
