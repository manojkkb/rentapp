@if(($banner['show_cta'] ?? false) && ($banner['cta_text'] ?? null) && ($banner['cta_url'] ?? null))
    <a href="{{ $banner['cta_url'] }}" target="_blank" rel="noopener noreferrer"
       class="{{ $theme['classes']['btn'] }} store-btn-primary mt-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold">
        {{ $banner['cta_text'] }}
        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
    </a>
@endif
