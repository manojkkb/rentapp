@extends('vendor.layouts.app')

@section('title', __('vendor.edit_item'))
@section('page-title', __('vendor.edit_item'))

@section('content')
@php
    $fc = 'w-full min-w-0 rounded-md border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $fl = 'block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-0.5';
    $fh = 'mt-0.5 text-[10px] leading-snug text-gray-500';
    $fs = 'rounded-lg border border-gray-200 bg-gray-50/60 p-3 sm:p-3.5 space-y-2';
    $fst = 'text-[11px] font-bold uppercase tracking-wide text-emerald-900 border-b border-emerald-200/70 pb-2 mb-1';
    $fstPlain = 'text-[11px] font-bold uppercase tracking-wide text-emerald-900 mb-2';
@endphp

<div class="mx-auto max-w-6xl space-y-2">
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
        <a href="{{ route('vendor.items.index') }}"
           class="inline-flex items-center text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
            {{ __('vendor.back_to_items') }}
        </a>
        <span class="hidden text-gray-300 sm:inline">·</span>
        <span class="font-semibold text-gray-900">{{ __('vendor.edit_item') }}</span>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <form action="{{ route('vendor.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-3 p-3 sm:p-4 lg:grid-cols-12 lg:gap-x-4 lg:gap-y-4">
                <section class="{{ $fs }} lg:col-span-5" aria-labelledby="item-section-listing">
                    <h2 id="item-section-listing" class="{{ $fst }}">{{ __('vendor.item_form_section_listing') }}</h2>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="{{ $fl }}">{{ __('vendor.item_name') }} <span class="text-red-500">*</span></label>
                            <p class="{{ $fh }}">{{ __('vendor.field_hint_item_name') }}</p>
                            <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" required
                                   class="{{ $fc }} @error('name') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.item_name_placeholder') }}">
                            @error('name')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] leading-snug text-gray-500">
                                <span class="font-medium text-gray-600">{{ __('vendor.current_url') }}:</span>
                                <code class="max-w-full truncate rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-800">{{ $item->slug }}</code>
                                <span class="text-gray-400">—</span>
                                <span>{{ __('vendor.slug_auto_update') }}</span>
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="category_id" class="{{ $fl }}">{{ __('vendor.category') }} <span class="text-red-500">*</span></label>
                            <p class="{{ $fh }}">{{ __('vendor.field_hint_category') }}</p>
                            <select id="category_id" name="category_id" required class="{{ $fc }} @error('category_id') border-red-500 @enderror">
                                <option value="">{{ __('vendor.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                            @if($categories->isEmpty())
                                <p class="mt-0.5 text-[10px] text-orange-600">{{ __('vendor.no_categories_found') }} <a href="{{ route('vendor.categories.create') }}" class="underline">{{ __('vendor.create_category_first') }}</a></p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label for="photo" class="{{ $fl }}">{{ __('vendor.item_photo') }} <span class="font-normal normal-case text-gray-400">({{ __('vendor.optional') }})</span></label>
                        <p class="{{ $fh }}">{{ __('vendor.field_hint_photo') }}</p>
                        @if($item->photo_url)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ $item->photo_url }}" alt="" class="h-14 w-14 shrink-0 rounded-md border border-gray-200 object-cover">
                                <span class="text-[10px] leading-snug text-gray-500">{{ __('vendor.current_image') }}</span>
                            </div>
                        @endif
                        <input type="file" id="photo" name="photo" accept="image/*"
                               class="js-item-image-input block w-full text-xs text-gray-600 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-emerald-700 {{ $fc }} @error('photo') border-red-500 @enderror">
                        <p class="mt-1 text-[10px] text-gray-500">{{ __('vendor.item_photo_crop_hint') }}</p>
                        @error('photo')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="{{ $fl }}">{{ __('vendor.description') }} <span class="font-normal normal-case text-gray-400">({{ __('vendor.optional') }})</span></label>
                        <p class="{{ $fh }}">{{ __('vendor.field_hint_description') }}</p>
                        <textarea id="description" name="description" rows="3"
                                  class="{{ $fc }} resize-y @error('description') border-red-500 @enderror"
                                  placeholder="{{ __('vendor.description_placeholder') }}">{{ old('description', $item->description) }}</textarea>
                        @error('description')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                </section>

                <div class="flex flex-col gap-3 lg:col-span-7">
                    <section class="{{ $fs }}" aria-labelledby="item-section-pricing">
                        <h2 id="item-section-pricing" class="{{ $fst }}">{{ __('vendor.item_form_section_pricing') }}</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="price" class="{{ $fl }}">{{ __('vendor.price') }} (₹) <span class="text-red-500">*</span></label>
                                <p class="{{ $fh }}">{{ __('vendor.field_hint_price') }}</p>
                                <input type="number" id="price" name="price" value="{{ old('price', $item->price) }}" step="0.01" min="0" required
                                       class="{{ $fc }} @error('price') border-red-500 @enderror" placeholder="0.00">
                                @error('price')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="price_type" class="{{ $fl }}">{{ __('vendor.price_type') }} <span class="text-red-500">*</span></label>
                                <p class="{{ $fh }}">{{ __('vendor.field_hint_price_type') }}</p>
                                <select id="price_type" name="price_type" required class="{{ $fc }} @error('price_type') border-red-500 @enderror">
                                    @foreach($priceTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('price_type', $item->price_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('price_type')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <div class="space-y-1.5">
                        <h2 id="item-section-policies" class="{{ $fstPlain }}">{{ __('vendor.item_form_section_policies') }}</h2>
                        @include('vendor.items.partials.item-policy-fields-compact', ['item' => $item])
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 bg-gray-50/90 px-3 py-2.5 sm:px-4">
                <h2 id="item-section-visibility" class="{{ $fstPlain }}">{{ __('vendor.item_form_section_visibility') }}</h2>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-2">
                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-start sm:gap-6">
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                            <div>
                                <label class="flex cursor-pointer items-center gap-1.5">
                                    <input type="checkbox" name="is_available" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600" {{ old('is_available', $item->is_available) ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700">{{ __('vendor.available_for_rent') }}</span>
                                </label>
                                <p class="mt-0.5 pl-5 text-[10px] leading-snug text-gray-500">{{ __('vendor.field_hint_is_available') }}</p>
                            </div>
                            <div>
                                <label class="flex cursor-pointer items-center gap-1.5">
                                    <input type="checkbox" name="is_active" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700">{{ __('vendor.active') }}</span>
                                </label>
                                <p class="mt-0.5 pl-5 text-[10px] leading-snug text-gray-500">{{ __('vendor.field_hint_is_active') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                onclick="if(confirm(@json(__('vendor.confirm_delete')))) document.getElementById('delete-item-form').submit();"
                                class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-50">
                            <i class="fas fa-trash-alt text-[10px]" aria-hidden="true"></i>
                            {{ __('vendor.delete') }}
                        </button>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 pt-2 sm:border-0 sm:pt-0">
                        <a href="{{ route('vendor.items.index') }}"
                           class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100">{{ __('vendor.cancel') }}</a>
                        <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-save mr-1"></i>{{ __('vendor.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <form id="delete-item-form" action="{{ route('vendor.items.destroy', $item->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@include('vendor.items.partials.item-image-crop-modal')
@endsection
