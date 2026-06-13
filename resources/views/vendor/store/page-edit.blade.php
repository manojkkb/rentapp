@extends('vendor.store.layout')

@section('store-content')
    <div class="store-section-panel space-y-4">
        <a href="{{ route('vendor.store.pages') }}" wire:navigate
           class="inline-flex min-h-11 items-center gap-2 rounded-lg px-1 text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-emerald-700">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.store_pages_back') }}
        </a>

        @include('vendor.store.partials.rich-text-assets')

        <form action="{{ route('vendor.store.pages.update', $pageForm['key']) }}" method="POST"
              x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-gray-200 p-4 sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                            <i class="fas {{ $pageForm['icon'] }} text-sm" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">{{ $pageForm['label'] }}</h2>
                            @if($pageForm['is_live'] && $pageForm['live_url'])
                                <a href="{{ $pageForm['live_url'] }}" target="_blank" rel="noopener"
                                   class="mt-0.5 inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                                    <i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i>
                                    {{ __('vendor.store_page_view') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @include('vendor.store.partials.rich-text-editor', [
                    'id' => 'page_content',
                    'name' => 'content',
                    'value' => $pageForm['content'],
                    'rows' => 12,
                    'placeholder' => $pageForm['placeholder'],
                    'hint' => $pageForm['hint'],
                ])
            </div>

            @include('vendor.store.partials.save-button')
        </form>
    </div>
@endsection
