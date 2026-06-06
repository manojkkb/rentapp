{{-- Feature image + gallery picker (gallery preview only until backend support) --}}

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    {{-- Feature image --}}
    @php
        $existingPhoto = ($item ?? null)?->photo_url;
    @endphp
    <div x-data="{ featurePreview: @js($existingPhoto) }">
        <label class="{{ $ilabel }}">{{ __('vendor.item_form_feature_image') }}</label>
        <p class="{{ $ihint }} mb-3">{{ __('vendor.field_hint_photo') }}</p>

        <input type="file" id="photo" name="photo" accept="image/*"
               class="js-item-image-input sr-only @error('photo') border-red-500 @enderror"
               @change="featurePreview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null">

        <label for="photo"
               class="group relative flex min-h-[160px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gradient-to-br from-slate-50 to-emerald-50/40 px-3 py-6 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50 sm:min-h-[180px]">
            <template x-if="!featurePreview">
                <div class="flex flex-col items-center gap-2">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition group-hover:scale-105">
                        <i class="fas fa-camera text-xl text-emerald-600" aria-hidden="true"></i>
                    </span>
                    <span class="text-sm font-semibold text-gray-800">{{ __('vendor.item_form_upload_feature') }}</span>
                    <span class="max-w-[16rem] text-xs leading-snug text-gray-500">{{ __('vendor.item_form_feature_image_hint') }}</span>
                </div>
            </template>
            <img x-show="featurePreview" x-cloak :src="featurePreview" alt=""
                 class="absolute inset-0 h-full w-full object-cover">
            <span x-show="featurePreview" x-cloak
                  class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-3 text-xs font-medium text-white">
                {{ __('vendor.item_form_tap_to_change') }}
            </span>
        </label>
        @error('photo')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>

    {{-- Gallery (preview UI) --}}
    <div x-data="{
        items: [],
        addFiles(event) {
            Array.from(event.target.files || []).forEach(file => {
                if (this.items.length >= 8) return;
                this.items.push({ id: crypto.randomUUID(), name: file.name, url: URL.createObjectURL(file) });
            });
            event.target.value = '';
        },
        remove(id) {
            const i = this.items.findIndex(x => x.id === id);
            if (i >= 0) {
                URL.revokeObjectURL(this.items[i].url);
                this.items.splice(i, 1);
            }
        }
    }">
        <label class="{{ $ilabel }}">{{ __('vendor.item_form_gallery_images') }}</label>
        <p class="{{ $ihint }} mb-3">{{ __('vendor.item_form_gallery_hint') }}</p>

        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
            <template x-for="item in items" :key="item.id">
                <div class="group relative aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                    <img :src="item.url" :alt="item.name" class="h-full w-full object-cover">
                    <button type="button"
                            @click="remove(item.id)"
                            class="absolute right-1 top-1 flex h-7 w-7 items-center justify-center rounded-lg bg-black/55 text-white opacity-100 transition hover:bg-red-600 sm:opacity-0 sm:group-hover:opacity-100"
                            :aria-label="@js(__('vendor.remove'))">
                        <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </template>

            <label x-show="items.length < 8"
                   class="flex aspect-square cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/80 px-2 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50">
                <i class="fas fa-plus text-sm text-emerald-600" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight text-gray-600">{{ __('vendor.item_form_add_gallery') }}</span>
                <input type="file" accept="image/*" multiple class="sr-only" @change="addFiles($event)">
            </label>
        </div>

        <p class="mt-2 text-[11px] leading-snug text-gray-400">
            <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
            {{ __('vendor.item_form_gallery_save_note') }}
        </p>
    </div>
</div>
