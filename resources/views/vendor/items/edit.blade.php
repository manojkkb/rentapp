@extends('vendor.layouts.app')

@section('title', __('vendor.edit_item'))
@section('page-title', __('vendor.edit_item'))
@section('main_bottom_class', 'pb-36 md:pb-8')

@section('content')
@php
    $ifc = 'block w-full min-h-[40px] rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500';
    $ilabel = 'mb-0.5 block text-sm font-medium text-gray-800';
    $ihint = 'mt-0.5 text-[11px] leading-snug text-gray-500 max-sm:hidden';
    $ierror = 'mt-0.5 text-xs text-red-600';
    $icard = 'scroll-mt-20 overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm';
    $ihead = 'flex items-center gap-2.5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3';
    $inum = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-xs font-bold text-white';
    $ibody = 'space-y-3 p-3 sm:space-y-4 sm:p-4';
    $igrid2 = 'grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4';

    $sections = [
        ['id' => 'item-section-general', 'num' => 1, 'label' => __('vendor.item_form_section_general')],
        ['id' => 'item-section-images', 'num' => 2, 'label' => __('vendor.item_form_section_images')],
        ['id' => 'item-section-variants', 'num' => 3, 'label' => __('vendor.item_form_section_variants')],
        ['id' => 'item-section-pricing', 'num' => 4, 'label' => __('vendor.item_form_section_combined_pricing')],
    ];
    $o = fn (string $key, $default) => old($key, $item->{$key} ?? $default);
@endphp

<div class="mx-auto w-full max-w-4xl">
    <header class="mb-3 sm:mb-4">
        <div class="mb-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
            <a href="{{ route('vendor.items.index') }}"
               class="inline-flex items-center gap-1.5 text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_items') }}
            </a>
            <span class="hidden text-gray-300 sm:inline">·</span>
            <a href="{{ route('vendor.items.show', $item) }}"
               class="hidden truncate font-medium text-gray-600 hover:text-emerald-600 sm:inline">{{ $item->name }}</a>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.edit_item') }}</h1>
                <p class="mt-0.5 truncate text-sm text-gray-500">{{ $item->name }}</p>
            </div>
            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('vendor.items.show', $item) }}"
                   class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit" form="item-edit-form"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-save text-xs" aria-hidden="true"></i>
                    {{ __('vendor.save_changes') }}
                </button>
            </div>
        </div>
    </header>

    <nav class="sticky top-0 z-20 mb-3 overflow-x-auto rounded-lg border border-gray-200 bg-white/95 py-1.5 shadow-sm backdrop-blur-md sm:hidden"
         aria-label="{{ __('vendor.item_form_section_nav') }}">
        <div class="flex min-w-max gap-1 px-1.5">
            @foreach ($sections as $section)
                <a href="#{{ $section['id'] }}"
                   class="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1.5 text-[11px] font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-800">
                    <span class="flex h-4 w-4 items-center justify-center rounded bg-emerald-100 text-[9px] font-bold text-emerald-800">{{ $section['num'] }}</span>
                    {{ $section['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div x-data="itemVariantForm(@js($variantFormState))">
        <form id="item-edit-form" action="{{ route('vendor.items.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-3 sm:space-y-4">
            @csrf
            @method('PUT')

            {{-- 1. General + visibility --}}
            <section id="item-section-general" class="{{ $icard }}">
                <div class="{{ $ihead }}">
                    <span class="{{ $inum }}">1</span>
                    <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.item_form_section_general') }}</h2>
                </div>
                <div class="{{ $ibody }}">
                    <div class="{{ $igrid2 }}">
                        <div class="sm:col-span-2">
                            <label for="name" class="{{ $ilabel }}">{{ __('vendor.item_name') }} <span class="text-red-500">*</span></label>
                            <p class="{{ $ihint }}">{{ __('vendor.field_hint_item_name') }}</p>
                            <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" required
                                   class="{{ $ifc }} @error('name') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.item_name_placeholder') }}">
                            @error('name')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                            <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-snug text-gray-500">
                                <span class="font-medium text-gray-600">{{ __('vendor.current_url') }}:</span>
                                <code class="max-w-full truncate rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-800">{{ $item->slug }}</code>
                                <span class="text-gray-400">—</span>
                                <span>{{ __('vendor.slug_auto_update') }}</span>
                            </p>
                        </div>

                        @include('vendor.items.partials.category-picker-searchable', [
                            'categories' => $categories,
                            'selectedCategoryId' => $item->category_id,
                            'inputClass' => $ifc,
                            'labelClass' => $ilabel,
                            'hintClass' => $ihint,
                        ])

                        <div>
                            <label for="item_code" class="{{ $ilabel }}">{{ __('vendor.item_code') }} <span class="text-red-500">*</span></label>
                            <p class="{{ $ihint }}">{{ __('vendor.field_hint_item_code') }}</p>
                            <input type="text" id="item_code" name="item_code" value="{{ old('item_code', $item->item_code) }}" required maxlength="32"
                                   class="{{ $ifc }} font-mono uppercase @error('item_code') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.item_code_placeholder') }}"
                                   autocapitalize="characters" spellcheck="false">
                            @error('item_code')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="condition_status" class="{{ $ilabel }}">{{ __('vendor.condition_status') }} <span class="text-red-500">*</span></label>
                            <p class="{{ $ihint }}">{{ __('vendor.field_hint_condition') }}</p>
                            <select id="condition_status" name="condition_status" required
                                    class="{{ $ifc }} @error('condition_status') border-red-500 @enderror">
                                @foreach (\App\Models\Items::conditionStatusOptions() as $key => $label)
                                    <option value="{{ $key }}" @selected($o('condition_status', 'good') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition_status')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="{{ $ilabel }}">{{ __('vendor.description') }} <span class="font-normal text-gray-400">({{ __('vendor.optional') }})</span></label>
                            <p class="{{ $ihint }}">{{ __('vendor.field_hint_description') }}</p>
                            <textarea id="description" name="description" rows="2"
                                      class="{{ $ifc }} min-h-[4.5rem] resize-y @error('description') border-red-500 @enderror"
                                      placeholder="{{ __('vendor.description_placeholder') }}">{{ old('description', $item->description) }}</textarea>
                            @error('description')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.item_form_section_visibility') }}</p>
                        <div class="{{ $igrid2 }}">
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 hover:border-emerald-200">
                                <input type="checkbox" name="is_available" value="1"
                                       class="h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                       {{ old('is_available', $item->is_available) ? 'checked' : '' }}>
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ __('vendor.available_for_rent') }}</span>
                                    <span class="{{ $ihint }}">{{ __('vendor.field_hint_is_available') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 hover:border-emerald-200">
                                <input type="checkbox" name="is_active" value="1"
                                       class="h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                       {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ __('vendor.active') }}</span>
                                    <span class="{{ $ihint }}">{{ __('vendor.field_hint_is_active') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. Images --}}
            <section id="item-section-images" class="{{ $icard }}">
                <div class="{{ $ihead }}">
                    <span class="{{ $inum }}">2</span>
                    <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.item_form_section_images') }}</h2>
                </div>
                <div class="{{ $ibody }}">
                    @include('vendor.items.partials.form.images-block', ['item' => $item])
                </div>
            </section>

            {{-- 3. Variants --}}
            <section id="item-section-variants" class="{{ $icard }}">
                <div class="{{ $ihead }}">
                    <span class="{{ $inum }}">3</span>
                    <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.item_form_section_variants') }}</h2>
                </div>
                <div class="{{ $ibody }}">
                    @include('vendor.items.partials.item-variant-manager', [
                        'embedded' => true,
                        'formInputClass' => $ifc,
                    ])
                </div>
            </section>

            {{-- 4. Pricing, fees & inventory --}}
            <section class="{{ $icard }}">
                <div class="{{ $ihead }}">
                    <span class="{{ $inum }}">4</span>
                    <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.item_form_section_combined_pricing') }}</h2>
                </div>
                <div class="{{ $ibody }}">
                    @include('vendor.items.partials.form.pricing-fees-inventory', ['item' => $item])
                </div>
            </section>

            {{-- Desktop actions --}}
            <div class="hidden flex-wrap items-center justify-between gap-2 sm:flex">
                <button type="button"
                        onclick="if(confirm(@json(__('vendor.confirm_delete')))) document.getElementById('delete-item-form').submit();"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3.5 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                    <i class="fas fa-trash-alt text-xs" aria-hidden="true"></i>
                    {{ __('vendor.delete') }}
                </button>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vendor.items.show', $item) }}"
                       class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('vendor.cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fas fa-save text-xs" aria-hidden="true"></i>
                        {{ __('vendor.save_changes') }}
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-item-form" action="{{ route('vendor.items.destroy', $item) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

{{-- Mobile sticky submit --}}
<div class="fixed inset-x-0 bottom-16 z-[60] border-t border-gray-200 bg-white px-3 py-2.5 shadow-lg md:hidden"
     style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
    <div class="mx-auto flex max-w-4xl items-center gap-2">
        <button type="button"
                onclick="if(confirm(@json(__('vendor.confirm_delete')))) document.getElementById('delete-item-form').submit();"
                class="inline-flex min-h-[44px] w-10 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600"
                aria-label="{{ __('vendor.delete') }}">
            <i class="fas fa-trash-alt text-sm" aria-hidden="true"></i>
        </button>
        <a href="{{ route('vendor.items.show', $item) }}"
           class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700">
            {{ __('vendor.cancel') }}
        </a>
        <button type="submit" form="item-edit-form"
                class="inline-flex min-h-[44px] flex-[1.35] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 text-sm font-semibold text-white">
            <i class="fas fa-save text-xs" aria-hidden="true"></i>
            {{ __('vendor.save_changes') }}
        </button>
    </div>
</div>

@include('vendor.items.partials.item-image-crop-modal')
@endsection
