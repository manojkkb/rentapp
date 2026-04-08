@extends('vendor.layouts.app')

@section('title', __('vendor.cart_details'))
@section('page-title', __('vendor.cart_details'))

@section('content')
<div id="cartApp" x-data="{
    showAddItem: false,
    addedItems: {{ $cart->items->pluck('quantity', 'item_id')->toJson() }},
    addingItem: null,
    updatingItem: null,
    editingItem: null,
    editQuantity: 1,
    searchQuery: '',
    selectedCategory: '',
    items: {{ $availableItems->toJson() }},
    isAdded(itemId) {
        return this.addedItems[itemId] !== undefined;
    },
    getAddedQty(itemId) {
        return this.addedItems[itemId] || 1;
    },
    addItemToCart(itemId) {
        if (this.addingItem === itemId) return;
        this.addingItem = itemId;
        addItemToCartAjax(itemId, 1, this);
    },
    incrementCartQty(itemId) {
        if (this.updatingItem === itemId) return;
        this.updatingItem = itemId;
        const newQty = this.getAddedQty(itemId) + 1;
        this.addedItems[itemId] = newQty;
        updateModalItemQty(itemId, newQty, this);
    },
    decrementCartQty(itemId) {
        if (this.updatingItem === itemId) return;
        this.updatingItem = itemId;
        const currentQty = this.getAddedQty(itemId);
        if (currentQty > 1) {
            const newQty = currentQty - 1;
            this.addedItems[itemId] = newQty;
            updateModalItemQty(itemId, newQty, this);
        } else {
            removeModalItem(itemId, this);
        }
    },
    syncItemRemoved(itemId) {
        const updated = { ...this.addedItems };
        delete updated[itemId];
        this.addedItems = updated;
    },
    syncItemUpdated(itemId, qty) {
        this.addedItems = { ...this.addedItems, [itemId]: qty };
    },
    syncAllRemoved() {
        this.addedItems = {};
    },
    get filteredItems() {
        return this.items.filter(item => {
            const matchesSearch = !this.searchQuery || 
                item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                (item.category && item.category.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            const matchesCategory = !this.selectedCategory || 
                (item.category_id == this.selectedCategory);
            return matchesSearch && matchesCategory;
        });
    }
}"
@cart-item-removed.window="syncItemRemoved($event.detail.itemId)"
@cart-item-updated.window="syncItemUpdated($event.detail.itemId, $event.detail.quantity)"
@cart-emptied.window="syncAllRemoved()"
>
    
    <!-- Back Button -->
    <div class="mb-4 md:mb-6">
        <a href="{{ route('vendor.carts.index') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 rounded-lg border border-gray-200 transition-all active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">{{ __('vendor.back') }}</span>
            <span class="sm:hidden">{{ __('vendor.back') }}</span>
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start space-x-3">
            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-900">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Cart Info & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Cart Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-blue-600 rounded-lg">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg md:text-xl font-bold text-gray-900" data-cart-name>{{ $cart->cart_name }}</h2>
                                <p class="text-xs md:text-sm text-gray-600">Created {{ $cart->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="openEditCartModal()"
                                class="px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </button>
                    </div>
                </div>

                <!-- Cart Details -->
                <div class="p-4 md:p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg">
                                <i class="fas fa-user text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('vendor.customer') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $cart->customer->name }}</p>
                                <p class="text-xs text-gray-600">{{ $cart->customer->mobile }}</p>
                            </div>
                        </div>

                        <!-- Booking Dates -->
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg">
                                <i class="fas fa-calendar text-purple-600"></i>
                            </div>
                            <div data-booking-dates>
                                <p class="text-xs text-gray-500 font-medium">Booking Period</p>
                                @if($cart->start_time && $cart->end_time)
                                    <p class="text-sm font-semibold text-gray-900">{{ $cart->start_time->format('M d, Y h:i A') }}</p>
                                    <p class="text-xs text-gray-600">to {{ $cart->end_time->format('M d, Y h:i A') }}</p>
                                @else
                                    <p class="text-sm text-gray-500 italic">Not specified</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.cart_items') }}</h3>
                            <p class="text-sm text-gray-600" data-items-count>{{ $cart->items->count() }} {{ __('vendor.items') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($cart->items->count() > 0)
                            <button type="button" onclick="confirmEmptyCart()"
                                    class="flex items-center flex-shrink-0 px-3 py-2 md:px-4 md:py-2.5 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-all active:scale-95">
                                <i class="fas fa-trash-alt mr-1 md:mr-2"></i>
                                <span class="text-xs md:text-sm">Empty</span>
                            </button>
                            @endif
                            <button @click="showAddItem = true" 
                                    class="flex items-center flex-shrink-0 px-3 py-2 md:px-4 md:py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <i class="fas fa-plus mr-1 md:mr-2"></i>
                                <span class="text-xs md:text-sm">{{ __('vendor.add_item') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="py-4 md:py-6" data-items-list>
                    @if($cart->items->count() > 0)
                        <div class="space-y-0 divide-y divide-gray-200">
                            @foreach($cart->items as $cartItem)
                                <div class="flex items-center justify-between px-4 py-4 md:px-6 bg-white hover:bg-gray-50 transition-colors">
                                    <!-- Item Info -->
                                    <div class="flex-1">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-12 h-12 flex items-center justify-center bg-white rounded-lg border border-gray-200">
                                                <i class="fas fa-box text-gray-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $cartItem->item->name }}</h4>
                                                <p class="text-xs text-gray-600">{{ $cartItem->item->category->name ?? __('vendor.no_category') }}</p>
                                                <div class="flex items-center space-x-4 mt-2">
                                                    <span class="text-sm font-bold text-blue-600">₹{{ number_format($cartItem->item->price, 2) }}</span>
                                                    <span class="text-xs text-gray-500">x</span>
                                                    
                                                    <!-- Quantity Display/Edit -->
                                                    <div x-show="editingItem !== {{ $cartItem->item_id }}" class="flex items-center space-x-2">
                                                        <span class="text-sm font-medium text-gray-900" data-qty-display="{{ $cartItem->item_id }}">{{ __('vendor.quantity') }}: {{ $cartItem->quantity }}</span>
                                                        <button @click="editingItem = {{ $cartItem->item_id }}; editQuantity = {{ $cartItem->quantity }}" 
                                                                class="text-xs text-blue-600 hover:text-blue-700">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Quantity Edit (AJAX) -->
                                                    <div x-show="editingItem === {{ $cartItem->item_id }}" 
                                                         class="flex items-center space-x-2"
                                                         style="display: none;">
                                                        <input type="number" 
                                                               x-model="editQuantity"
                                                               min="1" 
                                                               class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                                               @keydown.enter.prevent="updateCartItemQty({{ $cart->id }}, {{ $cartItem->item_id }}, editQuantity, $el); editingItem = null">
                                                        <button type="button" @click="updateCartItemQty({{ $cart->id }}, {{ $cartItem->item_id }}, editQuantity, $el); editingItem = null" class="text-green-600 hover:text-green-700">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" @click="editingItem = null" class="text-red-600 hover:text-red-700">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <span class="text-xs text-gray-500">=</span>
                                                    <span class="text-sm font-bold text-gray-900" data-line-total="{{ $cartItem->item_id }}">₹{{ number_format($cartItem->item->price * $cartItem->quantity, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" 
                                            onclick="removeCartItem({{ $cart->id }}, {{ $cartItem->item_id }}, this)"
                                            class="ml-4 p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                                <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_yet') }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ __('vendor.add_to_cart') }}</p>
                            <button @click="showAddItem = true" 
                                    class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('vendor.add_item') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="px-4 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.summary') }}</h3>
                </div>

                <!-- Summary Details -->
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('vendor.sub_total') }}</span>
                        <span data-sub-total class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->sub_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('vendor.tax') }} (10%)</span>
                        <span data-tax-total class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->tax_total, 2) }}</span>
                    </div>

                    <!-- Discount Row -->
                    <div class="py-2 border-b border-gray-100">
                        {{-- No discount applied --}}
                        <div id="discount-add" class="{{ $cart->discount_amount > 0 ? 'hidden' : '' }}">
                            <button type="button" onclick="openDiscountModal()"
                                    class="flex items-center space-x-2 text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add Discount</span>
                            </button>
                        </div>
                        {{-- Discount applied --}}
                        <div id="discount-applied" class="{{ $cart->discount_amount > 0 ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-tag text-blue-600 text-xs"></i>
                                    <span class="text-sm text-gray-700 font-medium" id="discount-label">
                                        @if($cart->discount_type === 'percent')
                                            Discount {{ rtrim(rtrim(number_format($cart->discount_value, 2), '0'), '.') }}%
                                        @elseif($cart->discount_type === 'fixed')
                                            Discount ₹{{ number_format($cart->discount_value, 2) }}
                                        @else
                                            Discount
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span data-discount-amount class="text-sm font-semibold text-red-600">-₹{{ number_format($cart->discount_amount, 2) }}</span>
                                    <button type="button" onclick="removeDiscount()"
                                            class="text-xs text-red-500 hover:text-red-700" title="Remove discount">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coupon Row -->
                    <div class="py-2 border-b border-gray-100">
                        {{-- No coupon applied --}}
                        <div id="coupon-add" class="{{ $cart->coupon_code ? 'hidden' : '' }}">
                            <button type="button" onclick="openCouponModal()"
                                    class="flex items-center space-x-2 text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Add Coupon</span>
                            </button>
                        </div>
                        {{-- Coupon applied --}}
                        <div id="coupon-applied" class="{{ $cart->coupon_code ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-ticket-alt text-emerald-600 text-xs"></i>
                                    <span class="text-sm text-emerald-700 font-medium" data-coupon-code>{{ $cart->coupon_code }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span data-coupon-discount class="text-sm font-semibold text-red-600">-₹{{ number_format($cart->coupon_discount, 2) }}</span>
                                    <button type="button" onclick="removeCoupon()"
                                            class="text-xs text-red-500 hover:text-red-700" title="Remove coupon">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Discount -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-700">Total Discount</span>
                        <span data-discount-total class="text-sm font-semibold text-red-600">-₹{{ number_format($cart->discount_total, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-t-2 border-gray-200 mt-2">
                        <span class="text-base font-bold text-gray-900">{{ __('vendor.grand_total') }}</span>
                        <span data-grand-total class="text-lg font-bold text-emerald-600">₹{{ number_format($cart->grand_total, 2) }}</span>
                    </div>
                    <!-- Add Payment Button -->
                    <div class="flex items-center justify-center mt-2 mb-2">
                        <button type="button" onclick="openAddPaymentModal()" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <i class="fas fa-wallet mr-2"></i>Add Payment
                        </button>
                    </div>

                    <!-- Add Payment Modal -->
                    <div id="addPaymentModal" class="fixed inset-0 z-[80] hidden">
                        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeAddPaymentModal()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                                                <i class="fas fa-wallet text-white"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900">Add Payment</h3>
                                            </div>
                                        </div>
                                        <button type="button" onclick="closeAddPaymentModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Body -->
                                <form class="p-6 space-y-5">
                                    <!-- Payment Type -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-3">Payment Type</label>
                                        <div class="grid grid-cols-1 gap-3">
                                            <label class="flex items-center space-x-3 cursor-pointer">
                                                <input type="radio" name="payment_type" value="security_deposit" class="form-radio text-blue-600" checked>
                                                <span class="text-sm font-medium text-gray-700">Security Deposit</span>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer">
                                                <input type="radio" name="payment_type" value="token_amount" class="form-radio text-blue-600">
                                                <span class="text-sm font-medium text-gray-700">Token Amount</span>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer">
                                                <input type="radio" name="payment_type" value="full_payment" class="form-radio text-blue-600">
                                                <span class="text-sm font-medium text-gray-700">Full Payment</span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                        <button type="button" onclick="closeAddPaymentModal()"
                                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                            <i class="fas fa-check mr-2"></i>Add Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                    function openAddPaymentModal() {
                        document.getElementById('addPaymentModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                    function closeAddPaymentModal() {
                        document.getElementById('addPaymentModal').classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                    </script>
                    
                </div>

                <!-- Payment Status & Actions (moved up) -->
                <div class="p-4 border-t border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ __('vendor.token_amount') }}</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->token_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ __('vendor.paid_amount') }}</span>
                        <span data-paid-amount class="text-sm font-semibold text-emerald-600">₹{{ number_format($cart->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-gray-200">
                        <span class="text-sm font-bold text-gray-900">{{ __('vendor.balance_due') }}</span>
                        <span data-balance-due class="text-sm font-bold text-red-600">₹{{ number_format($cart->grand_total - $cart->paid_amount, 2) }}</span>
                    </div>
                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-2">
                        @if($cart->items->count() > 0)
                            <form action="{{ route('vendor.carts.place-order', $cart->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('{{ __('vendor.confirm_place_order') }}')">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    {{ __('vendor.place_order') }}
                                </button>
                            </form>
                        @else
                            <button disabled 
                                    class="w-full px-4 py-3 text-sm font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <i class="fas fa-check-circle mr-2"></i>
                                {{ __('vendor.place_order') }}
                            </button>
                        @endif
                        <button class="w-full px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            {{ __('vendor.print') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal - Full Screen -->
    <div x-show="showAddItem" 
         x-cloak
         class="fixed inset-0 z-50">
        <!-- Background Overlay -->
        <div @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

        <!-- Modal Content - Full Screen -->
        <div x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="relative bg-white h-full flex flex-col z-10 md:m-4 md:rounded-xl md:shadow-2xl md:h-auto md:max-h-[95vh]">
            
            <!-- Modal Header -->
            <div class="flex-shrink-0 px-4 md:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-900">Add Items to Cart</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Select items and set quantities</p>
                    </div>
                    <button @click="showAddItem = false; searchQuery = ''; selectedCategory = ''" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 flex flex-col overflow-hidden">

                <!-- Search and Filter Section -->
                <div class="flex-shrink-0 px-4 md:px-6 py-4 bg-white border-b border-gray-200 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Search Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="{{ __('vendor.search_placeholder') }}"
                                   class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button type="button" 
                                    x-show="searchQuery"
                                    @click="searchQuery = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Category Filter -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-filter text-gray-400"></i>
                            </div>
                            <select x-model="selectedCategory"
                                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                <option value="">{{ __('vendor.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Results Info -->
                    <div class="flex items-center justify-between text-sm">
                        <p class="text-gray-600">
                            <span x-text="filteredItems.length"></span> {{ __('vendor.items') }}
                        </p>
                    </div>
                </div>

                <!-- Items Table Container -->
                <div class="flex-1 overflow-y-auto">
                    <!-- No Items State -->
                    <div x-show="items.length === 0" class="flex items-center justify-center h-full p-8">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box-open text-3xl text-yellow-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Available Items</h3>
                            <p class="text-sm text-gray-600 mb-4">You don't have any active items yet</p>
                            <a href="{{ route('vendor.items.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Your First Item
                            </a>
                        </div>
                    </div>

                    <!-- No Search Results State -->
                    <div x-show="items.length > 0 && filteredItems.length === 0" class="flex items-center justify-center h-full p-8">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_found') }}</h3>
                            <p class="text-sm text-gray-600 mb-3">{{ __('vendor.adjust_search') }}</p>
                            <button type="button" 
                                    @click="searchQuery = ''; selectedCategory = ''"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                <i class="fas fa-redo mr-1"></i>
                                {{ __('vendor.clear_filters') }}
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <table class="hidden md:table w-full" x-show="filteredItems.length > 0">
                        <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{{ __('vendor.item') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{{ __('vendor.price') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-44" colspan="2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="item in filteredItems" :key="item.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Item Info -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-box text-blue-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="item.name"></p>
                                                <p class="text-xs text-gray-500 truncate" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                <p class="text-xs text-gray-500" x-show="item.manage_stock">
                                                    {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Price -->
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-bold text-gray-900">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                    </td>
                                    
                                    <!-- Add / Quantity -->
                                    <td class="px-4 py-3 text-center" colspan="2">
                                        <!-- Add Button (shown when not added) -->
                                        <button type="button" @click="addItemToCart(item.id)"
                                                x-show="!isAdded(item.id)"
                                                :disabled="addingItem === item.id"
                                                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span x-show="addingItem !== item.id">
                                                <i class="fas fa-plus mr-1"></i>{{ __('vendor.add') }}
                                            </span>
                                            <span x-show="addingItem === item.id">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                        <!-- Quantity Stepper (shown after adding) -->
                                        <div x-show="isAdded(item.id)" class="flex items-center justify-center space-x-1">
                                            <button type="button" @click="decrementCartQty(item.id)"
                                                    :disabled="updatingItem === item.id"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400 transition-colors active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <input type="number"
                                                class="w-14 h-10 text-center text-sm font-semibold text-gray-900 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                min="1"
                                                :value="getAddedQty(item.id)"
                                                :disabled="updatingItem === item.id"
                                                @input="
                                                    let val = parseInt($event.target.value) || 1;
                                                    if (val < 1) val = 1;
                                                    addedItems[item.id] = val;
                                                "
                                                @blur="
                                                    let val = parseInt($event.target.value) || 1;
                                                    if (val < 1) val = 1;
                                                    updatingItem = item.id;
                                                    updateModalItemQty(item.id, val, $root);
                                                "
                                                @keydown.enter.prevent="
                                                    let val = parseInt($event.target.value) || 1;
                                                    if (val < 1) val = 1;
                                                    updatingItem = item.id;
                                                    updateModalItemQty(item.id, val, $root);
                                                    $event.target.blur();
                                                "
                                            >
                                            <button type="button" @click="incrementCartQty(item.id)"
                                                    :disabled="updatingItem === item.id"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400 transition-colors active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" x-show="filteredItems.length > 0">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <!-- Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-box text-blue-600"></i>
                                            </div>
                                            <div class="ml-3 flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-1" x-text="item.name"></h4>
                                                <p class="text-xs text-gray-500 mb-1" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                <p class="text-sm font-bold text-blue-600">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                                <p class="text-xs text-gray-500 mt-1" x-show="item.manage_stock">
                                                    {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Add / Quantity Stepper -->
                                        <div class="mt-3 flex items-center justify-end">
                                            <!-- Add Button (shown when not added) -->
                                            <button type="button" @click="addItemToCart(item.id)"
                                                    x-show="!isAdded(item.id)"
                                                    :disabled="addingItem === item.id"
                                                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="addingItem !== item.id">
                                                    <i class="fas fa-plus mr-1"></i>{{ __('vendor.add') }}
                                                </span>
                                                <span x-show="addingItem === item.id">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                            <!-- Quantity Stepper (shown after adding) -->
                                            <div x-show="isAdded(item.id)" class="flex items-center space-x-2">
                                                <button type="button" @click="decrementCartQty(item.id)"
                                                        :disabled="updatingItem === item.id"
                                                        class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number"
                                                    class="w-14 h-10 text-center text-sm font-semibold text-gray-900 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    min="1"
                                                    :value="getAddedQty(item.id)"
                                                    :disabled="updatingItem === item.id"
                                                    @input="
                                                        let val = parseInt($event.target.value) || 1;
                                                        if (val < 1) val = 1;
                                                        addedItems[item.id] = val;
                                                    "
                                                    @blur="
                                                        let val = parseInt($event.target.value) || 1;
                                                        if (val < 1) val = 1;
                                                        updatingItem = item.id;
                                                        updateModalItemQty(item.id, val, $root);
                                                    "
                                                    @keydown.enter.prevent="
                                                        let val = parseInt($event.target.value) || 1;
                                                        if (val < 1) val = 1;
                                                        updatingItem = item.id;
                                                        updateModalItemQty(item.id, val, $root);
                                                        $event.target.blur();
                                                    "
                                                >
                                                <button type="button" @click="incrementCartQty(item.id)"
                                                        :disabled="updatingItem === item.id"
                                                        class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="flex-shrink-0 px-4 md:px-6 py-3 bg-red-50 border-t border-red-100">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif

                <!-- Modal Footer -->
                <div class="flex-shrink-0 flex items-center justify-end px-4 md:px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <button type="button" 
                            @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
                            class="px-6 py-3 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div id="discountModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeDiscountModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                            <i class="fas fa-percent text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.add') }} {{ __('vendor.discount') }}</h3>
                            <p class="text-xs text-gray-600">Apply discount to this cart</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDiscountModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form id="discountForm" onsubmit="submitDiscount(event)" class="p-6 space-y-5">
                <!-- Discount Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Discount Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="discount_type" value="fixed" checked class="peer sr-only" id="discount_type_fixed">
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-rupee-sign text-lg text-gray-500 peer-checked:text-blue-600 mb-1"></i>
                                    <p class="text-sm font-semibold text-gray-700">Fixed Amount</p>
                                    <p class="text-xs text-gray-500">₹ value</p>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="discount_type" value="percent" class="peer sr-only" id="discount_type_percent">
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-percent text-lg text-gray-500 peer-checked:text-blue-600 mb-1"></i>
                                    <p class="text-sm font-semibold text-gray-700">Percentage</p>
                                    <p class="text-xs text-gray-500">% of subtotal</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Discount Value -->
                <div>
                    <label for="discount_value" class="block text-sm font-semibold text-gray-700 mb-2">
                        Discount Value <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span id="discountSymbol" class="text-gray-500 font-medium">₹</span>
                        </div>
                        <input type="number" name="discount_value" id="discount_value"
                               step="0.01" min="0" required
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter discount value">
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Subtotal: <span class="font-semibold">₹{{ number_format($cart->sub_total, 2) }}</span>
                    </p>
                    <p id="discountError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeDiscountModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" id="discountSubmitBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-check mr-2"></i>Apply Discount
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div id="couponModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeCouponModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all max-h-[85vh] flex flex-col" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-lg">
                            <i class="fas fa-ticket-alt text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Apply Coupon</h3>
                            <p class="text-xs text-gray-600">Enter code or select from list</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCouponModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Coupon Code Input -->
            <div class="flex-shrink-0 px-6 pt-5 pb-3">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <input type="text" id="coupon_code_input"
                               placeholder="Enter coupon code"
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent uppercase"
                               maxlength="50">
                    </div>
                    <button type="button" onclick="applyCouponFromModal()" id="applyCouponBtn"
                            class="px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 whitespace-nowrap">
                        Apply
                    </button>
                </div>
                <p id="couponError" class="mt-1.5 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Divider -->
            <div class="flex-shrink-0 px-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-white text-gray-500">or select a coupon</span>
                    </div>
                </div>
            </div>

            <!-- Coupon List -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3" id="couponList">
                <div class="flex items-center justify-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Cart Modal -->
<div id="editCartModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeEditCartModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full transform transition-all" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.edit') }} {{ __('vendor.cart') }}</h3>
                            <p class="text-xs text-gray-600">{{ __('vendor.update_cart_info') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditCartModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="editCartForm" onsubmit="submitEditCart(event)" class="p-6 space-y-5">
                <!-- Customer (Read-only) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('vendor.customer') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" 
                               value="{{ $cart->customer->name }} - {{ $cart->customer->mobile }}"
                               class="w-full pl-11 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                               disabled readonly>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500"><i class="fas fa-info-circle mr-1"></i>{{ __('vendor.customer_cannot_change') }}</p>
                </div>

                <!-- Cart Name -->
                <div>
                    <label for="edit_cart_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('vendor.name') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <input type="text" name="cart_name" id="edit_cart_name"
                               value="{{ $cart->cart_name }}"
                               class="w-full pl-11 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="{{ __('vendor.cart_name_placeholder') }}" required>
                    </div>
                    <p id="editCartNameError" class="mt-1.5 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Booking Dates -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar text-emerald-600 mr-1"></i>
                        {{ __('vendor.booking_dates') }} <span class="text-gray-500 text-xs">({{ __('vendor.optional') }})</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_start_time" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.start_date_time') }}
                            </label>
                            <div class="date-input-wrapper">
                                <input type="text" name="start_time" id="edit_start_time"
                                       value="{{ $cart->start_time ? $cart->start_time->format('Y-m-d H:i') : '' }}"
                                       readonly
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                       placeholder="Select start date">
                                <span class="date-clear-btn" onclick="clearEditDate('start')" title="Clear">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <p id="editStartTimeError" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                        <div>
                            <label for="edit_end_time" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.end_date_time') }}
                            </label>
                            <div class="date-input-wrapper">
                                <input type="text" name="end_time" id="edit_end_time"
                                       value="{{ $cart->end_time ? $cart->end_time->format('Y-m-d H:i') : '' }}"
                                       readonly
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                       placeholder="Select end date">
                                <span class="date-clear-btn" onclick="clearEditDate('end')" title="Clear">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <p id="editEndTimeError" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeEditCartModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" id="editCartSubmitBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-save mr-2"></i>{{ __('vendor.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" id="deleteModalOverlay"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" id="deleteModalPanel">
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-red-100 rounded-full">
                    <i class="fas fa-trash-alt text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('vendor.confirm_delete') }}</h3>
                <p class="text-sm text-gray-600 mb-1">Are you sure you want to remove this item from the cart?</p>
                <p class="text-xs text-gray-500">This action cannot be undone.</p>
            </div>
            <div class="flex items-center justify-center gap-3 px-6 pb-6">
                <button type="button" id="deleteModalCancel"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>{{ __('vendor.cancel') }}
                </button>
                <button type="button" id="deleteModalConfirm"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    <i class="fas fa-trash mr-2"></i>{{ __('vendor.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Empty Cart Confirm Modal -->
<div id="emptyCartModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target===this)closeEmptyCartModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-red-100 rounded-full">
                    <i class="fas fa-trash-alt text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Empty Cart?</h3>
                <p class="text-sm text-gray-600">This will remove all items and reset discounts. This action cannot be undone.</p>
            </div>
            <div class="flex border-t border-gray-200">
                <button type="button" onclick="closeEmptyCartModal()"
                        class="flex-1 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors border-r border-gray-200">
                    Cancel
                </button>
                <button type="button" id="emptyCartConfirmBtn" onclick="emptyCart()"
                        class="flex-1 px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fas fa-trash-alt mr-1"></i>Empty Cart
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    [x-cloak] { display: none !important; }
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .flatpickr-time input { font-size: 1rem !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; }
    .date-clear-btn { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 12px; padding: 2px 4px; display: none; }
    .date-clear-btn:hover { color: #ef4444; }
    .date-input-wrapper input:not([value=""]) ~ .date-clear-btn,
    .date-input-wrapper input.has-value ~ .date-clear-btn { display: block; }
</style>
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
let pendingDelete = null;

// --- Add Item to Cart (AJAX) ---
function addItemToCartAjax(itemId, qty, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route("vendor.carts.items.add", $cart->id) }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            items: [{ item_id: itemId, quantity: qty }]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            component.addedItems = { ...component.addedItems, [itemId]: qty };
            updateSummary(data.cart);
            refreshCartItems();
            showToast(data.message || 'Item added to cart', 'success');
        } else {
            showToast(data.message || 'Error adding item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error adding item', 'error');
    })
    .finally(() => {
        component.addingItem = null;
    });
}

// --- Update item qty from modal ---
function updateModalItemQty(itemId, newQty, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ url('vendor/carts') }}/{{ $cart->id }}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: newQty })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSummary(data.cart);
            refreshCartItems();
        } else {
            showToast(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating quantity', 'error');
    })
    .finally(() => {
        component.updatingItem = null;
    });
}

// --- Remove item from modal (when qty goes to 0) ---
function removeModalItem(itemId, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ url('vendor/carts') }}/{{ $cart->id }}/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const updated = { ...component.addedItems };
            delete updated[itemId];
            component.addedItems = updated;
            updateSummary(data.cart);
            refreshCartItems();
            showToast(data.message || 'Item removed', 'success');
        } else {
            component.addedItems = { ...component.addedItems, [itemId]: 1 };
            showToast(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        component.addedItems = { ...component.addedItems, [itemId]: 1 };
        showToast('Error removing item', 'error');
    })
    .finally(() => {
        component.updatingItem = null;
    });
}

// --- Refresh cart items list via page reload ---
function refreshCartItems() {
    fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newList = doc.querySelector('[data-items-list]');
        const currentList = document.querySelector('[data-items-list]');
        if (newList && currentList) currentList.innerHTML = newList.innerHTML;
        const newCount = doc.querySelector('[data-items-count]');
        const currentCount = document.querySelector('[data-items-count]');
        if (newCount && currentCount) currentCount.textContent = newCount.textContent;
    })
    .catch(() => {});
}

// --- Flatpickr for Edit Cart Modal ---
let editStartPicker, editEndPicker;

function initEditDatePickers() {
    function toggleClearBtn(instance) {
        const wrapper = instance.element.closest('.date-input-wrapper');
        if (!wrapper) return;
        const clearBtn = wrapper.querySelector('.date-clear-btn');
        if (clearBtn) {
            clearBtn.style.display = instance.selectedDates.length > 0 ? 'block' : 'none';
        }
    }

    const fpConfig = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'M j, Y h:i K',
        time_24hr: false,
        allowInput: false,
        disableMobile: false,
        monthSelectorType: 'dropdown',
        animate: true,
        onReady: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        },
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        }
    };

    if (editStartPicker) editStartPicker.destroy();
    if (editEndPicker) editEndPicker.destroy();

    editStartPicker = flatpickr('#edit_start_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                editEndPicker.set('minDate', selectedDates[0]);
            } else {
                editEndPicker.set('minDate', null);
            }
        }
    });

    editEndPicker = flatpickr('#edit_end_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                editStartPicker.set('maxDate', selectedDates[0]);
            } else {
                editStartPicker.set('maxDate', null);
            }
        }
    });

    // Set initial constraints
    if (editStartPicker.selectedDates.length > 0) {
        editEndPicker.set('minDate', editStartPicker.selectedDates[0]);
    }
    if (editEndPicker.selectedDates.length > 0) {
        editStartPicker.set('maxDate', editEndPicker.selectedDates[0]);
    }
}

window.clearEditDate = function(which) {
    if (which === 'start') {
        editStartPicker.clear();
        editEndPicker.set('minDate', null);
    } else {
        editEndPicker.clear();
        editStartPicker.set('maxDate', null);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initEditDatePickers();
});

// --- Discount Modal ---
function openDiscountModal() {
    document.getElementById('discountModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('discountError').classList.add('hidden');
    document.getElementById('discount_value').value = '';
    document.getElementById('discount_type_fixed').checked = true;
    updateDiscountSymbol();
}

function closeDiscountModal() {
    document.getElementById('discountModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function updateDiscountSymbol() {
    const type = document.querySelector('input[name="discount_type"]:checked').value;
    document.getElementById('discountSymbol').textContent = type === 'percent' ? '%' : '₹';
}

// Listen for discount type changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="discount_type"]').forEach(radio => {
        radio.addEventListener('change', updateDiscountSymbol);
    });
});

function submitDiscount(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('discountSubmitBtn');
    const errorEl = document.getElementById('discountError');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    errorEl.classList.add('hidden');

    const discountType = document.querySelector('input[name="discount_type"]:checked').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value);

    if (!discountValue || discountValue <= 0) {
        errorEl.textContent = 'Please enter a valid discount value';
        errorEl.classList.remove('hidden');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Applying...';

    fetch(`{{ route('vendor.carts.discount', $cart->id) }}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            discount_type: discountType,
            discount_value: discountValue
        })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (status === 422) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                errorEl.textContent = firstError;
            } else {
                errorEl.textContent = data.message || 'Validation error';
            }
            errorEl.classList.remove('hidden');
            return;
        }

        if (data.success) {
            // Update discount label
            const label = discountType === 'percent'
                ? 'Discount ' + parseFloat(discountValue) + '%'
                : 'Discount ₹' + parseFloat(discountValue).toFixed(2);
            document.getElementById('discount-label').textContent = label;

            // Show applied, hide add
            document.getElementById('discount-applied').classList.remove('hidden');
            document.getElementById('discount-add').classList.add('hidden');

            updateSummary(data.cart);
            closeDiscountModal();
            showToast(data.message, 'success');
        } else {
            errorEl.textContent = data.message || 'Error applying discount';
            errorEl.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorEl.textContent = 'Error applying discount';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Apply Discount';
    });
}

// --- Remove Discount ---
function removeDiscount() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ route('vendor.carts.discount.remove', $cart->id) }}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show add, hide applied
            document.getElementById('discount-add').classList.remove('hidden');
            document.getElementById('discount-applied').classList.add('hidden');

            updateSummary(data.cart);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing discount', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing discount', 'error');
    });
}

// --- Coupon Modal ---
function openCouponModal() {
    document.getElementById('couponModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('couponError').classList.add('hidden');
    document.getElementById('coupon_code_input').value = '';
    loadCouponList();
}

function closeCouponModal() {
    document.getElementById('couponModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function loadCouponList() {
    const listEl = document.getElementById('couponList');
    listEl.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i></div>';

    fetch(`{{ route('vendor.carts.coupons.list', $cart->id) }}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.coupons.length) {
            listEl.innerHTML = '<div class="text-center py-8 text-sm text-gray-500"><i class="fas fa-ticket-alt text-2xl mb-2 block text-gray-300"></i>No coupons available</div>';
            return;
        }

        listEl.innerHTML = data.coupons.map(coupon => {
            const disabled = !coupon.is_applicable;
            const typeLabel = coupon.type === 'percent'
                ? parseFloat(coupon.value) + '% OFF'
                : '₹' + parseFloat(coupon.value).toFixed(0) + ' OFF';
            const minOrder = coupon.min_order_amount > 0 ? 'Min order ₹' + parseFloat(coupon.min_order_amount).toFixed(0) : '';
            const maxDiscount = coupon.max_discount_amount > 0 ? 'Max ₹' + parseFloat(coupon.max_discount_amount).toFixed(0) : '';
            const expiry = coupon.end_date ? 'Expires ' + coupon.end_date : '';
            const details = [minOrder, maxDiscount, expiry].filter(Boolean).join(' · ');

            return `
                <div class="border ${disabled ? 'border-gray-200 opacity-60' : 'border-emerald-200 hover:border-emerald-400 cursor-pointer'} rounded-lg p-3 transition-all ${disabled ? '' : 'hover:shadow-sm'}"
                     ${disabled ? '' : `onclick="selectCoupon('${coupon.code}')"`}>
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold ${disabled ? 'bg-gray-100 text-gray-500' : 'bg-emerald-100 text-emerald-800'} border ${disabled ? 'border-gray-200' : 'border-emerald-200'} tracking-wider">${coupon.code}</span>
                                <span class="text-xs font-semibold ${disabled ? 'text-gray-400' : 'text-blue-600'}">${typeLabel}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1 truncate">${coupon.name}</p>
                            ${details ? `<p class="text-xs text-gray-400 mt-0.5">${details}</p>` : ''}
                        </div>
                        ${disabled
                            ? '<span class="text-xs text-gray-400 flex-shrink-0 ml-2">Not applicable</span>'
                            : '<button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex-shrink-0 ml-2">Apply</button>'}
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(error => {
        console.error('Error:', error);
        listEl.innerHTML = '<div class="text-center py-8 text-sm text-red-500">Error loading coupons</div>';
    });
}

function selectCoupon(code) {
    document.getElementById('coupon_code_input').value = code;
    applyCouponFromModal();
}

function applyCouponFromModal() {
    const codeInput = document.getElementById('coupon_code_input');
    const code = codeInput.value.trim();
    const errorEl = document.getElementById('couponError');
    const btn = document.getElementById('applyCouponBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    errorEl.classList.add('hidden');

    if (!code) {
        errorEl.textContent = 'Please enter a coupon code';
        errorEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ route('vendor.carts.coupon.apply', $cart->id) }}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (!data.success) {
            errorEl.textContent = data.message || 'Invalid coupon';
            errorEl.classList.remove('hidden');
            return;
        }

        // Show applied coupon in summary
        document.querySelector('[data-coupon-code]').textContent = data.coupon.code;
        document.getElementById('coupon-applied').classList.remove('hidden');
        document.getElementById('coupon-add').classList.add('hidden');

        updateSummary(data.cart);
        closeCouponModal();
        showToast(data.message, 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        errorEl.textContent = 'Error applying coupon';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Apply';
    });
}

function removeCoupon() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ route('vendor.carts.coupon.remove', $cart->id) }}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show add, hide applied
            document.getElementById('coupon-add').classList.remove('hidden');
            document.getElementById('coupon-applied').classList.add('hidden');

            updateSummary(data.cart);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing coupon', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing coupon', 'error');
    });
}

// --- Edit Cart Modal ---
function openEditCartModal() {
    document.getElementById('editCartModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Clear previous errors
    document.getElementById('editCartNameError').classList.add('hidden');
    document.getElementById('editStartTimeError').classList.add('hidden');
    document.getElementById('editEndTimeError').classList.add('hidden');
}

function closeEditCartModal() {
    document.getElementById('editCartModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function submitEditCart(e) {
    e.preventDefault();

    const form = document.getElementById('editCartForm');
    const submitBtn = document.getElementById('editCartSubmitBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Clear previous errors
    document.getElementById('editCartNameError').classList.add('hidden');
    document.getElementById('editStartTimeError').classList.add('hidden');
    document.getElementById('editEndTimeError').classList.add('hidden');

    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("vendor.updating") }}...';

    const formData = {
        cart_name: document.getElementById('edit_cart_name').value,
        start_time: document.getElementById('edit_start_time').value || null,
        end_time: document.getElementById('edit_end_time').value || null,
        _method: 'PUT'
    };

    fetch(`{{ route('vendor.carts.update', $cart->id) }}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (status === 422 && data.errors) {
            // Show validation errors
            if (data.errors.cart_name) {
                const el = document.getElementById('editCartNameError');
                el.textContent = data.errors.cart_name[0];
                el.classList.remove('hidden');
            }
            if (data.errors.start_time) {
                const el = document.getElementById('editStartTimeError');
                el.textContent = data.errors.start_time[0];
                el.classList.remove('hidden');
            }
            if (data.errors.end_time) {
                const el = document.getElementById('editEndTimeError');
                el.textContent = data.errors.end_time[0];
                el.classList.remove('hidden');
            }
            return;
        }

        if (data.success) {
            // Update cart name in header
            const cartNameEl = document.querySelector('[data-cart-name]');
            if (cartNameEl) cartNameEl.textContent = data.cart.cart_name;

            // Update booking dates
            const bookingEl = document.querySelector('[data-booking-dates]');
            if (bookingEl) {
                if (data.cart.start_time && data.cart.end_time) {
                    bookingEl.innerHTML = `
                        <p class="text-sm font-semibold text-gray-900">${data.cart.start_time}</p>
                        <p class="text-xs text-gray-600">to ${data.cart.end_time}</p>
                    `;
                } else {
                    bookingEl.innerHTML = '<p class="text-sm text-gray-500 italic">Not specified</p>';
                }
            }

            closeEditCartModal();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating cart', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>{{ __("vendor.update") }}';
    });
}

function updateCartItemQty(cartId, itemId, quantity, el) {
    quantity = parseInt(quantity);
    if (!quantity || quantity < 1) {
        showToast('Quantity must be at least 1', 'error');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ url('vendor/carts') }}/${cartId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sync Alpine state via event
            window.dispatchEvent(new CustomEvent('cart-item-updated', { detail: { itemId: itemId, quantity: data.item.quantity } }));

            // Update quantity display
            const qtyDisplay = document.querySelector(`[data-qty-display="${itemId}"]`);
            if (qtyDisplay) qtyDisplay.textContent = '{{ __("vendor.quantity") }}: ' + data.item.quantity;

            // Update line total
            const lineTotalEl = document.querySelector(`[data-line-total="${itemId}"]`);
            if (lineTotalEl) lineTotalEl.textContent = '₹' + parseFloat(data.item.line_total).toFixed(2);

            // Update summary totals
            updateSummary(data.cart);

            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating quantity', 'error');
    });
}

function removeCartItem(cartId, itemId, button) {
    // Show confirmation modal
    pendingDelete = { cartId, itemId, button };
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    pendingDelete = null;
}

function confirmDelete() {
    if (!pendingDelete) return;

    const { cartId, itemId, button } = pendingDelete;
    closeDeleteModal();

    const row = button.closest('.flex.items-center.justify-between');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Disable button while processing
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ url('vendor/carts') }}/${cartId}/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sync Alpine state via event
            window.dispatchEvent(new CustomEvent('cart-item-removed', { detail: { itemId: itemId } }));

            // Animate and remove the row
            row.style.transition = 'opacity 0.3s, max-height 0.3s';
            row.style.opacity = '0';
            row.style.overflow = 'hidden';
            setTimeout(() => {
                row.remove();

                // Update summary totals
                updateSummary(data.cart);

                // Update items count in header
                const itemsCountEl = document.querySelector('[data-items-count]');
                if (itemsCountEl) {
                    itemsCountEl.textContent = data.cart.items_count + ' {{ __("vendor.items") }}';
                }

                // Show empty state if no items left
                if (data.cart.items_count === 0) {
                    const itemsList = document.querySelector('[data-items-list]');
                    if (itemsList) {
                        itemsList.innerHTML = `
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                                    <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_yet') }}</h3>
                                <p class="text-sm text-gray-600 mb-4">{{ __('vendor.add_to_cart') }}</p>
                            </div>
                        `;
                    }
                }
            }, 300);

            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing item', 'error');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing item', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash"></i>';
    });
}

// Modal event listeners
document.getElementById('deleteModalCancel').addEventListener('click', closeDeleteModal);
document.getElementById('deleteModalOverlay').addEventListener('click', closeDeleteModal);
document.getElementById('deleteModalConfirm').addEventListener('click', confirmDelete);

function updateSummary(cart) {
    const subTotalEl = document.querySelector('[data-sub-total]');
    const taxTotalEl = document.querySelector('[data-tax-total]');
    const discountAmountEl = document.querySelector('[data-discount-amount]');
    const couponDiscountEl = document.querySelector('[data-coupon-discount]');
    const discountTotalEl = document.querySelector('[data-discount-total]');
    const grandTotalEl = document.querySelector('[data-grand-total]');
    const balanceDueEl = document.querySelector('[data-balance-due]');

    if (subTotalEl) subTotalEl.textContent = '₹' + parseFloat(cart.sub_total).toFixed(2);
    if (taxTotalEl) taxTotalEl.textContent = '₹' + parseFloat(cart.tax_total).toFixed(2);
    if (discountAmountEl) discountAmountEl.textContent = '-₹' + parseFloat(cart.discount_amount).toFixed(2);
    if (couponDiscountEl) couponDiscountEl.textContent = '-₹' + parseFloat(cart.coupon_discount).toFixed(2);
    if (discountTotalEl) discountTotalEl.textContent = '-₹' + parseFloat(cart.discount_total).toFixed(2);
    if (grandTotalEl) grandTotalEl.textContent = '₹' + parseFloat(cart.grand_total).toFixed(2);
    if (balanceDueEl) {
        const paidAmount = parseFloat(document.querySelector('[data-paid-amount]')?.textContent?.replace(/[₹,]/g, '') || 0);
        balanceDueEl.textContent = '₹' + (parseFloat(cart.grand_total) - paidAmount).toFixed(2);
    }
}

function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-[60]`;
    toast.innerHTML = `
        <i class="fas ${icon} text-2xl"></i>
        <div><p class="font-medium">${message}</p></div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-100">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

// --- Empty Cart ---
function confirmEmptyCart() {
    document.getElementById('emptyCartModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEmptyCartModal() {
    document.getElementById('emptyCartModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function emptyCart() {
    const btn = document.getElementById('emptyCartConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Emptying...';

    fetch('{{ route("vendor.carts.empty", $cart->id) }}', {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Sync Alpine state via event
            window.dispatchEvent(new CustomEvent('cart-emptied'));
            closeEmptyCartModal();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Error emptying cart', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error emptying cart', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt mr-1"></i>Empty Cart';
    });
}
</script>
@endsection
