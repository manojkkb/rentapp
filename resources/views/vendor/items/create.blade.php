@extends('vendor.layouts.app')

@section('title', __('vendor.add_item'))
@section('page-title', __('vendor.add_item'))
@section('main_bottom_class', 'pb-36 md:pb-8')

@section('content')
@php
    $ifc = 'block w-full min-h-[44px] rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-base sm:text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500';
    $ilabel = 'mb-1 block text-sm font-medium text-gray-800';
    $ihint = 'mt-1 text-xs leading-snug text-gray-500';
    $ierror = 'mt-1 text-xs font-medium text-red-600';
    $ireq = '<span class="text-red-500" aria-hidden="true">*</span>';
    $icard = 'scroll-mt-24 overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80';
    $ihead = 'flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-slate-50 via-white to-emerald-50/30 px-4 py-3.5 sm:px-5 sm:py-4';
    $inum = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm shadow-emerald-600/20';
    $ibody = 'space-y-4 p-4 sm:p-5';
    $igrid2 = 'grid grid-cols-1 gap-4 sm:grid-cols-2';

    $sections = [
        ['id' => 'item-section-general', 'num' => 1, 'label' => __('vendor.item_form_section_general')],
        ['id' => 'item-section-images', 'num' => 2, 'label' => __('vendor.item_form_section_images')],
        ['id' => 'item-section-variants', 'num' => 3, 'label' => __('vendor.item_form_section_variants')],
        ['id' => 'item-section-pricing', 'num' => 4, 'label' => __('vendor.item_form_section_combined_pricing')],
    ];
    $o = fn (string $key, $default) => old($key, $default);
@endphp

<div class="mx-auto w-full max-w-4xl">
    {{-- Header --}}
    <header class="mb-3 sm:mb-4">
        <a href="{{ route('vendor.items.index') }}"
           class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back_to_items') }}
        </a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.add_new_item_title') }}</h1>
            </div>
            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('vendor.items.index') }}"
                   class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit" form="item-create-form"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('vendor.add_item') }}
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile jump nav --}}
    <nav class="sticky top-0 z-20 mb-3 overflow-x-auto rounded-xl border border-gray-200 bg-white/95 py-2 shadow-sm backdrop-blur-md sm:hidden"
         aria-label="{{ __('vendor.item_form_section_nav') }}">
        <div class="flex min-w-max gap-1.5 px-2">
            @foreach ($sections as $section)
                <a href="#{{ $section['id'] }}"
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-800">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-emerald-100 text-[10px] font-bold text-emerald-800">{{ $section['num'] }}</span>
                    {{ $section['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div class="mb-3 rounded-xl border border-amber-100 bg-amber-50/80 px-3.5 py-2.5 text-xs text-amber-900 sm:text-sm">
        <i class="fas fa-asterisk mr-1.5 text-[10px] text-red-500" aria-hidden="true"></i>
        {{ __('vendor.required_fields_note') }}
    </div>

    <div x-data="itemVariantForm(@js($variantFormState))">
        <form id="item-create-form" action="{{ route('vendor.items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 sm:space-y-4">
            @csrf

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
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="{{ $ifc }} @error('name') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.item_name_placeholder') }}">
                            @error('name')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                        </div>

                        @include('vendor.items.partials.category-picker-searchable', [
                            'categories' => $categories,
                            'selectedCategoryId' => old('category_id'),
                            'inputClass' => $ifc,
                            'labelClass' => $ilabel,
                            'hintClass' => $ihint,
                        ])

                        <div>
                            <label for="item_code" class="{{ $ilabel }}">{{ __('vendor.item_code_optional_label') }}</label>
                            <p class="{{ $ihint }}">{{ __('vendor.field_hint_item_code') }}</p>
                            <input type="text" id="item_code" name="item_code" value="{{ old('item_code') }}" maxlength="32"
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
                                      placeholder="{{ __('vendor.description_placeholder') }}">{{ old('description') }}</textarea>
                            @error('description')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.item_form_section_visibility') }}</p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 hover:border-emerald-200">
                                <input type="checkbox" name="is_available" value="1"
                                       x-model="itemIsAvailable"
                                       class="h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ __('vendor.available_for_rent') }}</span>
                                    <span class="{{ $ihint }}">{{ __('vendor.field_hint_is_available') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 hover:border-emerald-200">
                                <input type="checkbox" name="is_active" value="1"
                                       x-model="itemIsActive"
                                       class="h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ __('vendor.active') }}</span>
                                    <span class="{{ $ihint }}">{{ __('vendor.field_hint_is_active') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 hover:border-emerald-200 sm:col-span-2 lg:col-span-1">
                                <input type="checkbox" name="manage_stock" value="1"
                                       x-model="itemManageStock"
                                       class="h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-800">{{ __('vendor.track_stock_quantity') }}</span>
                                    <span class="{{ $ihint }}">{{ __('vendor.field_hint_manage_stock') }}</span>
                                </span>
                            </label>
                        </div>
                        <p x-show="hasVariants" x-cloak class="mt-2 text-xs leading-snug text-gray-500">
                            {{ __('vendor.item_visibility_applies_variants') }}
                        </p>
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
                    @include('vendor.items.partials.form.images-block')
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

            {{-- 4. Pricing, fees & inventory (combined) --}}
            <section class="{{ $icard }}">
                <div class="{{ $ihead }}">
                    <span class="{{ $inum }}">4</span>
                    <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.item_form_section_combined_pricing') }}</h2>
                </div>
                <div class="{{ $ibody }}">
                    @include('vendor.items.partials.form.pricing-fees-inventory', ['item' => null])
                </div>
            </section>

            {{-- Desktop submit --}}
            <div class="hidden items-center justify-end gap-2 sm:flex">
                <a href="{{ route('vendor.items.index') }}"
                   class="inline-flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('vendor.add_item') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Mobile sticky submit --}}
<div class="fixed inset-x-0 bottom-16 z-[60] border-t border-gray-200 bg-white px-3 py-2.5 shadow-lg md:hidden"
     style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
    <div class="mx-auto flex max-w-4xl items-center gap-2">
        <a href="{{ route('vendor.items.index') }}"
           class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700">
            {{ __('vendor.cancel') }}
        </a>
        <button type="submit" form="item-create-form"
                class="inline-flex min-h-[44px] flex-[1.35] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 text-sm font-semibold text-white">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.add_item') }}
        </button>
    </div>
</div>

@include('vendor.items.partials.item-image-crop-modal')
@endsection
