@extends('admin.layouts.app')

@section('title', ($category->exists ? 'Edit' : 'Create') . ' Business Category - Admin')

@section('content')
@php $isEdit = $category->exists; @endphp
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">{{ $isEdit ? 'Edit' : 'Create' }} business category</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.business-categories.index') }}" class="text-green-600 hover:text-green-700">← Back to categories</a>
        </p>
    </div>

    @include('admin.users.partials.alerts')

    <form method="POST"
          action="{{ $isEdit ? route('admin.business-categories.update', $category) : route('admin.business-categories.store') }}"
          class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">URL slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="auto-generated"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Parent category</label>
                <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">— Top-level —</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Icon class (Font Awesome)</label>
            <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="e.g. fas fa-car"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @if(old('icon', $category->icon))
                <p class="mt-2 text-sm text-gray-500">Preview: <i class="{{ old('icon', $category->icon) }} text-lg text-green-600"></i></p>
            @endif
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" rows="3"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description', $category->description) }}</textarea>
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded">
            Active (shown to vendors during signup)
        </label>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                {{ $isEdit ? 'Save changes' : 'Create category' }}
            </button>
            <a href="{{ route('admin.business-categories.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
