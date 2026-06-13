    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-[70] flex items-end justify-center p-0 sm:items-center sm:p-4"
         role="dialog"
         aria-modal="true"
         @keydown.escape.window="closeModal()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]" @click="closeModal()" aria-hidden="true"></div>

        <div class="relative z-10 flex max-h-[94dvh] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[92vh] sm:rounded-2xl"
             @click.stop>
            <div class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 bg-gradient-to-b from-emerald-50/60 to-white px-4 py-3.5 sm:px-5">
                <div>
                    <h3 class="text-base font-bold text-gray-900 sm:text-lg"
                        x-text="mode === 'add' ? @js(__('vendor.store_add_location')) : @js(__('vendor.store_edit_location'))"></h3>
                    <p class="mt-0.5 text-xs text-gray-600 sm:text-sm">{{ __('vendor.store_location_modal_help') }}</p>
                </div>
                <button type="button" @click="closeModal()"
                        class="rounded-xl p-2 text-gray-500 transition hover:bg-white hover:text-gray-800"
                        aria-label="{{ __('vendor.cancel') }}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="flex min-h-0 flex-1 flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-800">
                                {{ __('vendor.store_location_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required x-model="form.name"
                                   placeholder="{{ __('vendor.store_location_name_placeholder') }}"
                                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-semibold text-gray-800">
                                    {{ __('vendor.address_line1') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="address_line1" required x-model="form.address_line1"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.address_line2') }}</label>
                                <input type="text" name="address_line2" x-model="form.address_line2"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-800">
                                    {{ __('vendor.city') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="city" required x-model="form.city"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-800">
                                    {{ __('vendor.state') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="state" required x-model="form.state"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.postal_code') }}</label>
                                <input type="text" name="postal_code" x-model="form.postal_code"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-800">
                                    {{ __('vendor.country') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="country" required x-model="form.country"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.phone') }}</label>
                                <input type="text" name="phone" x-model="form.phone"
                                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 sm:p-4">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('vendor.store_geo_location') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('vendor.store_geo_help') }}</p>
                                </div>
                                <button type="button"
                                        @click="useCurrentLocation()"
                                        :disabled="geoLoading"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-50 disabled:opacity-60">
                                    <i class="fas" :class="geoLoading ? 'fa-spinner fa-spin' : 'fa-crosshairs'" aria-hidden="true"></i>
                                    {{ __('vendor.store_use_my_location') }}
                                </button>
                            </div>

                            <template x-if="geoError">
                                <p class="mb-2 text-xs font-medium text-red-600" x-text="geoError"></p>
                            </template>

                            <div x-ref="mapEl"
                                 class="mb-3 h-48 w-full overflow-hidden rounded-lg border border-gray-300 bg-gray-200 sm:h-56"></div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-gray-700">{{ __('vendor.store_latitude') }}</label>
                                    <input type="number" step="any" name="latitude" x-model="form.latitude" @input="onCoordInput()"
                                           placeholder="e.g. 28.6139"
                                           class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-gray-700">{{ __('vendor.store_longitude') }}</label>
                                    <input type="number" step="any" name="longitude" x-model="form.longitude" @input="onCoordInput()"
                                           placeholder="e.g. 77.2090"
                                           class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">{{ __('vendor.store_map_pin_hint') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_default" value="1" x-model="form.is_default"
                                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                {{ __('vendor.store_set_as_default') }}
                            </label>
                            <label x-show="mode === 'edit'" x-cloak class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                {{ __('vendor.active') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 justify-end gap-2 border-t border-gray-200 bg-white px-4 py-3 sm:px-5">
                    <button type="button" @click="closeModal()"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fas fa-check text-xs" aria-hidden="true"></i>
                        <span x-text="mode === 'add' ? @js(__('vendor.store_add_location')) : @js(__('vendor.save_changes'))"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
