@php
    $fieldId = $id ?? 'rte_'.preg_replace('/[^a-z0-9_-]/i', '_', $name);
    $minHeight = max(8, (int) ($rows ?? 8)) * 1.5;
@endphp

<div class="rich-text-field" data-rich-text>
    @if(! empty($label))
        <label for="{{ $fieldId }}" class="mb-1 block text-sm font-semibold text-gray-800">{{ $label }}</label>
    @endif
    <div class="rich-text-editor-wrap overflow-hidden rounded-lg border border-gray-300 bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/25"
         style="--rich-text-min-height: {{ $minHeight }}rem;">
        <div class="rich-text-editor"
             data-placeholder="{{ $placeholder ?? '' }}"
             role="textbox"
             aria-multiline="true"></div>
    </div>
    <textarea id="{{ $fieldId }}"
              name="{{ $name }}"
              class="sr-only"
              aria-hidden="true">{{ old($name, $value ?? '') }}</textarea>
    @if(! empty($hint))
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
