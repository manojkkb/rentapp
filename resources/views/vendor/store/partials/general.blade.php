<form action="{{ route('vendor.store.general.update') }}" method="POST" enctype="multipart/form-data"
      x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="grid gap-5 lg:grid-cols-2">
        <div>
            <label for="store_name" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.business_name') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="store_name" name="name" required
                   value="{{ old('name', $vendor->name) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="store_category" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.business_category') }} <span class="text-red-500">*</span>
            </label>
            <select id="store_category" name="business_category_id" required
                    class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                <option value="">{{ __('vendor.select_category') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('business_category_id', $vendor->business_category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="store_phone" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.store_business_phone') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="store_phone" name="business_phone" required
                   value="{{ old('business_phone', $store->business_phone) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-gray-50/80 p-4">
            <div class="mb-3 flex items-center gap-2">
                <i class="fab fa-whatsapp text-lg text-[#25D366]" aria-hidden="true"></i>
                <p class="text-sm font-semibold text-gray-900">{{ __('vendor.store_whatsapp_settings') }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="whatsapp_number" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_whatsapp_number') }}</label>
                    <input type="tel" id="whatsapp_number" name="whatsapp_number" pattern="[0-9]{10}" maxlength="10" inputmode="numeric"
                           placeholder="{{ __('vendor.store_whatsapp_number_placeholder') }}"
                           value="{{ old('whatsapp_number', $store->whatsapp_number) }}"
                           class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                    <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_whatsapp_number_help') }}</p>
                </div>
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:mt-6">
                    <input type="hidden" name="whatsapp_enabled" value="0">
                    <input type="checkbox" name="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $store->whatsapp_enabled ?? true))
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">{{ __('vendor.store_whatsapp_enabled') }}</span>
                        <span class="mt-0.5 block text-xs text-gray-600">{{ __('vendor.store_whatsapp_enabled_help') }}</span>
                    </span>
                </label>
            </div>
        </div>

        <div>
            <label for="store_email" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.store_business_email') }}
            </label>
            <input type="email" id="store_email" name="business_email"
                   value="{{ old('business_email', $store->business_email) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="store_website" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.store_website') }}
            </label>
            <input type="url" id="store_website" name="website" placeholder="https://"
                   value="{{ old('website', $store->website) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="store_gst" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.gst_number') }}
            </label>
            <input type="text" id="store_gst" name="gst_number"
                   value="{{ old('gst_number', $vendor->gst_number) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>
    </div>

    <div>
        <label for="store_tagline" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_tagline') }}</label>
        <input type="text" id="store_tagline" name="tagline" maxlength="255"
               value="{{ old('tagline', $store->tagline) }}"
               class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
    </div>

    <div>
        <label for="store_description" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_description') }}</label>
        <textarea id="store_description" name="description" rows="4"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">{{ old('description', $store->description) }}</textarea>
    </div>

    <div>
        <label for="store_logo" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.business_logo') }}</label>
        <div class="flex items-center gap-4">
            @if($vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="" class="h-14 w-14 rounded-lg border border-gray-200 object-cover">
            @endif
            <input type="file" id="store_logo" name="logo" accept="image/*"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-emerald-700">
        </div>
    </div>

    <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $store->is_published))
               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
        <span>
            <span class="block text-sm font-semibold text-gray-900">{{ __('vendor.store_publish_label') }}</span>
            <span class="mt-0.5 block text-xs text-gray-600">{{ __('vendor.store_publish_help') }}</span>
        </span>
    </label>

    @include('vendor.store.partials.save-button')
</form>
