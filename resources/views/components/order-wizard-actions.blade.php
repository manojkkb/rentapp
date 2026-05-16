{{-- Sticky above vendor mobile bottom nav (h-16); in document flow from md up --}}
<div {{ $attributes->class([
    'order-wizard-actions flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2',
    'max-md:fixed max-md:z-[45] max-md:inset-x-0 max-md:bottom-16 max-md:border-t max-md:border-gray-200 max-md:bg-white',
    'max-md:px-3 max-md:py-3 max-md:pb-[max(0.75rem,env(safe-area-inset-bottom))] max-md:shadow-[0_-4px_20px_rgba(0,0,0,0.07)]',
    'md:static md:z-auto md:shadow-none md:bg-transparent',
]) }}>
    {{ $slot }}
</div>
