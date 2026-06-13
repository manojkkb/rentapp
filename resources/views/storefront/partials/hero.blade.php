@php
    $banner = $banner ?? \App\Support\StorefrontBanner::resolve($store, $vendor);
    $heroVariant = $heroVariant ?? 'default';
    $heroOverlay = $heroOverlay ?? false;
    $hasHeroImage = (bool) ($banner['image_url'] ?? null);
    $imageOverlayClass = $heroOverlay
        ? 'bg-black/50'
        : \App\Support\StorefrontBanner::overlayClass($banner['overlay'], $hasHeroImage);
    $paddingClass = $banner['padding_class'];
@endphp

@if($heroVariant === 'compact')
    <section class="{{ $theme['classes']['hero'] }} store-accent-bg text-white shadow-md">
        <div class="store-site-container {{ $paddingClass }}">
            <div class="max-w-3xl">
                @if($vendor->businessCategory)
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-white/75">{{ $vendor->businessCategory->name }}</p>
                @endif
                <h2 class="text-2xl font-black uppercase tracking-tight sm:text-4xl">{{ $banner['title'] }}</h2>
                @if($banner['subtitle'])
                    <p class="mt-2 text-sm font-semibold text-white/90 sm:text-base">{{ $banner['subtitle'] }}</p>
                @endif
                @include('storefront.partials.hero-cta')
            </div>
        </div>
    </section>
@elseif($heroVariant === 'neon')
    <section class="{{ $theme['classes']['hero'] }} relative overflow-hidden text-white" style="background: linear-gradient(135deg, #4c1d95 0%, #7e22ce 50%, #c026d3 100%);">
        @if($hasHeroImage)
            <img src="{{ $banner['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
            <div class="absolute inset-0 {{ $imageOverlayClass }}"></div>
        @else
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 20% 50%, #f0abfc 0%, transparent 50%), radial-gradient(circle at 80% 20%, #a855f7 0%, transparent 40%);"></div>
        @endif
        <div class="store-site-container relative {{ $paddingClass }}">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-fuchsia-200">{{ __('vendor.store_theme_neon_tagline') }}</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-5xl">{{ $banner['title'] }}</h2>
                @if($banner['subtitle'])
                    <p class="mt-3 text-base text-purple-100 sm:text-lg">{{ $banner['subtitle'] }}</p>
                @endif
                @include('storefront.partials.hero-cta')
            </div>
        </div>
    </section>
@elseif($heroVariant === 'nature')
    <section class="{{ $theme['classes']['hero'] }} relative overflow-hidden text-white" style="background: linear-gradient(160deg, #365314 0%, #4d7c0f 55%, #65a30d 100%);">
        @if($hasHeroImage)
            <img src="{{ $banner['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
            <div class="absolute inset-0 {{ $imageOverlayClass }}"></div>
        @endif
        <div class="store-site-container relative {{ $paddingClass }}">
            <div class="max-w-2xl">
                @if($vendor->businessCategory)
                    <p class="text-xs font-semibold uppercase tracking-widest text-lime-200">{{ $vendor->businessCategory->name }}</p>
                @endif
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $banner['title'] }}</h2>
                @if($banner['subtitle'])
                    <p class="mt-3 text-sm leading-relaxed text-lime-50 sm:text-base">{{ $banner['subtitle'] }}</p>
                @elseif($store->description)
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-lime-50 sm:text-base">{{ Str::limit($store->description, 180) }}</p>
                @endif
                @include('storefront.partials.hero-cta')
            </div>
        </div>
    </section>
@elseif($heroVariant === 'ocean')
    <section class="{{ $theme['classes']['hero'] }} store-theme-ocean-hero relative overflow-hidden text-white">
        @if($hasHeroImage)
            <img src="{{ $banner['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
            <div class="absolute inset-0 {{ $imageOverlayClass }}"></div>
        @else
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #0c4a6e 0%, #0369a1 60%, #0284c7 100%);"></div>
        @endif
        <div class="store-site-container relative {{ $paddingClass }}">
            <div class="max-w-2xl text-center sm:text-left">
                <h2 class="text-3xl font-bold sm:text-4xl">{{ $banner['title'] }}</h2>
                @if($banner['subtitle'])
                    <p class="mt-3 text-lg text-sky-100">{{ $banner['subtitle'] }}</p>
                @endif
                @include('storefront.partials.hero-cta')
            </div>
        </div>
        <div class="store-theme-ocean-wave absolute bottom-0 left-0 right-0 h-8 sm:h-12"></div>
    </section>
@elseif($heroVariant === 'sunset')
    <section class="{{ $theme['classes']['hero'] }} relative overflow-hidden text-white" style="background: linear-gradient(120deg, #c2410c 0%, #ea580c 35%, #db2777 100%);">
        @if($hasHeroImage)
            <img src="{{ $banner['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
            <div class="absolute inset-0 {{ $imageOverlayClass }}"></div>
        @endif
        <div class="store-site-container relative {{ $paddingClass }}">
            <div class="max-w-3xl">
                <h2 class="text-3xl font-extrabold sm:text-5xl">{{ $banner['title'] }}</h2>
                @if($banner['subtitle'])
                    <p class="mt-4 text-lg font-medium text-orange-100">{{ $banner['subtitle'] }}</p>
                @endif
                @include('storefront.partials.hero-cta')
            </div>
        </div>
    </section>
@elseif($heroVariant === 'mono')
    <section class="{{ $theme['classes']['hero'] }} bg-zinc-900 text-white {{ $paddingClass }}">
        <div class="store-site-container">
            <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-zinc-400">{{ __('vendor.store_public_catalog') }}</p>
            <h2 class="mt-2 text-4xl font-black uppercase tracking-tight sm:text-5xl">{{ $banner['title'] }}</h2>
            @if($banner['subtitle'])
                <p class="mt-3 max-w-xl text-sm text-zinc-400">{{ $banner['subtitle'] }}</p>
            @endif
            @include('storefront.partials.hero-cta')
        </div>
    </section>
@elseif($heroVariant === 'split')
    <section class="{{ $theme['classes']['hero'] }} store-section-bg overflow-hidden">
        <div class="store-site-container {{ $paddingClass }}">
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
                <div class="order-2 lg:order-1">
                    @if($banner['subtitle'])
                        <p class="text-xs font-medium uppercase tracking-[0.25em] text-gray-400">{{ $banner['subtitle'] }}</p>
                    @endif
                    <h2 class="store-theme-boutique-title mt-2 text-3xl font-light tracking-tight sm:text-4xl lg:text-5xl">{{ $banner['title'] }}</h2>
                    @if($store->description)
                        <p class="mt-4 max-w-lg text-sm leading-relaxed text-gray-600 sm:text-base">{{ Str::limit($store->description, 220) }}</p>
                    @endif
                    @include('storefront.partials.hero-cta')
                </div>
                <div class="order-1 lg:order-2">
                    <div class="relative overflow-hidden rounded-2xl shadow-lg ring-1 ring-black/5">
                        @if($hasHeroImage)
                            <img src="{{ $banner['image_url'] }}" alt="" class="aspect-[4/3] w-full object-cover lg:aspect-square" loading="eager">
                        @else
                            <div class="store-hero-gradient flex aspect-[4/3] items-center justify-center lg:aspect-square">
                                <span class="text-6xl font-light text-white/90">{{ strtoupper(substr($banner['title'], 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@else
<section class="{{ $theme['classes']['hero'] }} relative overflow-hidden {{ $hasHeroImage ? '' : 'store-hero-gradient' }} text-white">
    @if($hasHeroImage)
        <img src="{{ $banner['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
        <div class="absolute inset-0 {{ $imageOverlayClass }}"></div>
    @endif
    <div class="store-site-container relative {{ $paddingClass }}">
        <div class="max-w-2xl">
            @if($vendor->businessCategory)
                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/80 sm:text-xs">{{ $vendor->businessCategory->name }}</p>
            @endif
            <h2 class="text-3xl font-bold leading-tight sm:text-4xl md:text-5xl">{{ $banner['title'] }}</h2>
            @if($banner['subtitle'])
                <p class="mt-3 text-base text-white/90 sm:text-lg">{{ $banner['subtitle'] }}</p>
            @endif
            @if($store->description)
                <p class="mt-4 line-clamp-3 max-w-xl text-sm leading-relaxed text-white/85 sm:text-base">{{ Str::limit($store->description, 200) }}</p>
            @endif
            <div class="mt-6 flex flex-wrap items-center gap-2">
                @if($vendor->rating > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-xs font-semibold backdrop-blur-sm">
                        <i class="fas fa-star text-amber-300" aria-hidden="true"></i>
                        {{ number_format((float) $vendor->rating, 1) }}
                        <span class="text-white/70">({{ $vendor->total_reviews }})</span>
                    </span>
                @endif
                @if($store->pickup_enabled)
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium backdrop-blur-sm">{{ __('vendor.pickup') }}</span>
                @endif
                @if($store->delivery_enabled)
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium backdrop-blur-sm">{{ __('vendor.delivery') }}</span>
                @endif
            </div>
            @include('storefront.partials.hero-cta')
        </div>
    </div>
</section>
@endif
