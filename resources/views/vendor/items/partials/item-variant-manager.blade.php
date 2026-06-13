{{-- Variant manager: attribute columns + variant rows grid (Alpine.js) --}}
@php
    $embedded = $embedded ?? false;
    $in = $formInputClass ?? 'w-full min-w-0 rounded border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $lb = $formLabelClass ?? 'block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-0.5';
    $fh = $formHintClass ?? 'text-[10px] leading-snug text-gray-500';
@endphp

@if(!$embedded)
<section class="rounded-lg border border-emerald-200/80 bg-emerald-50/40 p-3 sm:p-3.5 space-y-3" aria-labelledby="item-section-variants">
    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-emerald-200/70 pb-2">
        <div>
            <h2 id="item-section-variants" class="text-[11px] font-bold uppercase tracking-wide text-emerald-900">
                {{ __('vendor.item_variants_section') }}
            </h2>
            <p class="mt-0.5 text-[10px] leading-snug text-gray-600">{{ __('vendor.item_variants_section_hint') }}</p>
        </div>
        <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border border-emerald-300 bg-white px-2.5 py-1.5 shadow-sm">
            <input type="checkbox"
                   class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                   :checked="hasVariants"
                   @change="setHasVariants($event.target.checked)">
            <span class="text-xs font-semibold text-emerald-900">{{ __('vendor.item_has_variants') }}</span>
        </label>
    </div>
@else
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-emerald-50/60 px-3.5 py-3">
        <p class="text-xs leading-snug text-gray-600">{{ __('vendor.item_variants_section_hint') }}</p>
        <label class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 shadow-sm">
            <input type="checkbox"
                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                   :checked="hasVariants"
                   @change="setHasVariants($event.target.checked)">
            <span class="text-sm font-semibold text-emerald-900">{{ __('vendor.item_has_variants') }}</span>
        </label>
    </div>
@endif

    <input type="hidden" name="has_variants" :value="hasVariants ? 1 : 0">

    <div x-show="hasVariants" x-cloak class="space-y-3">
        {{-- Step 1: Attributes (columns) --}}
        <div class="rounded-md border border-gray-200 bg-white p-2.5">
            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.item_variant_attributes_title') }}</p>
            <p class="mt-0.5 mb-2 text-[10px] text-gray-500">{{ __('vendor.item_variant_attributes_hint') }}</p>

            <div class="flex flex-wrap gap-1.5 mb-2">
                <template x-for="preset in presetAttributes" :key="'preset-'+preset">
                    <button type="button"
                            class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800"
                            @click="addPreset(preset)"
                            x-text="'+' + preset"></button>
                </template>
            </div>

            <div class="flex flex-wrap gap-2">
                <input type="text"
                       x-model="newAttributeName"
                       @keydown.enter.prevent="addAttribute()"
                       class="{{ $in }} max-w-xs flex-1"
                       placeholder="{{ __('vendor.item_variant_attribute_placeholder') }}">
                <button type="button"
                        @click="addAttribute()"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-plus mr-1"></i>{{ __('vendor.item_variant_add_attribute') }}
                </button>
            </div>

            <div class="mt-2 flex flex-wrap gap-1.5" x-show="attributes.length > 0">
                <template x-for="(attr, index) in attributes" :key="'attr-chip-'+index">
                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-900">
                        <span x-text="attr.name"></span>
                        <button type="button"
                                class="text-emerald-700/70 hover:text-red-600"
                                @click="removeAttribute(index)"
                                :title="@js(__('vendor.remove'))">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                    </span>
                </template>
            </div>

            @error('variant_attributes')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            @error('variant_attributes.*')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Step 2: Variant rows --}}
        <div class="rounded-md border border-gray-200 bg-white p-2.5" x-show="attributes.length > 0" x-cloak>
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.item_variant_rows_title') }}</p>
                    <p class="mt-0.5 text-[10px] text-gray-500">{{ __('vendor.item_variant_rows_hint') }}</p>
                </div>
                <button type="button"
                        @click="addVariantRow()"
                        class="rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
                    <i class="fas fa-plus mr-1"></i>{{ __('vendor.item_variant_add_row') }}
                </button>
            </div>

            <div class="overflow-x-auto -mx-1 px-1">
                <table class="min-w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-[10px] uppercase tracking-wide text-gray-500">
                            <template x-for="(attr, ai) in attributes" :key="'head-'+ai">
                                <th class="px-2 py-1.5 font-semibold whitespace-nowrap" x-text="attr.name"></th>
                            </template>
                            <th class="px-2 py-1.5 font-semibold whitespace-nowrap">{{ __('vendor.price') }} (₹)</th>
                            <th class="px-2 py-1.5 font-semibold whitespace-nowrap">{{ __('vendor.stock') }}</th>
                            <th class="px-2 py-1.5 font-semibold whitespace-nowrap w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(variant, vi) in variants" :key="'row-'+vi">
                            <tr class="border-b border-gray-100 align-top">
                                <template x-for="(attr, ai) in attributes" :key="'cell-'+vi+'-'+ai">
                                    <td class="px-2 py-1.5 min-w-[7rem]">
                                        <input type="text"
                                               class="{{ $in }}"
                                               x-model="variant.attributes[attr.slug]"
                                               :name="'variants['+vi+'][attributes]['+attr.slug+']'"
                                               :placeholder="attr.name">
                                    </td>
                                </template>
                                <td class="px-2 py-1.5 min-w-[6rem]">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           required
                                           class="{{ $in }}"
                                           x-model="variant.price"
                                           :name="'variants['+vi+'][price]'"
                                           placeholder="0.00">
                                </td>
                                <td class="px-2 py-1.5 min-w-[5rem]">
                                    <input type="number"
                                           min="0"
                                           required
                                           class="{{ $in }}"
                                           x-model="variant.stock"
                                           :name="'variants['+vi+'][stock]'"
                                           placeholder="1">
                                </td>
                                <td class="px-2 py-1.5">
                                    <button type="button"
                                            class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-30"
                                            @click="removeVariantRow(vi)"
                                            :disabled="variants.length <= 1"
                                            :title="@js(__('vendor.remove'))">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                    </button>
                                    <template x-if="variant.id">
                                        <input type="hidden" :name="'variants['+vi+'][id]'" :value="variant.id">
                                    </template>
                                    <input type="hidden" :name="'variants['+vi+'][damaged_stock]'" :value="variant.damaged_stock ?? 0">
                                    <input type="hidden" :name="'variants['+vi+'][maintenance_stock]'" :value="variant.maintenance_stock ?? 0">
                                    <input type="hidden" :name="'variants['+vi+'][manage_stock]'" :value="itemManageStock ? 1 : 0">
                                    <input type="hidden" :name="'variants['+vi+'][is_available]'" :value="itemIsAvailable ? 1 : 0">
                                    <input type="hidden" :name="'variants['+vi+'][is_active]'" :value="itemIsActive ? 1 : 0">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            @error('variants')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            @error('variants.*')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <div x-show="attributes.length === 0" class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 py-4 text-center">
            <p class="text-xs text-gray-600">{{ __('vendor.item_variant_add_attribute_first') }}</p>
        </div>
    </div>

    {{-- Hidden attribute metadata for submit --}}
    <template x-for="(attr, ai) in attributes" :key="'hidden-attr-'+ai">
        <div class="hidden">
            <input type="hidden" :name="'variant_attributes['+ai+'][name]'" :value="attr.name">
            <input type="hidden" :name="'variant_attributes['+ai+'][slug]'" :value="attr.slug">
            <input type="hidden" :name="'variant_attributes['+ai+'][sort_order]'" :value="ai">
            <template x-if="attr.id">
                <input type="hidden" :name="'variant_attributes['+ai+'][id]'" :value="attr.id">
            </template>
        </div>
    </template>
@if(!$embedded)
</section>
@else
</div>
@endif
