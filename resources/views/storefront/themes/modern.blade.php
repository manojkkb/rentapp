<div class="mx-auto flex min-h-dvh max-w-6xl flex-col">
    @include('storefront.partials.header')

    @include('storefront.partials.hero', ['heroOverlay' => 'dark'])

    <main class="flex-1 px-3 pb-24 pt-5 sm:px-5 sm:pb-8 md:px-8">
        @include('storefront.partials.categories')

        <section class="mt-5 sm:mt-8">
            <div class="mb-4 flex items-end justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest store-accent-text sm:text-xs">{{ __('vendor.store_public_rent_now') }}</p>
                    <h2 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.store_public_catalog') }}</h2>
                </div>
                <span class="store-accent-bg-soft rounded-full px-2.5 py-1 text-xs font-semibold store-accent-text-dark">{{ $items->count() }}</span>
            </div>

            @if($items->isEmpty())
                <div class="{{ $theme['classes']['section'] }} bg-white px-4 py-14 text-center">
                    <p class="text-sm text-gray-600">{{ __('vendor.store_public_no_items') }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                    @foreach($items as $item)
                        @include('storefront.partials.item-card', ['item' => $item, 'modernCard' => true])
                    @endforeach
                </div>
            @endif
        </section>

        @include('storefront.partials.about')
        @include('storefront.partials.locations')
    </main>

    @include('storefront.partials.footer')
    @include('storefront.partials.mobile-bar')
</div>
