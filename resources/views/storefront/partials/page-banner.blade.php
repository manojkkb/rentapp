@php
    $minimal = ($theme['template'] ?? 'classic') === 'minimal';
    $backUrl = $backUrl ?? route('storefront.show', $vendor->slug);
    $backLabel = $backLabel ?? __('vendor.store_nav_shop');
@endphp
<section class="{{ $minimal ? 'border-b border-gray-100 bg-white' : 'store-accent-bg-soft border-b border-gray-200/60' }}">
    <div class="store-site-container py-8 sm:py-10">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 text-sm font-medium store-accent-text hover:underline">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ $backLabel }}
        </a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
        @if(! empty($subtitle))
            <p class="mt-1 text-sm text-gray-600 sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>
</section>
