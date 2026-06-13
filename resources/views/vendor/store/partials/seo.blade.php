<form action="{{ route('vendor.store.seo.update') }}" method="POST"
      x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_seo_help') }}</p>

    <div>
        <label for="seo_title" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_seo_title') }}</label>
        <input type="text" id="seo_title" name="seo_title" maxlength="255"
               value="{{ old('seo_title', $store->seo_title) }}"
               placeholder="{{ $vendor->name }}"
               class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_seo_title_help') }}</p>
    </div>

    <div>
        <label for="seo_description" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_seo_description') }}</label>
        <textarea id="seo_description" name="seo_description" rows="4" maxlength="1000"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25"
                  placeholder="{{ Str::limit($store->description ?? '', 160) }}">{{ old('seo_description', $store->seo_description) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_seo_description_help') }}</p>
    </div>

    <div>
        <label for="seo_keywords" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_seo_keywords') }}</label>
        <input type="text" id="seo_keywords" name="seo_keywords" maxlength="500"
               value="{{ old('seo_keywords', $store->seo_keywords) }}"
               placeholder="{{ __('vendor.store_seo_keywords_placeholder') }}"
               class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_seo_keywords_help') }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
        <p class="font-semibold text-gray-800">{{ __('vendor.store_preview') }}</p>
        <p class="mt-2 text-base font-medium text-blue-800">{{ old('seo_title', $store->seo_title) ?: $vendor->name }}</p>
        <p class="text-xs text-green-700">{{ $storeUrl }}</p>
        <p class="mt-1 text-sm text-gray-600">{{ Str::limit(old('seo_description', $store->seo_description) ?: ($store->description ?? ''), 160) }}</p>
    </div>

    @include('vendor.store.partials.save-button')
</form>
