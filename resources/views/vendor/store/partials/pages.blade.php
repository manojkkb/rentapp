@php
    $pages = $storePagesList ?? [];
    $contentPages = array_values(array_filter($pages, fn ($page) => $page['group'] === 'content'));
    $legalPages = array_values(array_filter($pages, fn ($page) => $page['group'] === 'legal'));
@endphp

<div class="space-y-6">
    <p class="text-sm text-gray-600">{{ __('vendor.store_pages_help') }}</p>

    @foreach([
        'content' => ['items' => $contentPages, 'title' => __('vendor.store_pages_group_content')],
        'legal' => ['items' => $legalPages, 'title' => __('vendor.store_pages_group_legal')],
    ] as $group)
        @if($group['items'] !== [])
            <section class="space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $group['title'] }}</h2>
                <div class="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white">
                    @foreach($group['items'] as $page)
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                                    <i class="fas {{ $page['icon'] }} text-sm" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-gray-900">{{ $page['label'] }}</h3>
                                        @if($page['is_live'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                                <i class="fas fa-circle text-[6px]" aria-hidden="true"></i>
                                                {{ __('vendor.store_page_live') }}
                                            </span>
                                        @elseif($page['has_content'])
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-900">{{ __('vendor.store_page_has_content') }}</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">{{ __('vendor.store_page_empty') }}</span>
                                        @endif
                                    </div>
                                    @if($page['excerpt'])
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $page['excerpt'] }}</p>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">{{ __('vendor.store_page_no_content_yet') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="grid w-full grid-cols-1 gap-2 sm:flex sm:w-auto sm:flex-wrap sm:items-center sm:justify-end">
                                @if($page['live_url'])
                                    <a href="{{ $page['live_url'] }}" target="_blank" rel="noopener"
                                       class="inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:h-10 sm:w-auto">
                                        <i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i>
                                        {{ __('vendor.store_page_view') }}
                                    </a>
                                @endif
                                <a href="{{ $page['edit_url'] }}" wire:navigate
                                   class="inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:h-10 sm:w-auto">
                                    <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    {{ __('vendor.store_page_edit') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div>
