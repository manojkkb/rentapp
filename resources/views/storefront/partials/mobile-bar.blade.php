@if(($whatsappEnabled ?? false) && ($whatsappContactUrl ?? null))
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white/95 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] backdrop-blur-md sm:hidden">
        <div class="mx-auto flex max-w-lg gap-2">
            @include('storefront.partials.whatsapp-button', [
                'url' => $whatsappContactUrl,
                'label' => __('vendor.store_whatsapp_chat'),
                'variant' => 'primary',
                'class' => 'h-11 flex-1',
            ])
            @if($store->business_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}"
                   class="{{ $theme['classes']['btn'] }} flex h-11 flex-1 items-center justify-center gap-2 border border-gray-200 bg-white text-sm font-semibold text-gray-700">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                    {{ __('vendor.call') }}
                </a>
            @endif
        </div>
    </div>
@elseif($store->business_phone)
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white/95 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] backdrop-blur-md sm:hidden">
        <div class="mx-auto max-w-lg">
            <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}"
               class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-11 w-full items-center justify-center gap-2 text-sm font-semibold">
                <i class="fas fa-phone" aria-hidden="true"></i>
                {{ __('vendor.call') }}
            </a>
        </div>
    </div>
@endif
