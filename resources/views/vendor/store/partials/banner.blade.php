<form action="{{ route('vendor.store.banner.update') }}" method="POST" enctype="multipart/form-data"
      x-data="{ submitting: false, showCta: @js((bool) old('banner_show_cta', $store->banner_show_cta)) }"
      @submit="submitting = true"
      class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_banner_help') }}</p>

    <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
        <input type="checkbox" name="banner_enabled" value="1" class="rounded border-gray-300 text-emerald-600"
               @checked(old('banner_enabled', $store->banner_enabled ?? true))>
        <span class="text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_enabled') }}</span>
    </label>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="banner_title" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_title') }}</label>
            <input type="text" id="banner_title" name="banner_title" maxlength="255"
                   value="{{ old('banner_title', $store->banner_title) }}"
                   placeholder="{{ $vendor->name }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm text-gray-800">
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_banner_title_help') }}</p>
        </div>
        <div>
            <label for="banner_subtitle" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_subtitle') }}</label>
            <input type="text" id="banner_subtitle" name="banner_subtitle" maxlength="500"
                   value="{{ old('banner_subtitle', $store->banner_subtitle) }}"
                   placeholder="{{ $store->tagline ?? __('vendor.store_banner_subtitle_placeholder') }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm text-gray-800">
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_banner_subtitle_help') }}</p>
        </div>
    </div>

    <div>
        <label for="hero_image" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_image') }}</label>
        @if($store->hero_image_url)
            <img src="{{ $store->hero_image_url }}" alt="" class="mb-3 h-40 w-full max-w-xl rounded-xl border border-gray-200 object-cover">
            <label class="mb-3 flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-gray-300 text-emerald-600">
                {{ __('vendor.store_remove_hero_image') }}
            </label>
        @endif
        <input type="file" id="hero_image" name="hero_image" accept="image/*"
               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-emerald-700">
        <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_banner_image_help') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="banner_height" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_height') }}</label>
            <select id="banner_height" name="banner_height" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm text-gray-800">
                @foreach($bannerHeights as $height)
                    <option value="{{ $height }}" @selected(old('banner_height', $store->banner_height ?? 'medium') === $height)>
                        {{ __('vendor.store_banner_height_'.$height) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="banner_overlay" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_banner_overlay') }}</label>
            <select id="banner_overlay" name="banner_overlay" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm text-gray-800">
                @foreach($bannerOverlays as $overlay)
                    <option value="{{ $overlay }}" @selected(old('banner_overlay', $store->banner_overlay ?? 'gradient') === $overlay)>
                        {{ __('vendor.store_banner_overlay_'.$overlay) }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_banner_overlay_help') }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 p-4">
        <label class="flex items-center gap-2 text-sm font-semibold text-gray-800">
            <input type="checkbox" name="banner_show_cta" value="1" x-model="showCta" class="rounded border-gray-300 text-emerald-600"
                   @checked(old('banner_show_cta', $store->banner_show_cta))>
            {{ __('vendor.store_banner_show_cta') }}
        </label>
        <div x-show="showCta" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="banner_cta_text" class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.store_banner_cta_text') }}</label>
                <input type="text" id="banner_cta_text" name="banner_cta_text" maxlength="120"
                       value="{{ old('banner_cta_text', $store->banner_cta_text) }}"
                       placeholder="{{ __('vendor.store_set_booking_dates') }}"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm">
            </div>
            <div>
                <label for="banner_cta_url" class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.store_banner_cta_url') }}</label>
                <input type="url" id="banner_cta_url" name="banner_cta_url" maxlength="500"
                       value="{{ old('banner_cta_url', $store->banner_cta_url) }}"
                       placeholder="https://"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm">
            </div>
        </div>
    </div>

    @include('vendor.store.partials.save-button')
</form>
