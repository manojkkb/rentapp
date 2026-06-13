{{-- Feature image + gallery (max 8) --}}
@php
    $existingPhoto = ($item ?? null)?->photo_url;
    $existingGallery = ($item ?? null)?->images
        ?->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])
        ->values()
        ->all() ?? [];
    $galleryConfig = [
        'existing' => $existingGallery,
        'maxImages' => 8,
    ];
@endphp

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemGalleryPicker', (config) => ({
            maxImages: config.maxImages || 8,
            existing: config.existing || [],
            removedIds: [],
            newPreviews: [],
            init() {
                this.syncFileInput();
            },
            get activeExisting() {
                return this.existing.filter((img) => !this.removedIds.includes(img.id));
            },
            get totalCount() {
                return this.activeExisting.length + this.newPreviews.length;
            },
            get canAddMore() {
                return this.totalCount < this.maxImages;
            },
            get remainingSlots() {
                return Math.max(0, this.maxImages - this.totalCount);
            },
            addFiles(event) {
                const files = Array.from(event.target.files || []);
                for (const file of files) {
                    if (!this.canAddMore) break;
                    this.newPreviews.push({
                        id: crypto.randomUUID(),
                        file,
                        url: URL.createObjectURL(file),
                        name: file.name,
                    });
                }
                this.syncFileInput();
                event.target.value = '';
            },
            removeExisting(id) {
                if (!this.removedIds.includes(id)) {
                    this.removedIds.push(id);
                }
            },
            removeNew(id) {
                const index = this.newPreviews.findIndex((p) => p.id === id);
                if (index >= 0) {
                    URL.revokeObjectURL(this.newPreviews[index].url);
                    this.newPreviews.splice(index, 1);
                    this.syncFileInput();
                }
            },
            syncFileInput() {
                const input = this.$refs.galleryInput;
                if (!input) return;
                const dt = new DataTransfer();
                this.newPreviews.forEach((p) => dt.items.add(p.file));
                input.files = dt.files;
            },
        }));
    });
</script>
@endonce

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    {{-- Feature image --}}
    <div x-data="{
        featurePreview: @js($existingPhoto),
        isDefault: @js(!$existingPhoto),
        onPhotoChange(event) {
            const file = event.target.files?.[0];
            if (file) {
                this.featurePreview = URL.createObjectURL(file);
                this.isDefault = false;
            }
        }
    }">
        <label class="{{ $ilabel }}">{{ __('vendor.item_form_feature_image') }}</label>
        <p class="{{ $ihint }} mb-3">{{ __('vendor.field_hint_photo') }}</p>

        <input type="file" id="photo" name="photo" accept="image/*"
               class="js-item-image-input sr-only @error('photo') border-red-500 @enderror"
               @change="onPhotoChange($event)">

        <label for="photo"
               class="group relative flex min-h-[180px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gradient-to-br from-slate-50 to-emerald-50/40 px-3 py-6 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50 sm:min-h-[200px]">
            <template x-if="!featurePreview">
                <div class="flex flex-col items-center gap-3">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-md ring-4 ring-emerald-100 transition group-hover:scale-105">
                        <i class="fas fa-box text-2xl" aria-hidden="true"></i>
                    </span>
                    <div>
                        <span class="block text-sm font-semibold text-gray-800">{{ __('vendor.item_form_upload_feature') }}</span>
                        <span class="mt-1 block max-w-[16rem] text-xs leading-snug text-gray-500">{{ __('vendor.item_form_feature_image_hint') }}</span>
                    </div>
                </div>
            </template>
            <img x-show="featurePreview" x-cloak :src="featurePreview" alt=""
                 class="absolute inset-0 h-full w-full object-cover">
            <span x-show="featurePreview" x-cloak
                  class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-3 py-3 text-xs font-medium text-white">
                <i class="fas fa-camera mr-1" aria-hidden="true"></i>
                {{ __('vendor.item_form_tap_to_change') }}
            </span>
        </label>
        @error('photo')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>

    {{-- Gallery --}}
    <div x-data="itemGalleryPicker(@js($galleryConfig))">
        <div class="mb-3 flex items-start justify-between gap-2">
            <div>
                <label class="{{ $ilabel }}">{{ __('vendor.item_form_gallery_images') }}</label>
                <p class="{{ $ihint }}">{{ __('vendor.item_form_gallery_hint') }}</p>
            </div>
            <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold tabular-nums text-gray-600"
                  x-text="totalCount + ' / ' + maxImages"></span>
        </div>

        <input type="file"
               x-ref="galleryInput"
               name="gallery_images[]"
               accept="image/*"
               multiple
               class="sr-only"
               @change="addFiles($event)">

        <template x-for="id in removedIds" :key="'remove-' + id">
            <input type="hidden" name="remove_gallery_images[]" :value="id">
        </template>

        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
            <template x-for="img in activeExisting" :key="'existing-' + img.id">
                <div class="group relative aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                    <img :src="img.url" alt="" class="h-full w-full object-cover">
                    <button type="button"
                            @click="removeExisting(img.id)"
                            class="absolute right-1 top-1 flex h-7 w-7 items-center justify-center rounded-lg bg-black/55 text-white transition hover:bg-red-600 sm:opacity-0 sm:group-hover:opacity-100"
                            :aria-label="@js(__('vendor.remove'))">
                        <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </template>

            <template x-for="preview in newPreviews" :key="'new-' + preview.id">
                <div class="group relative aspect-square overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/50 ring-1 ring-emerald-100">
                    <img :src="preview.url" :alt="preview.name" class="h-full w-full object-cover">
                    <span class="absolute left-1 top-1 rounded-md bg-emerald-600/90 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white">New</span>
                    <button type="button"
                            @click="removeNew(preview.id)"
                            class="absolute right-1 top-1 flex h-7 w-7 items-center justify-center rounded-lg bg-black/55 text-white transition hover:bg-red-600 sm:opacity-0 sm:group-hover:opacity-100"
                            :aria-label="@js(__('vendor.remove'))">
                        <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </template>

            <button type="button"
                    x-show="canAddMore"
                    x-cloak
                    @click="$refs.galleryInput.click()"
                    class="flex aspect-square flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/80 px-2 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50">
                <i class="fas fa-plus text-sm text-emerald-600" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight text-gray-600">{{ __('vendor.item_form_add_gallery') }}</span>
            </button>
        </div>

        <p class="mt-2 text-xs leading-snug text-gray-500" x-show="canAddMore" x-cloak>
            <i class="fas fa-info-circle mr-1 text-gray-400" aria-hidden="true"></i>
            <span x-text="@js(__('vendor.item_form_gallery_slots_remaining'))".replace(':count', remainingSlots)"></span>
        </p>

        @error('gallery_images')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
        @error('gallery_images.*')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
</div>
