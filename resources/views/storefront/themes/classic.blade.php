<div class="mx-auto flex min-h-dvh max-w-6xl flex-col">
    @include('storefront.partials.header')

    @include('storefront.partials.hero')

    <main class="flex-1 px-3 pb-24 pt-4 sm:px-4 sm:pb-8 md:px-6">
        @include('storefront.partials.categories')

        <section class="mt-4 sm:mt-6">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.store_public_catalog') }}</h2>
                <span class="text-xs text-gray-500 sm:text-sm">{{ $items->count() }} {{ __('vendor.items') }}</span>
            </div>

            @if($items->isEmpty())
                <div class="{{ $theme['classes']['section'] }} bg-white px-4 py-12 text-center">
                    <i class="fas fa-box-open mb-3 text-3xl text-gray-300" aria-hidden="true"></i>
                    <p class="text-sm font-medium text-gray-700">{{ __('vendor.store_public_no_items') }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    @foreach($items as $item)
                        @include('storefront.partials.item-card', ['item' => $item])
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
