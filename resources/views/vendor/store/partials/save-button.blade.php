<div class="store-save-bar pointer-events-none max-lg:fixed max-lg:inset-x-0 max-lg:bottom-16 max-lg:z-40 max-lg:px-3 md:static md:z-auto md:px-0">
    <div class="pointer-events-auto flex justify-end rounded-t-xl border-t border-gray-200 bg-white/95 px-3 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.08)] backdrop-blur-sm md:rounded-none md:border-0 md:bg-transparent md:px-0 md:py-0 md:pt-4 md:shadow-none"
         style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
        <button type="submit"
                :disabled="submitting"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70 md:h-10 md:w-auto md:min-w-[8.5rem]">
            <span x-show="!submitting" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                {{ __('vendor.save_changes') }}
            </span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <i class="fas fa-circle-notch fa-spin text-sm" aria-hidden="true"></i>
                {{ __('vendor.saving') }}
            </span>
        </button>
    </div>
</div>
