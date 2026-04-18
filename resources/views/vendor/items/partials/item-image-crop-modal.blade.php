{{-- Square crop (Croppie): same behavior as category; IDs/classes are item-specific --}}
<div id="itemImageCropModal"
     class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center p-0 sm:p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="itemImageCropTitle">
    <div class="js-item-crop-backdrop absolute inset-0 bg-black/55 backdrop-blur-[2px]" aria-hidden="true"></div>

    <div class="relative flex w-full max-h-[min(96dvh,100svh)] flex-col rounded-t-3xl border border-gray-200 bg-white shadow-2xl sm:max-h-[96vh] sm:max-w-2xl sm:rounded-2xl lg:max-w-3xl">
        <div class="flex shrink-0 items-start justify-between gap-3 border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 pb-3 pt-[max(0.75rem,env(safe-area-inset-top))] sm:px-5 sm:py-4">
            <div class="min-w-0 flex-1">
                <h3 id="itemImageCropTitle" class="text-lg font-bold leading-tight text-gray-900 sm:text-xl">
                    Square photo for item
                </h3>
                <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">
                    <span class="font-medium text-emerald-800">1024×1024 WebP</span> saved. Drag to move the photo, use the slider to zoom in or out.
                </p>
                <p class="mt-2 rounded-lg bg-white/80 px-2.5 py-1.5 text-[11px] text-gray-700 ring-1 ring-emerald-100 sm:hidden sm:text-xs">
                    <i class="fas fa-hand-pointer mr-1 text-emerald-600" aria-hidden="true"></i>
                    Drag to move. Use the zoom bar below — pinch zoom is limited on some phones.
                </p>
            </div>
            <button type="button"
                    class="js-item-crop-cancel -mr-1 shrink-0 rounded-xl p-3 text-gray-500 transition-colors hover:bg-white/90 hover:text-gray-800 active:bg-white min-h-[44px] min-w-[44px]"
                    aria-label="Close">
                <i class="fas fa-times text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-hidden bg-slate-100 px-3 pb-2 pt-3 sm:px-4 sm:pb-3 sm:pt-4">
            <div id="itemImageCropStage"
                 class="item-croppie-stage mx-auto w-full max-w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-inner
                        h-[min(52svh,420px)] min-h-[220px] sm:h-[min(58vh,480px)] sm:min-h-[280px]">
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:justify-end sm:px-5 sm:py-4"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <button type="button"
                    class="js-item-crop-cancel min-h-[48px] w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base font-semibold text-gray-800 transition-colors hover:bg-gray-50 active:bg-gray-100 sm:min-h-0 sm:w-auto sm:py-2.5 sm:text-sm">
                Cancel
            </button>
            <button type="button"
                    id="itemImageCropApply"
                    class="min-h-[48px] w-full rounded-xl bg-emerald-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-60 sm:min-h-0 sm:w-auto sm:py-2.5 sm:text-sm">
                <i class="fas fa-check mr-2" aria-hidden="true"></i><span id="itemImageCropApplyLabel">Use this image</span>
            </button>
        </div>
    </div>
</div>

<style>
    .item-croppie-stage .cr-slider-wrap {
        margin-top: 0.875rem;
        padding: 0.5rem 0.25rem 0.25rem;
        width: 100% !important;
        max-width: 100%;
    }
    .item-croppie-stage .cr-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 9999px;
        background: rgb(203 213 225);
        outline: none;
    }
    .item-croppie-stage .cr-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 26px;
        height: 26px;
        margin-top: -9px;
        border-radius: 9999px;
        background: rgb(5 150 105);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.2);
    }
    .item-croppie-stage .cr-slider::-moz-range-thumb {
        width: 26px;
        height: 26px;
        border-radius: 9999px;
        background: rgb(5 150 105);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.2);
    }
    .item-croppie-stage .cr-viewport {
        border-width: 3px !important;
        border-color: rgb(255 255 255) !important;
        box-shadow:
            0 0 0 2px rgb(16 185 129 / 0.45),
            0 0 2000px 2000px rgb(0 0 0 / 0.5) !important;
    }
    .item-croppie-stage .cr-boundary {
        touch-action: none;
    }
</style>
