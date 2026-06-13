@php
    $livewireWizard = $livewireWizard ?? false;
@endphp

@php
    use App\Support\InvoiceDocument;
    use Carbon\Carbon;

    $formatWizardDateTime = static function (?string $value): string {
        if (! $value) {
            return '—';
        }
        try {
            return InvoiceDocument::formatDate(Carbon::parse($value));
        } catch (\Throwable) {
            return $value;
        }
    };
    $startTimeDisplay = $formatWizardDateTime($wizard['start_time'] ?? null);
    $endTimeDisplay = $formatWizardDateTime($wizard['end_time'] ?? null);

    $btnPrimary = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800';
    $btnOutlineNeutral = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition [touch-action:manipulation] hover:bg-gray-50';
    $btnPrimaryLg = 'inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition hover:bg-emerald-700 sm:w-auto';
    $btnOutlineNeutralLg = 'inline-flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:w-auto';
@endphp
@php
    $orderWizardSummaryConfig = [
        'livewireWizard' => $livewireWizard ?? false,
        'rentalPeriods' => $rentalPeriods,
        'billingUnitsLabels' => $billingUnitsLabels,
        'bookingDefaultUnitsByPriceType' => $bookingDefaultUnitsByPriceType ?? [],
        'i18n' => [
            'billing_units' => __('vendor.billing_units'),
        ],
    ];
@endphp

<div class="w-full"
     x-data="orderWizardSummary(@js($orderWizardSummaryConfig))"
     data-wizard-alpine-root
     @wizard-summary-open-edit="openEdit($event.detail)"
     @wizard-summary-open-remove="openRemove($event.detail)"
     @keydown.escape.window="onEscape()">
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
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $startTimeDisplay }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs sm:normal-case sm:font-medium sm:text-gray-700">{{ __('vendor.end_date_time') }}</dt>
                    <dd class="mt-0.5 break-words text-xs font-medium leading-snug text-gray-900 sm:text-sm">{{ $endTimeDisplay }}</dd>
                </div>
            </div>
        </dl>
        <ul class="mt-3 divide-y divide-gray-100 border-t border-gray-100 pt-2 text-[13px] text-gray-800 sm:pt-3 sm:text-sm">
            @foreach($lineSummaries as $row)
                @php
                    $linePt = $row['rental_period'] ?? 'per_day';
                    $usesBilling = \App\Models\Items::rentalPeriodUsesBillingUnits($linePt);
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
                        'line_key' => $row['line_key'] ?? (string) (int) $row['item_id'],
                        'item_id' => (int) $row['item_id'],
                        'name' => $row['name'],
                        'quantity' => (int) $row['quantity'],
                        'unit_price' => (float) $row['unit_price'],
                        'rental_period' => $linePt,
                        'billing_units' => $row['billing_units'],
                        'uses_billing' => $usesBilling,
                    ];
                    $editPayloadB64 = base64_encode(json_encode($editPayload));
                    $removePayloadB64 = base64_encode(json_encode([
                        'line_key' => $row['line_key'] ?? (string) (int) $row['item_id'],
                        'item_id' => (int) $row['item_id'],
                        'name' => $row['name'],
                    ]));
                @endphp
                <li class="flex items-center gap-2 py-2.5 first:pt-0 sm:gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-slate-100 to-emerald-50 ring-1 ring-gray-200/80 sm:h-12 sm:w-12">
                        @if(! empty($row['photo_url']))
                            <img src="{{ $row['photo_url'] }}"
                                 alt=""
                                 class="h-full w-full object-cover"
                                 loading="lazy"
                                 decoding="async">
                        @else
                            <i class="fas fa-box-open text-lg text-emerald-600/90" aria-hidden="true"></i>
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
                                    <i class="fas fa-pen w-4 text-center text-xs text-emerald-500" aria-hidden="true"></i>
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
         aria-modal="true"
         @keydown.escape.window="onEscape()">
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="absolute inset-0 bg-gray-900/55 backdrop-blur-[1px]"
             @click="closeEdit()"></div>
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 sm:rounded-2xl"
             @click.stop>
            <div class="relative overflow-hidden border-b border-emerald-100 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-600 px-4 py-4 text-white sm:px-5">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-8 right-8 h-20 w-20 rounded-full bg-white/5"></div>
                <div class="relative flex items-start gap-3 pr-8">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                        <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-base font-bold leading-tight">{{ __('vendor.order_wizard_summary_edit_line') }}</h4>
                        <p class="mt-1 line-clamp-2 text-sm text-emerald-100" x-text="editName"></p>
                    </div>
                </div>
                <button type="button"
                        @click="closeEdit()"
                        class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                        aria-label="{{ __('vendor.cancel') }}">
                    <i class="fas fa-times text-sm" aria-hidden="true"></i>
                </button>
            </div>
            <form method="post"
                  @if(! ($livewireWizard ?? false)) action="{{ route('vendor.orders.create.summary.update-line') }}" @endif
                  @submit.prevent="submitEditLine($event)"
                  class="space-y-4 px-4 py-4 sm:px-5 sm:py-5">
                @if(! ($livewireWizard ?? false)) @csrf @endif
                <input type="hidden" name="line_key" x-bind:value="editLineKey || ''">
                <input type="hidden" name="item_id" x-bind:value="editItemId != null ? String(editItemId) : ''">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 sm:col-span-1">
                        <label for="summary_edit_price" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                            <i class="fas fa-rupee-sign text-[10px] text-emerald-600" aria-hidden="true"></i>
                            {{ __('vendor.price') }}
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-500">₹</span>
                            <input id="summary_edit_price"
                                   type="number"
                                   name="price"
                                   min="0"
                                   step="0.01"
                                   inputmode="decimal"
                                   required
                                   x-model="editPrice"
                                   class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-8 pr-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="summary_edit_qty" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                            <i class="fas fa-cubes text-[10px] text-emerald-600" aria-hidden="true"></i>
                            {{ __('vendor.quantity') }}
                        </label>
                        <input id="summary_edit_qty"
                               type="number"
                               name="quantity"
                               min="1"
                               step="1"
                               required
                               x-model.number="editQty"
                               class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div>
                    <label for="summary_edit_rental" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                        <i class="fas fa-clock text-[10px] text-emerald-600" aria-hidden="true"></i>
                        {{ __('vendor.rental_period') }}
                    </label>
                    <div class="relative">
                        <select id="summary_edit_rental"
                                name="rental_period"
                                x-model="editRentalPeriod"
                                @change="onEditRentalChange()"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50/50 px-3 pr-9 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach($rentalPeriods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                            <i class="fas fa-chevron-down text-xs" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
                <div x-show="editUsesBilling" x-cloak x-transition>
                    <label for="summary_edit_billing" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                        <i class="fas fa-hourglass-half text-[10px] text-emerald-600" aria-hidden="true"></i>
                        <span x-text="editBillingLabel()"></span>
                    </label>
                    <input id="summary_edit_billing"
                           type="number"
                           name="billing_units"
                           step="0.01"
                           min="0.01"
                           lang="en"
                           x-model="editBilling"
                           x-bind:required="editUsesBilling"
                           class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/50 px-4 py-3 ring-1 ring-emerald-100/80">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">{{ __('vendor.order_wizard_summary_total_amount_label') }}</p>
                            <p class="mt-0.5 truncate text-xs text-gray-600">
                                <span x-text="editRentalPeriodLabel()"></span>
                                <template x-if="editUsesBilling && editBilling">
                                    <span> · <span x-text="formatBillingUnitsDisplay(editBilling)"></span></span>
                                </template>
                            </p>
                        </div>
                        <p class="shrink-0 text-xl font-bold tabular-nums text-emerald-700 sm:text-2xl">
                            ₹<span x-text="formatRupeeInt(editPreviewTotal())"></span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button"
                            @click="closeEdit()"
                            class="{{ $btnOutlineNeutralLg }}">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="{{ $btnPrimaryLg }}">
                        <i class="fas fa-check text-xs" aria-hidden="true"></i>
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
                  @if(! ($livewireWizard ?? false)) action="{{ route('vendor.orders.create.summary.remove-line') }}" @endif
                  @submit.prevent="submitRemoveLine($event)"
                  class="flex flex-col-reverse gap-2 border-t border-gray-100 px-4 py-3 sm:flex-row sm:justify-end sm:gap-2">
                @if(! ($livewireWizard ?? false)) @csrf @endif
                <input type="hidden" name="line_key" x-bind:value="removeLineKey || ''">
                <input type="hidden" name="item_id" x-bind:value="removeItemId != null ? String(removeItemId) : ''">
                <button type="button"
                        @click="closeRemove()"
                        class="{{ $btnOutlineNeutralLg }}">
                    {{ __('vendor.cancel') }}
                </button>
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:w-auto">
                    {{ __('vendor.remove') }}
                </button>
            </form>
        </div>
    </div>

    <x-order-wizard-actions class="mt-1">
        @if($livewireWizard ?? false)
            <button type="button" wire:click="goToStep(2)" class="{{ $btnOutlineNeutral }} sm:mr-auto">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back') }}
            </button>
            <button type="button"
                    wire:click="goToStep(4)"
                    wire:loading.attr="disabled"
                    wire:target="goToStep(4)"
                    class="{{ $btnPrimary }} w-full sm:w-auto sm:min-w-[8rem] disabled:opacity-70">
                <span wire:loading.remove wire:target="goToStep(4)">
                    <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    {{ __('vendor.order_wizard_continue_fulfillment') }}
                </span>
                <span wire:loading wire:target="goToStep(4)">
                    <i class="fas fa-spinner fa-spin text-xs" aria-hidden="true"></i>
                </span>
            </button>
        @else
        <a href="{{ route('vendor.orders.create.items') }}"
           class="{{ $btnOutlineNeutral }} sm:mr-auto">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back') }}
        </a>
        <a href="{{ route('vendor.orders.create.fulfillment') }}"
           class="{{ $btnPrimary }} w-full sm:w-auto sm:min-w-[8rem]">
            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            {{ __('vendor.order_wizard_continue_fulfillment') }}
        </a>
        @endif
    </x-order-wizard-actions>
</div>
