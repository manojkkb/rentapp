@extends('admin.layouts.app')

@section('title', ($vendor->exists ? 'Edit' : 'Create') . ' Vendor - Admin')

@section('content')
@php
    $isEdit = $vendor->exists;
    $languages = ['en' => 'English', 'hi' => 'Hindi', 'bn' => 'Bengali', 'mr' => 'Marathi', 'te' => 'Telugu', 'ta' => 'Tamil', 'gu' => 'Gujarati', 'ur' => 'Urdu', 'kn' => 'Kannada', 'or' => 'Odia', 'ml' => 'Malayalam', 'pa' => 'Punjabi'];
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">{{ $isEdit ? 'Edit' : 'Create' }} vendor</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @if($isEdit)
                <a href="{{ route('admin.vendors.show', $vendor) }}" class="text-green-600 hover:text-green-700">← Back to vendor</a>
            @else
                <a href="{{ route('admin.vendors.index') }}" class="text-green-600 hover:text-green-700">← Back to all vendors</a>
            @endif
        </p>
    </div>

    @include('admin.users.partials.alerts')

    <form method="POST"
          action="{{ $isEdit ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}"
          enctype="multipart/form-data"
          class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Primary owner (user account) <span class="text-red-500">*</span></label>
            <select name="user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Select user…</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('user_id', $vendor->user_id) === $owner->id)>
                        {{ $owner->name ?: 'Unnamed' }}
                        @if($owner->mobile) — {{ $owner->mobile }} @endif
                        @if($owner->email) ({{ $owner->email }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Business name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Owner display name</label>
                <input type="text" name="owner_name" value="{{ old('owner_name', $vendor->owner_name) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">URL slug</label>
                <input type="text" name="slug" value="{{ old('slug', $vendor->slug) }}" placeholder="auto-generated from name"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Business category</label>
                <select name="business_category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((int) old('business_category_id', $vendor->business_category_id) === $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Logo</label>
            @if($isEdit && $vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="" class="mb-2 h-16 w-16 rounded-lg object-cover">
            @endif
            <input type="file" name="logo" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:font-semibold file:text-green-700">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Address line 1</label>
                <input type="text" name="address_line1" value="{{ old('address_line1', $vendor->address_line1) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Address line 2</label>
                <input type="text" name="address_line2" value="{{ old('address_line2', $vendor->address_line2) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">City</label>
                <input type="text" name="city" value="{{ old('city', $vendor->city) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">State</label>
                <input type="text" name="state" value="{{ old('state', $vendor->state) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Postal code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $vendor->postal_code) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Country</label>
                <input type="text" name="country" value="{{ old('country', $vendor->country ?? 'India') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">GST number</label>
                <input type="text" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Language</label>
                <select name="language" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @foreach($languages as $code => $label)
                        <option value="{{ $code }}" @selected(old('language', $vendor->language ?? 'en') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-6 border-t border-gray-100 pt-4 dark:border-gray-700">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $vendor->is_verified)) class="rounded">
                KYC verified
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor->is_active ?? true)) class="rounded">
                Active (accepts rentals)
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                {{ $isEdit ? 'Save changes' : 'Create vendor' }}
            </button>
            <a href="{{ route('admin.vendors.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
