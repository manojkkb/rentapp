@if(!$orders->isEmpty())
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/90 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-4">{{ __('vendor.order_details') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.customer') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.orders_col_delivery_return') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.orders_col_payment') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.status') }}</th>
                        <th class="px-5 py-4"><span class="sr-only">{{ __('vendor.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orders as $order)
                        @php
                            $pill = $statusMeta[$order->status]['pill'] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200/70';
                            $customer = $order->customer;
                            $customerName = $customer?->name ?? __('vendor.order_customer_unavailable');
                            $customerMobile = $customer?->mobile;
                            $totalDue = (float) $order->grand_total + (float) ($order->security_deposit ?? 0);
                            $paidAmount = (float) $order->paid_amount;
                            $balanceDue = $totalDue - $paidAmount;
                        @endphp
                        <tr class="hover:bg-emerald-50/35" wire:key="order-{{ $order->uuid }}">
                            <td class="px-5 py-4 align-top">
                                <a href="{{ route('vendor.orders.show', $order) }}"
                                   wire:navigate
                                   class="font-mono text-sm font-semibold text-emerald-700 transition hover:text-emerald-900 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                @if($order->event_name)<p class="mt-0.5 truncate text-xs text-gray-600">{{ $order->event_name }}</p>@endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($customer)
                                    <a href="{{ route('vendor.customers.show', $customer) }}"
                                       wire:navigate
                                       class="block font-medium text-emerald-700 transition hover:text-emerald-900 hover:underline">
                                        {{ $customerName }}
                                    </a>
                                    @if($customerMobile)
                                        <a href="{{ route('vendor.customers.show', $customer) }}"
                                           wire:navigate
                                           class="mt-0.5 block text-xs text-gray-600 transition hover:text-emerald-700 hover:underline">
                                            {{ $customerMobile }}
                                        </a>
                                    @endif
                                @else
                                    <p class="font-medium text-gray-900">{{ $customerName }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="space-y-2.5 text-xs">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.delivered_status') }}</p>
                                        @if($order->delivered_at)
                                            <p class="mt-0.5 font-medium tabular-nums text-gray-900">{{ $order->delivered_at->format('M j, Y g:i A') }}</p>
                                            <span class="mt-1 inline-flex rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-semibold text-teal-800 ring-1 ring-teal-200/70">{{ __('vendor.delivered_status') }}</span>
                                        @else
                                            <p class="mt-0.5 italic text-gray-500">{{ __('vendor.not_delivered_yet') }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.returned_status') }}</p>
                                        @if($order->returned_at)
                                            <p class="mt-0.5 font-medium tabular-nums text-gray-900">{{ $order->returned_at->format('M j, Y g:i A') }}</p>
                                            <span class="mt-1 inline-flex rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-semibold text-teal-800 ring-1 ring-teal-200/70">{{ __('vendor.returned_status') }}</span>
                                        @else
                                            <p class="mt-0.5 italic text-gray-500">{{ __('vendor.not_returned_yet') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="min-w-[8.5rem] space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-gray-600">{{ __('vendor.paid') }}</span>
                                        <span class="shrink-0 whitespace-nowrap font-semibold tabular-nums text-emerald-700">₹{{ number_format($paidAmount, 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-medium text-gray-800">{{ __('vendor.balance_due') }}</span>
                                        <span class="shrink-0 whitespace-nowrap font-bold tabular-nums {{ $balanceDue > 0 ? 'text-red-600' : 'text-gray-600' }}">₹{{ number_format($balanceDue, 2) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $pill }}">{{ ucfirst($order->status) }}</span></td>
                            <td class="px-5 py-4 align-top">
                                <a href="{{ route('vendor.orders.show', $order) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                    {{ __('vendor.view_order') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4 md:hidden">
        @foreach($orders as $order)
            @php
                $pill = $statusMeta[$order->status]['pill'] ?? 'bg-gray-100 text-gray-800';
                $customer = $order->customer;
                $customerName = $customer?->name ?? __('vendor.order_customer_unavailable');
                $customerMobile = $customer?->mobile;
                $totalDue = (float) $order->grand_total + (float) ($order->security_deposit ?? 0);
                $paidAmount = (float) $order->paid_amount;
                $balanceDue = $totalDue - $paidAmount;
            @endphp
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" wire:key="order-m-{{ $order->uuid }}">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3">
                    <a href="{{ route('vendor.orders.show', $order) }}"
                       wire:navigate
                       class="font-mono text-sm font-bold text-emerald-700 transition hover:text-emerald-900 hover:underline">
                        {{ $order->order_number }}
                    </a>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $pill }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="space-y-3 p-4">
                    @if($customer)
                        <a href="{{ route('vendor.customers.show', $customer) }}"
                           wire:navigate
                           class="block text-sm font-semibold text-emerald-700 transition hover:text-emerald-900 hover:underline">
                            {{ $customerName }}
                        </a>
                        @if($customerMobile)
                            <a href="{{ route('vendor.customers.show', $customer) }}"
                               wire:navigate
                               class="mt-0.5 block text-xs text-gray-600 transition hover:text-emerald-700 hover:underline">
                                {{ $customerMobile }}
                            </a>
                        @endif
                    @else
                        <p class="text-sm font-semibold text-gray-900">{{ $customerName }}</p>
                    @endif

                    <div class="grid grid-cols-2 gap-2 rounded-xl border border-gray-100 bg-gray-50/80 p-3 text-xs">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.delivered_status') }}</p>
                            @if($order->delivered_at)
                                <p class="mt-0.5 font-medium tabular-nums text-gray-900">{{ $order->delivered_at->format('M j, Y') }}</p>
                                <span class="mt-1 inline-flex rounded-full bg-teal-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-800">{{ __('vendor.delivered_status') }}</span>
                            @else
                                <p class="mt-0.5 italic text-gray-500">{{ __('vendor.not_delivered_yet') }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.returned_status') }}</p>
                            @if($order->returned_at)
                                <p class="mt-0.5 font-medium tabular-nums text-gray-900">{{ $order->returned_at->format('M j, Y') }}</p>
                                <span class="mt-1 inline-flex rounded-full bg-teal-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-800">{{ __('vendor.returned_status') }}</span>
                            @else
                                <p class="mt-0.5 italic text-gray-500">{{ __('vendor.not_returned_yet') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-white px-3 py-2.5 text-sm">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.paid') }}</p>
                            <p class="mt-0.5 font-semibold tabular-nums text-emerald-700">₹{{ number_format($paidAmount, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.balance_due') }}</p>
                            <p class="mt-0.5 font-bold tabular-nums {{ $balanceDue > 0 ? 'text-red-600' : 'text-gray-600' }}">₹{{ number_format($balanceDue, 2) }}</p>
                        </div>
                    </div>

                    <a href="{{ route('vendor.orders.show', $order) }}" wire:navigate class="flex min-h-[48px] w-full items-center justify-center rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('vendor.view_order') }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pt-2">{{ $orders->onEachSide(1)->links() }}</div>
@else
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_orders_found') }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-600">
            @if($search ?? false)
                {{ __('vendor.orders_empty_search') }}
            @elseif($status ?? false)
                {{ __('vendor.orders_empty_status') }}
            @else
                {{ __('vendor.orders_empty_default') }}
            @endif
        </p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            @if(($search ?? '') !== '' || ($status ?? '') !== '')
                <button type="button" wire:click="clearFilters" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    {{ __('vendor.clear_filters') }}
                </button>
            @endif
            <a wire:navigate href="{{ route('vendor.orders.new') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                {{ __('vendor.create_order') }}
            </a>
        </div>
    </div>
@endif
