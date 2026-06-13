@php
    $homeSections = $homeSections ?? [];
    $sectionCardProps = $sectionCardProps ?? [];
    $sections = [
        ['key' => 'trending', 'items' => $homeSections['trending'] ?? collect(), 'title' => __('vendor.store_section_trending'), 'icon' => 'fa-fire'],
        ['key' => 'new', 'items' => $homeSections['new'] ?? collect(), 'title' => __('vendor.store_section_new'), 'icon' => 'fa-sparkles'],
        ['key' => 'latest', 'items' => $homeSections['latest'] ?? collect(), 'title' => __('vendor.store_section_latest'), 'icon' => 'fa-clock'],
    ];
@endphp

@foreach($sections as $section)
    @if($section['items']->isNotEmpty())
        <section class="mt-6 sm:mt-8" aria-labelledby="home-section-{{ $section['key'] }}">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg store-accent-bg-soft store-accent-text">
                        <i class="fas {{ $section['icon'] }} text-sm" aria-hidden="true"></i>
                    </span>
                    <h2 id="home-section-{{ $section['key'] }}" class="text-base font-bold text-gray-900 sm:text-lg">{{ $section['title'] }}</h2>
                </div>
            </div>
            <div class="store-hide-scrollbar -mx-1 flex gap-3 overflow-x-auto pb-2 sm:gap-4">
                @foreach($section['items'] as $item)
                    <div class="w-[11.5rem] shrink-0 sm:w-[13rem]">
                        @include('storefront.partials.item-card', array_merge(['item' => $item], $sectionCardProps))
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endforeach

@if(! empty($homeSections['by_category']))
    @foreach($homeSections['by_category'] as $block)
        <section class="mt-6 sm:mt-8" aria-labelledby="home-cat-{{ $block['category']->id }}">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2">
                    @if($block['category']->image_url)
                        <img src="{{ $block['category']->image_url }}" alt="" class="h-8 w-8 rounded-lg object-cover" loading="lazy">
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                            <i class="fas fa-layer-group text-sm" aria-hidden="true"></i>
                        </span>
                    @endif
                    <h2 id="home-cat-{{ $block['category']->id }}" class="truncate text-base font-bold text-gray-900 sm:text-lg">{{ $block['category']->name }}</h2>
                </div>
                <a href="{{ $block['url'] }}"
                   class="shrink-0 text-sm font-semibold store-accent-text-dark hover:underline">
                    {{ __('vendor.store_view_all') }}
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                @foreach($block['items'] as $item)
                    @include('storefront.partials.item-card', array_merge(['item' => $item], $sectionCardProps))
                @endforeach
            </div>
        </section>
    @endforeach
@endif
