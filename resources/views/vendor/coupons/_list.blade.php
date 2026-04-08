@forelse($coupons as $coupon)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow coupon-card" 
         data-coupon-id="{{ $coupon->id }}">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <!-- Left: Coupon Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-2">
                        <span data-coupon-badge="{{ $coupon->id }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold tracking-wider border
                            {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                            {{ $coupon->code }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                            {{ $coupon->type === 'percent' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                            @if($coupon->type === 'percent')
                                {{ rtrim(rtrim(number_format($coupon->value, 2), '0'), '.') }}% {{ __('vendor.off') }}
                            @else
                                ₹{{ number_format($coupon->value, 2) }} {{ __('vendor.off') }}
                            @endif
                        </span>
                        @if($coupon->is_active)
                            @if($coupon->end_date && now()->gt($coupon->end_date))
                                <span data-coupon-status="{{ $coupon->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600">
                                    <i class="fas fa-clock mr-1"></i>{{ __('vendor.expired') }}
                                </span>
                            @elseif($coupon->start_date && now()->lt($coupon->start_date))
                                <span data-coupon-status="{{ $coupon->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-50 text-yellow-700">
                                    <i class="fas fa-clock mr-1"></i>{{ __('vendor.scheduled') }}
                                </span>
                            @else
                                <span data-coupon-status="{{ $coupon->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-check-circle mr-1"></i>{{ __('vendor.active') }}
                                </span>
                            @endif
                        @else
                            <span data-coupon-status="{{ $coupon->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                <i class="fas fa-ban mr-1"></i>{{ __('vendor.inactive') }}
                            </span>
                        @endif
                    </div>
                    @if($coupon->name)
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $coupon->name }}</p>
                    @endif
                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                        @if($coupon->min_order_amount > 0)
                            <span><i class="fas fa-shopping-bag mr-1"></i>{{ __('vendor.min') }} ₹{{ number_format($coupon->min_order_amount, 0) }}</span>
                        @endif
                        @if($coupon->max_discount_amount)
                            <span><i class="fas fa-arrow-down mr-1"></i>{{ __('vendor.max') }} ₹{{ number_format($coupon->max_discount_amount, 0) }}</span>
                        @endif
                        @if($coupon->usage_limit)
                            <span><i class="fas fa-sync-alt mr-1"></i>{{ $coupon->used_count }}/{{ $coupon->usage_limit }} {{ __('vendor.used') }}</span>
                        @else
                            <span><i class="fas fa-infinity mr-1"></i>{{ __('vendor.unlimited') }}</span>
                        @endif
                        @if($coupon->start_date || $coupon->end_date)
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                @if($coupon->start_date && $coupon->end_date)
                                    {{ $coupon->start_date->format('d M') }} - {{ $coupon->end_date->format('d M Y') }}
                                @elseif($coupon->end_date)
                                    {{ __('vendor.until') }} {{ $coupon->end_date->format('d M Y') }}
                                @else
                                    {{ __('vendor.from') }} {{ $coupon->start_date->format('d M Y') }}
                                @endif
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center space-x-1 flex-shrink-0">
                    <!-- Toggle -->
                    <button data-coupon-toggle="{{ $coupon->id }}" onclick="toggleCoupon({{ $coupon->id }})" 
                            class="p-2 rounded-lg transition-colors {{ $coupon->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-gray-400 hover:bg-gray-100' }}"
                            title="{{ $coupon->is_active ? __('vendor.deactivate') : __('vendor.activate') }}">
                        <i class="fas {{ $coupon->is_active ? 'fa-toggle-on text-lg' : 'fa-toggle-off text-lg' }}"></i>
                    </button>
                    <!-- Edit -->
                    <button onclick="editCoupon({{ $coupon->id }})" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('vendor.edit') }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <!-- Delete -->
                    <button onclick="confirmDeleteCoupon({{ $coupon->id }}, '{{ $coupon->code }}')" 
                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('vendor.delete') }}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-span-full text-center py-16">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
            <i class="fas fa-ticket-alt text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-1">{{ __('vendor.no_coupons_found') }}</h3>
        <p class="text-sm text-gray-500">{{ __('vendor.create_first_coupon') }}</p>
    </div>
@endforelse

@if($coupons->hasPages())
    <div class="mt-4">
        {{ $coupons->links() }}
    </div>
@endif
