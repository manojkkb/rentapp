@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('content')
<script>
    function orderWizardSummaryPage() {
        return {
            editOpen: false,
            editItemId: null,
            editName: '',
            editQty: 1,
            editBilling: '',
            editUsesBilling: false,
            removeOpen: false,
            removeItemId: null,
            removeItemName: '',
            openEdit(d) {
                this.editItemId = d.item_id;
                this.editName = d.name ?? '';
                this.editQty = parseInt(String(d.quantity), 10) || 1;
                this.editUsesBilling = !!d.uses_billing;
                if (d.billing_units !== null && d.billing_units !== undefined && d.billing_units !== '') {
                    const n = parseFloat(String(d.billing_units));
                    this.editBilling = Number.isFinite(n) ? String(n) : '';
                } else {
                    this.editBilling = '';
                }
                this.editOpen = true;
            },
            closeEdit() {
                this.editOpen = false;
            },
            openRemove(d) {
                this.removeItemId = d.item_id;
                this.removeItemName = d.name ?? '';
                this.removeOpen = true;
            },
            closeRemove() {
                this.removeOpen = false;
                this.removeItemId = null;
                this.removeItemName = '';
            },
            onEscape() {
                if (this.removeOpen) {
                    this.closeRemove();
                } else if (this.editOpen) {
                    this.closeEdit();
                }
            },
        };
    }
</script>

<div class="mx-auto max-w-2xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0"
     x-data="orderWizardSummaryPage()"
     @wizard-summary-open-edit="openEdit($event.detail)"
     @wizard-summary-open-remove="openRemove($event.detail)"
     @keydown.escape.window="onEscape()">
    @include('vendor.orders.partials.wizard-steps', ['current' => 3, 'compact' => true])

    <div class="mb-3 rounded-xl border border-gray-200/90 bg-white p-3 shadow-sm sm:mb-4 sm:p-4">
        <h3 class="text-sm font-bold text-gray-900">{{ __('vendor.order_wizard_summary') }}</h3>

        @if(session('success'))
            <div class="mt-2 rounded-lg border border-emerald-200 bg-emerald-50/90 px-3 py-2 text-xs font-medium text-emerald-900 sm:text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 sm:text-sm">
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <dl class="mt-2 space-y-3 text-[13px] text-gray-700 sm:text-sm">
            <div class="grid grid-cols-2 gap-2 border-b border-gray-100 pb-3 sm:gap-4 sm:pb-3">
                <div class="min-w-0">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs sm:normal-case sm:font-medium sm:text-gray-700">{{ __('vendor.event_name') }}</dt>
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $wizard['event_name'] }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs sm:normal-case sm:font-medium sm:text-gray-700">{{ __('vendor.customer') }}</dt>
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $customer?->name ?? '—' }}</dd>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:gap-4">
                <div class="min-w-0">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs sm:normal-case sm:font-medium sm:text-gray-700">{{ __('vendor.start_date_time') }}</dt>
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $wizard['start_time'] }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs sm:normal-case sm:font-medium sm:text-gray-700">{{ __('vendor.end_date_time') }}</dt>
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $wizard['end_time'] }}</dd>
                </div>
            </div>
        </dl>
        <ul class="mt-3 divide-y divide-gray-100 border-t border-gray-100 pt-2 text-[13px] text-gray-800 sm:pt-3 sm:text-sm">
            @foreach($lineSummaries as $row)
                @php
                    $linePt = $row['price_type'] ?? 'per_day';
                    $usesBilling = \App\Models\Items::priceTypeUsesBillingUnits($linePt);
                    $bu = $row['billing_units'];
                    $buFmt = ($usesBilling && $bu !== null && $bu !== '')
                        ? (rtrim(rtrim(number_format((float) $bu, 2, '.', ''), '0'), '.') ?: '0')
                        : null;
                    $unitShort = match ($linePt) {
                        'per_minute' => __('vendor.order_wizard_summary_unit_minute'),
                        'per_hour' => __('vendor.order_wizard_summary_unit_hour'),
                        'per_day' => __('vendor.order_wizard_summary_unit_day'),
                        'per_week' => __('vendor.order_wizard_summary_unit_week'),
                        'per_month' => __('vendor.order_wizard_summary_unit_month'),
                        'per_year' => __('vendor.order_wizard_summary_unit_year'),
                        default => '',
                    };
                    $editPayload = [
                        'item_id' => (int) $row['item_id'],
                        'name' => $row['name'],
                        'quantity' => (int) $row['quantity'],
                        'billing_units' => $row['billing_units'],
                        'uses_billing' => $usesBilling,
                    ];
                    $editPayloadB64 = base64_encode(json_encode($editPayload));
                    $removePayloadB64 = base64_encode(json_encode([
                        'item_id' => (int) $row['item_id'],
                        'name' => $row['name'],
                    ]));
                @endphp
                <li class="flex items-center gap-2 py-2.5 first:pt-0 sm:gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-slate-100 to-blue-50 ring-1 ring-gray-200/80 sm:h-12 sm:w-12">
                        @if(! empty($row['photo_url']))
                            <img src="{{ $row['photo_url'] }}"
                                 alt=""
                                 class="h-full w-full object-cover"
                                 loading="lazy"
                                 decoding="async">
                        @else
                            <i class="fas fa-box-open text-lg text-blue-600/90" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1 pr-1">
                        <p class="truncate font-medium leading-snug text-gray-900">{{ $row['name'] }}</p>
                        <p class="mt-0.5 break-words text-[11px] leading-snug text-gray-600 tabular-nums sm:text-xs">
                            ₹{{ number_format((int) round($row['unit_price']), 0, '.', ',') }} × {{ $row['quantity'] }}
                            @if($usesBilling && $buFmt !== null)
                                × {{ $buFmt }}
                            @endif
                            <span class="font-semibold text-gray-800"> = ₹{{ number_format((int) round($row['line_total']), 0, '.', ',') }}</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 items-stretch gap-2 text-right sm:gap-3">
                        <div class="flex min-w-[2.25rem] flex-col items-end justify-center gap-0.5">
                            <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg">{{ $row['quantity'] }}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.order_wizard_qty') }}</span>
                        </div>
                        @if($buFmt !== null)
                            <div class="flex min-w-[2.25rem] flex-col items-end justify-center gap-0.5 border-l border-gray-200 pl-2 sm:pl-3">
                                <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg">{{ $buFmt }}</span>
                                <span class="max-w-[4.5rem] truncate text-[10px] font-semibold capitalize leading-tight text-gray-500 sm:max-w-none">{{ $unitShort }}</span>
                            </div>
                        @endif
                        <div class="relative flex shrink-0 items-center border-l border-gray-200 pl-1 sm:pl-2" x-data="{ menu: false }" @keydown.escape.window="menu = false">
                            <button type="button"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 [touch-action:manipulation]"
                                    @click.stop="menu = !menu"
                                    :aria-expanded="menu"
                                    aria-haspopup="true"
                                    aria-label="{{ __('vendor.order_wizard_summary_more_actions') }}">
                                <i class="fas fa-ellipsis-v text-sm" aria-hidden="true"></i>
                            </button>
                            <div x-show="menu"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 @click.outside="menu = false"
                                 class="absolute right-0 top-full z-[60] mt-1 w-36 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black/5">
                                <button type="button"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                        data-edit-b64="{{ $editPayloadB64 }}"
                                        @click="menu = false; $dispatch('wizard-summary-open-edit', JSON.parse(atob($event.currentTarget.dataset.editB64)))">
                                    <i class="fas fa-pen w-4 text-center text-xs text-gray-400" aria-hidden="true"></i>
                                    {{ __('vendor.edit') }}
                                </button>
                                <button type="button"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                                        data-remove-b64="{{ $removePayloadB64 }}"
                                        @click="menu = false; $dispatch('wizard-summary-open-remove', JSON.parse(atob($event.currentTarget.dataset.removeB64)))">
                                    <i class="fas fa-trash-alt w-4 text-center text-xs" aria-hidden="true"></i>
                                    {{ __('vendor.remove') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 flex items-start justify-between gap-4 border-t border-gray-200 pt-3">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">{{ __('vendor.order_wizard_summary_total_items_label') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900 sm:text-xl">{{ $summaryTotalQuantity }}</p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">{{ __('vendor.order_wizard_summary_total_amount_label') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-700 sm:text-xl">₹{{ number_format((int) round($summaryItemsSubtotal), 0, '.', ',') }}</p>
            </div>
        </div>
    </div>

    <div x-show="editOpen"
         x-cloak
         class="fixed inset-0 z-[80] flex items-end justify-center p-2 sm:items-center sm:p-4"
         role="dialog"
         aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/50" @click="closeEdit()"></div>
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-2xl"
             @click.stop>
            <div class="border-b border-gray-100 px-4 py-3">
                <h4 class="text-base font-bold text-gray-900">{{ __('vendor.order_wizard_summary_edit_line') }}</h4>
                <p class="mt-0.5 truncate text-sm text-gray-600" x-text="editName"></p>
            </div>
            <form method="post"
                  action="{{ route('vendor.orders.create.summary.update-line') }}"
                  class="space-y-3 px-4 py-4">
                @csrf
                <input type="hidden" name="item_id" x-bind:value="editItemId != null ? String(editItemId) : ''">
                <div>
                    <label for="summary_edit_qty" class="mb-1 block text-xs font-semibold text-gray-800">{{ __('vendor.quantity') }}</label>
                    <input id="summary_edit_qty"
                           type="number"
                           name="quantity"
                           min="1"
                           step="1"
                           required
                           x-model="editQty"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25">
                </div>
                <div x-show="editUsesBilling" x-cloak>
                    <label for="summary_edit_billing" class="mb-1 block text-xs font-semibold text-gray-800">{{ __('vendor.billing_units') }}</label>
                    <input id="summary_edit_billing"
                           type="number"
                           name="billing_units"
                           step="0.01"
                           min="0.01"
                           lang="en"
                           x-model="editBilling"
                           x-bind:disabled="!editUsesBilling"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 disabled:bg-gray-50 disabled:text-gray-400">
                </div>
                <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end sm:gap-2">
                    <button type="button"
                            @click="closeEdit()"
                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-gray-100 px-4 text-sm font-semibold text-gray-800 hover:bg-gray-200 sm:w-auto">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:w-auto">
                        {{ __('vendor.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="removeOpen"
         x-cloak
         class="fixed inset-0 z-[85] flex items-end justify-center p-2 sm:items-center sm:p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="summary-remove-title">
        <div class="absolute inset-0 bg-gray-900/50" @click="closeRemove()"></div>
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-2xl"
             @click.stop>
            <div class="border-b border-gray-100 px-4 py-3">
                <h4 id="summary-remove-title" class="text-base font-bold text-gray-900">{{ __('vendor.remove') }}</h4>
                <p class="mt-1 line-clamp-2 text-sm font-medium text-gray-900" x-text="removeItemName"></p>
                <p class="mt-2 text-sm leading-snug text-gray-600">{{ __('vendor.order_wizard_summary_remove_confirm') }}</p>
            </div>
            <form method="post"
                  action="{{ route('vendor.orders.create.summary.remove-line') }}"
                  class="flex flex-col-reverse gap-2 border-t border-gray-100 px-4 py-3 sm:flex-row sm:justify-end sm:gap-2">
                @csrf
                <input type="hidden" name="item_id" x-bind:value="removeItemId != null ? String(removeItemId) : ''">
                <button type="button"
                        @click="closeRemove()"
                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-gray-100 px-4 text-sm font-semibold text-gray-800 hover:bg-gray-200 sm:w-auto">
                    {{ __('vendor.cancel') }}
                </button>
                <button type="submit"
                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:w-auto">
                    {{ __('vendor.remove') }}
                </button>
            </form>
        </div>
    </div>

    <x-order-wizard-actions class="mt-1">
        <a href="{{ route('vendor.orders.create.items') }}"
           class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-blue-700 [touch-action:manipulation] sm:mr-auto">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back') }}
        </a>
        <a href="{{ route('vendor.orders.create.fulfillment') }}"
           class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 sm:w-auto sm:min-w-[8rem]">
            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            {{ __('vendor.order_wizard_continue_fulfillment') }}
        </a>
    </x-order-wizard-actions>
</div>
@endsection
