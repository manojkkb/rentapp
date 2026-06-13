@props([
    'url',
    'label' => null,
    'variant' => 'primary',
    'class' => '',
])
@php
    $label = $label ?? __('vendor.store_whatsapp_chat');
    $base = match ($variant) {
        'outline' => $theme['classes']['btn'].' flex items-center justify-center gap-2 border-2 border-[#25D366] bg-white text-sm font-semibold text-[#128C7E] hover:bg-[#25D366]/10',
        'soft' => $theme['classes']['btn'].' flex items-center justify-center gap-2 bg-[#25D366]/10 text-sm font-semibold text-[#128C7E] hover:bg-[#25D366]/20',
        'compact' => 'inline-flex items-center gap-1.5 text-xs font-semibold text-[#128C7E] hover:underline',
        default => $theme['classes']['btn'].' flex items-center justify-center gap-2 bg-[#25D366] text-sm font-semibold text-white shadow-sm hover:bg-[#20BD5A]',
    };
@endphp
<a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
   {{ $attributes->merge(['class' => trim($base.' '.$class)]) }}>
    <i class="fab fa-whatsapp text-base" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</a>
