<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\ListsVendorLogistics;
use App\Http\Controllers\Vendor\Concerns\ManagesOrderLive;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\Category;
use App\Models\CreateOrder;
use App\Models\Items;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorCustomer;
use App\Support\PdfIndicFonts;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendorOrderController extends Controller
{
    use ListsVendorLogistics;
    use ManagesOrderLive;
    use RedirectsIfNumericRouteKey;

    /** @var string Session payload for multi-step direct order creation */
    private const ORDER_CREATE_WIZARD_KEY = 'vendor_order_create_wizard';

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        return view('vendor.orders.index');
    }

    public function deliveries(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $orders = $this->deliveriesPaginator($vendor);

        return view('vendor.logistics.index', [
            'type' => 'deliveries',
            'orders' => $orders,
            'pageTitle' => __('vendor.deliveries'),
            'pageSubtitle' => __('vendor.deliveries_page_subtitle'),
            'countLabel' => trans_choice('vendor.dashboard_outgoing_choice', $orders->total()),
            'countBadgeClass' => 'bg-sky-50 text-sky-800 ring-1 ring-sky-100',
            'emptyMessage' => __('vendor.dashboard_outgoing_empty'),
            'todayRingClass' => 'ring-1 ring-emerald-200/80 bg-emerald-50/40',
            'tomorrowRingClass' => 'ring-1 ring-sky-200/70 bg-sky-50/35',
            'timeBadgeClass' => 'text-emerald-900 ring-1 ring-emerald-300/70 bg-emerald-100',
        ]);
    }

    public function returns(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $orders = $this->returnsPaginator($vendor);

        return view('vendor.logistics.index', [
            'type' => 'returns',
            'orders' => $orders,
            'pageTitle' => __('vendor.returns'),
            'pageSubtitle' => __('vendor.returns_page_subtitle'),
            'countLabel' => trans_choice('vendor.dashboard_returns_choice', $orders->total()),
            'countBadgeClass' => 'bg-violet-50 text-violet-800 ring-1 ring-violet-100',
            'emptyMessage' => __('vendor.dashboard_returns_empty'),
            'todayRingClass' => 'ring-1 ring-violet-200/80 bg-violet-50/40',
            'tomorrowRingClass' => 'ring-1 ring-violet-200/50 bg-violet-50/25',
            'timeBadgeClass' => 'text-violet-900 ring-1 ring-violet-300/70 bg-violet-100',
        ]);
    }

    /**
     * Step 1: customer, event name, booking dates.
     */
    public function create(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        if ($request->boolean('reset')) {
            $this->orderCreateWizardClear();
        }

        $customers = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $wizard = $this->orderCreateWizardGet();

        return view('vendor.orders.create', [
            'customers' => $customers,
            'wizardPrefill' => $wizard,
        ]);
    }

    /**
     * Step 1 (Livewire): customer, event name, booking dates.
     */
    public function createNew(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        if ($request->boolean('reset')) {
            $this->orderCreateWizardClear();
        }

        return view('vendor.orders.new');
    }

    public function storeWizardStep1(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $payload = CreateOrder::validateForDirectOrder($request, $vendor->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('vendor.orders.create')
                ->withErrors($e->errors())
                ->withInput();
        }

        $this->orderCreateWizardPut(array_merge(
            $this->orderCreateWizardStep1Payload($payload),
            ['lines' => []],
        ));

        return redirect()
            ->route('vendor.orders.create.items')
            ->with('success', __('vendor.order_wizard_step1_saved'));
    }

    /**
     * Step 2: select quantities (and billing units where needed).
     */
    public function createWizardItems()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard)) {
            return redirect()
                ->route('vendor.orders.create')
                ->withErrors(['error' => __('vendor.order_wizard_complete_step1_first')]);
        }

        return view('vendor.orders.create-items', $this->wizardItemsViewData());
    }

    /**
     * @return array<string, mixed>
     */
    public function wizardItemsViewData(): array
    {
        $vendor = Auth::user()->currentVendor();
        $wizard = $this->orderCreateWizardGet();

        $catalogItems = Items::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with([
                'category',
                'variantAttributes' => fn ($q) => $q->ordered(),
                'variants' => fn ($q) => $q->ordered(),
            ])
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $initialLineMeta = [];
        foreach ($wizard['lines'] ?? [] as $row) {
            if (! is_array($row) || empty($row['item_id'])) {
                continue;
            }
            $itemId = (int) $row['item_id'];
            $variantId = isset($row['item_variant_id']) && $row['item_variant_id'] !== '' && $row['item_variant_id'] !== null
                ? (int) $row['item_variant_id']
                : null;
            $lineKey = (string) ($row['line_key'] ?? $this->orderWizardLineKey($itemId, $variantId));
            $qty = (int) ($row['quantity'] ?? 0);
            $bu = array_key_exists('billing_units', $row) && $row['billing_units'] !== null && $row['billing_units'] !== ''
                ? (float) $row['billing_units']
                : null;
            $initialLineMeta[$lineKey] = [
                'line_key' => $lineKey,
                'item_id' => $itemId,
                'item_variant_id' => $variantId,
                'quantity' => $qty,
                'billing_units' => $bu,
                'rental_period' => isset($row['rental_period']) && is_string($row['rental_period']) ? $row['rental_period'] : null,
                'price' => isset($row['price']) && $row['price'] !== '' && $row['price'] !== null ? (float) $row['price'] : null,
            ];
        }

        $bookingDefaultsByPriceType = $this->orderCreateWizardDefaultBillingUnitsByPriceType($wizard);
        foreach ($catalogItems as $i) {
            $pt = in_array($i->rental_period ?? '', Items::rentalPeriodKeys(), true) ? $i->rental_period : 'per_day';
            foreach ($initialLineMeta as $lineKey => &$meta) {
                if ((int) ($meta['item_id'] ?? 0) !== (int) $i->id) {
                    continue;
                }
                $linePt = $meta['rental_period'] ?? null;
                if (! is_string($linePt) || ! in_array($linePt, Items::rentalPeriodKeys(), true)) {
                    $linePt = $pt;
                }
                $meta['rental_period'] = $linePt;
                $meta['uses_billing_units'] = Items::rentalPeriodUsesBillingUnits($linePt);
                if ($meta['billing_units'] === null && $meta['uses_billing_units'] && isset($bookingDefaultsByPriceType[$linePt])) {
                    $meta['billing_units'] = $bookingDefaultsByPriceType[$linePt];
                }
                if ($meta['item_variant_id']) {
                    $variant = $i->variants->firstWhere('id', (int) $meta['item_variant_id']);
                    if ($variant) {
                        $meta['variant_label'] = $variant->displayLabel($i->variantAttributes);
                        $meta['price'] = $meta['price'] ?? (float) $variant->price;
                    }
                } else {
                    $meta['price'] = $meta['price'] ?? (float) $i->price;
                }
            }
            unset($meta);
        }

        if (old('lines') && is_array(old('lines'))) {
            $initialLineMeta = [];
            foreach (old('lines') as $lineKey => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $itemId = (int) ($row['item_id'] ?? $lineKey);
                $variantId = isset($row['item_variant_id']) && $row['item_variant_id'] !== '' && $row['item_variant_id'] !== null
                    ? (int) $row['item_variant_id']
                    : null;
                $key = (string) ($row['line_key'] ?? $this->orderWizardLineKey($itemId, $variantId));
                $initialLineMeta[$key] = [
                    'line_key' => $key,
                    'item_id' => $itemId,
                    'item_variant_id' => $variantId,
                    'quantity' => (int) ($row['quantity'] ?? 0),
                    'billing_units' => isset($row['billing_units']) && $row['billing_units'] !== '' ? (float) $row['billing_units'] : null,
                    'rental_period' => isset($row['rental_period']) && is_string($row['rental_period']) ? $row['rental_period'] : null,
                    'price' => isset($row['price']) && $row['price'] !== '' && $row['price'] !== null ? (float) $row['price'] : null,
                ];
            }
        }

        $catalogItemsForJs = $catalogItems->map(fn (Items $i) => $this->catalogItemPayloadForOrderWizard($i))->values();

        $billingUnitsLabels = collect(Items::rentalPeriodKeys())
            ->filter(fn ($k) => Items::rentalPeriodUsesBillingUnits($k))
            ->mapWithKeys(fn ($k) => [$k => Items::billingUnitsFieldLabel($k)])
            ->all();

        $rentalPeriods = Items::rentalPeriodSelectOptions();

        return [
            'catalogItems' => $catalogItems,
            'categories' => $categories,
            'catalogItemsForJs' => $catalogItemsForJs,
            'billingUnitsLabels' => $billingUnitsLabels,
            'initialLineMeta' => $initialLineMeta,
            'bookingDefaultUnitsByPriceType' => $bookingDefaultsByPriceType,
            'rentalPeriods' => $rentalPeriods,
            'wizard' => $wizard,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $rawLines
     */
    public function wizardSaveLines(array $rawLines): void
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard)) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_complete_step1_first')]]);
        }

        $request = Request::create('/', 'POST', ['lines' => $rawLines]);
        $lines = $this->orderCreateWizardNormalizeLinesFromRequest($request, $vendor);

        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_select_at_least_one_item')]]);
        }

        $validator = Validator::make(
            ['items' => $lines],
            [
                'items' => ['required', 'array', 'min:1'],
                'items.*.item_id' => [
                    'required',
                    'integer',
                    Rule::exists('items', 'id')->where(fn ($q) => $q->where('vendor_id', $vendor->id)),
                ],
                'items.*.item_variant_id' => ['nullable', 'integer'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.billing_units' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
                'items.*.rental_period' => ['nullable', 'string', Rule::in(Items::rentalPeriodKeys())],
                'items.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            ],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        foreach ($lines as $row) {
            $item = Items::where('id', $row['item_id'])
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->with(['variants', 'variantAttributes'])
                ->first();
            if (! $item) {
                throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_invalid_item')]]);
            }

            if ($item->usesVariants()) {
                $variantId = (int) ($row['item_variant_id'] ?? 0);
                if ($variantId < 1) {
                    throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_variant_required')]]);
                }
                $variant = $item->variants->firstWhere('id', $variantId);
                if (! $variant || ! $variant->is_active || ! $variant->is_available) {
                    throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_variant_invalid')]]);
                }
                if ($variant->manage_stock && (int) $variant->stock < (int) $row['quantity']) {
                    throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_variant_insufficient_stock', ['label' => $variant->displayLabel($item->variantAttributes)])]]);
                }
            } elseif (! empty($row['item_variant_id'])) {
                throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_invalid_item')]]);
            }

            $type = $row['rental_period'] ?? $item->rental_period ?? 'per_day';
            if (! in_array($type, Items::rentalPeriodKeys(), true)) {
                $type = 'per_day';
            }
            if (Items::rentalPeriodUsesBillingUnits($type) && empty($row['billing_units'])) {
                throw ValidationException::withMessages(['lines' => [__('vendor.order_wizard_billing_units_required')]]);
            }
        }

        $this->orderCreateWizardPut(['lines' => $lines]);
    }

    public function storeWizardStep2(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $this->wizardSaveLines(is_array($request->input('lines')) ? $request->input('lines') : []);
        } catch (ValidationException $e) {
            return redirect()
                ->route('vendor.orders.create.items')
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('vendor.orders.create.summary')
            ->with('success', __('vendor.order_wizard_step2_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    public function wizardSummaryViewData(): array
    {
        $vendor = Auth::user()->currentVendor();
        $wizard = $this->orderCreateWizardGet();

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $wizard['customer_id'])
            ->first();

        $lineSummaries = $this->orderCreateWizardLineSummaries($vendor, $wizard['lines']);
        $summaryTotalQuantity = array_sum(array_map(
            static fn (array $r): int => (int) ($r['quantity'] ?? 0),
            $lineSummaries
        ));
        $summaryItemsSubtotal = $this->orderCreateWizardFinancialPreview($vendor, $wizard)['sub_total'];

        $billingUnitsLabels = collect(Items::rentalPeriodKeys())
            ->filter(fn ($k) => Items::rentalPeriodUsesBillingUnits($k))
            ->mapWithKeys(fn ($k) => [$k => Items::billingUnitsFieldLabel($k)])
            ->all();

        return [
            'wizard' => $wizard,
            'customer' => $customer,
            'lineSummaries' => $lineSummaries,
            'summaryTotalQuantity' => $summaryTotalQuantity,
            'summaryItemsSubtotal' => $summaryItemsSubtotal,
            'rentalPeriods' => Items::rentalPeriodSelectOptions(),
            'billingUnitsLabels' => $billingUnitsLabels,
            'bookingDefaultUnitsByPriceType' => $this->orderCreateWizardDefaultBillingUnitsByPriceType($wizard),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wizardUpdateSummaryLine(array $data): void
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $request = Request::create('/', 'POST', $data);
        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_add_items_before_checkout')]]);
        }

        $validated = $request->validate([
            'line_key' => ['nullable', 'string', 'max:64'],
            'item_id' => ['required', 'integer', Rule::exists('items', 'id')->where(fn ($q) => $q->where('vendor_id', $vendor->id))],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'rental_period' => ['required', 'string', Rule::in(Items::rentalPeriodKeys())],
            'billing_units' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
        ]);

        $lineKey = trim((string) ($validated['line_key'] ?? ''));
        if ($lineKey === '') {
            $lineKey = (string) (int) $validated['item_id'];
        }

        $wizardItemIds = array_map(static fn ($l) => (int) ($l['item_id'] ?? 0), $wizard['lines']);
        if (! in_array((int) $validated['item_id'], $wizardItemIds, true)) {
            throw ValidationException::withMessages(['line' => [__('vendor.order_wizard_invalid_item')]]);
        }

        $item = Items::where('id', $validated['item_id'])
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with('variants')
            ->first();

        if (! $item) {
            throw ValidationException::withMessages(['line' => [__('vendor.order_wizard_invalid_item')]]);
        }

        $lineRentalPeriod = $validated['rental_period'];
        if (! in_array($lineRentalPeriod, Items::rentalPeriodKeys(), true)) {
            $lineRentalPeriod = $item->rental_period ?? 'per_day';
        }

        $price = round(max(0, (float) $validated['price']), 2);

        $billing = null;
        if (Items::rentalPeriodUsesBillingUnits($lineRentalPeriod)) {
            if ($request->input('billing_units') === null || $request->input('billing_units') === '') {
                throw ValidationException::withMessages(['billing_units' => [__('vendor.order_wizard_billing_units_required')]]);
            }
            $billing = $this->normalizedBillingUnits((float) $request->input('billing_units'), $lineRentalPeriod);
        }

        $lines = $wizard['lines'];
        $found = false;
        foreach ($lines as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $rowKey = (string) ($row['line_key'] ?? $this->orderWizardLineKey(
                (int) ($row['item_id'] ?? 0),
                isset($row['item_variant_id']) ? (int) $row['item_variant_id'] : null
            ));
            if ($rowKey !== $lineKey) {
                continue;
            }
            $lines[$i] = array_merge($row, [
                'line_key' => $lineKey,
                'item_id' => (int) $validated['item_id'],
                'quantity' => (int) $validated['quantity'],
                'rental_period' => $lineRentalPeriod,
                'price' => $price,
            ]);
            if (Items::rentalPeriodUsesBillingUnits($lineRentalPeriod)) {
                $lines[$i]['billing_units'] = $billing;
            } else {
                unset($lines[$i]['billing_units']);
            }
            $found = true;
            break;
        }

        if (! $found) {
            throw ValidationException::withMessages(['line' => [__('vendor.order_wizard_invalid_item')]]);
        }

        $this->orderCreateWizardPut(['lines' => array_values($lines)]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wizardRemoveSummaryLine(array $data): void
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_add_items_before_checkout')]]);
        }

        $validated = Validator::make($data, [
            'line_key' => ['nullable', 'string', 'max:64'],
            'item_id' => ['required', 'integer'],
        ])->validate();

        $lineKey = trim((string) ($validated['line_key'] ?? ''));
        if ($lineKey === '') {
            $lineKey = (string) (int) $validated['item_id'];
        }

        $newLines = array_values(array_filter(
            $wizard['lines'],
            static function ($row) use ($lineKey) {
                if (! is_array($row)) {
                    return false;
                }
                $rowKey = (string) ($row['line_key'] ?? (string) (int) ($row['item_id'] ?? 0));

                return $rowKey !== $lineKey;
            }
        ));

        if (count($newLines) === count($wizard['lines'])) {
            throw ValidationException::withMessages(['line' => [__('vendor.order_wizard_invalid_item')]]);
        }

        if ($newLines === []) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_select_at_least_one_item')]]);
        }

        $this->orderCreateWizardPut(['lines' => $newLines]);
    }

    /**
     * @return array<string, mixed>
     */
    public function wizardFulfillmentViewData(): array
    {
        return [
            'wizard' => $this->orderCreateWizardGet(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wizardSaveFulfillment(array $data): void
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $request = Request::create('/', 'POST', array_merge($data, [
            'pickup_at' => ! empty($data['pickup_at']) ? $data['pickup_at'] : null,
            'delivery_at' => ! empty($data['delivery_at']) ? $data['delivery_at'] : null,
        ]));

        $validated = $request->validate([
            'fulfillment_type' => ['required', Rule::in(['pickup', 'delivery'])],
            'delivery_address' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'delivery'),
                'nullable',
                'string',
                'max:5000',
            ],
            'pickup_at' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'pickup'),
                'nullable',
                'date',
                'after_or_equal:now',
            ],
            'delivery_at' => ['nullable', 'date', 'after_or_equal:now'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        $type = $validated['fulfillment_type'];
        $payload = [
            'fulfillment_type' => $type,
            'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
            'pickup_at' => $type === 'pickup' && ! empty($validated['pickup_at'])
                ? Carbon::parse($validated['pickup_at'])->format('Y-m-d H:i')
                : null,
            'delivery_at' => $type === 'delivery' && ! empty($validated['delivery_at'])
                ? Carbon::parse($validated['delivery_at'])->format('Y-m-d H:i')
                : null,
            'delivery_charge' => $type === 'delivery'
                ? round((float) ($validated['delivery_charge'] ?? 0), 2)
                : 0.0,
        ];

        if ($type === 'delivery') {
            $payload['pickup_at'] = null;
        } else {
            $payload['delivery_at'] = null;
        }

        $this->orderCreateWizardPut($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function wizardPaymentViewData(): array
    {
        $vendor = Auth::user()->currentVendor();
        $wizard = $this->orderCreateWizardGet();

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $wizard['customer_id'])
            ->first();

        $lineSummaries = $this->orderCreateWizardLineSummaries($vendor, $wizard['lines']);

        $paymentPreview = $this->orderCreateWizardFinancialPreview($vendor, $wizard);
        $paymentPreview['old_type'] = 'none';
        $paymentPreview['old_value'] = null;
        $paymentPreview['sd_labels'] = [
            'modal_title' => __('vendor.order_wizard_security_deposit_modal_title'),
            'modal_subtitle' => __('vendor.order_wizard_security_deposit_modal_subtitle'),
            'configure' => __('vendor.order_wizard_security_deposit_configure'),
        ];
        $paymentPreview['deposit_names'] = [
            'none' => __('vendor.order_wizard_sd_type_none'),
            'order_amount' => __('vendor.order_wizard_sd_type_order_pct'),
            'product_security_deposit' => __('vendor.order_wizard_sd_type_product_pct'),
            'fixed_amount' => __('vendor.order_wizard_sd_type_fixed'),
        ];

        return [
            'wizard' => $wizard,
            'customer' => $customer,
            'lineSummaries' => $lineSummaries,
            'paymentPreview' => $paymentPreview,
        ];
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    public function wizardPlaceOrder(array $payment): Order
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_session_expired')]]);
        }

        if (! $this->orderCreateWizardHasFulfillment($wizard)) {
            throw ValidationException::withMessages(['error' => [__('vendor.order_wizard_complete_fulfillment_first')]]);
        }

        $fake = Request::create('/', 'POST', [
            'customer_id' => $wizard['customer_id'],
            'event_name' => $wizard['event_name'],
            'start_time' => $wizard['start_time'],
            'end_time' => $wizard['end_time'],
        ]);

        try {
            $payload = CreateOrder::validateForDirectOrder($fake, $vendor->id);
        } catch (ValidationException $e) {
            $this->orderCreateWizardClear();
            throw $e;
        }

        $request = Request::create('/', 'POST', $payment);
        $validated = $request->validate([
            'initial_payment_amount' => ['nullable', 'numeric', 'min:0.01'],
            'initial_payment_method' => ['nullable', 'string', 'max:50'],
            'security_deposit_payment_amount' => ['nullable', 'numeric', 'min:0.01'],
            'security_deposit_payment_method' => ['nullable', 'string', 'max:50'],
            'security_deposit_type' => ['required', Rule::in(['none', 'order_amount', 'product_security_deposit', 'fixed_amount'])],
            'security_deposit_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['initial_payment_amount']) && trim((string) ($validated['initial_payment_method'] ?? '')) === '') {
            throw ValidationException::withMessages(['initial_payment_method' => [__('vendor.order_wizard_payment_method_required')]]);
        }

        if (! empty($validated['security_deposit_payment_amount']) && trim((string) ($validated['security_deposit_payment_method'] ?? '')) === '') {
            throw ValidationException::withMessages(['security_deposit_payment_method' => [__('vendor.order_wizard_payment_method_required')]]);
        }

        $secType = $validated['security_deposit_type'];
        $secValue = null;
        if ($secType !== 'none') {
            $num = round((float) ($validated['security_deposit_value'] ?? 0), 2);
            if ($num <= 0.009) {
                throw ValidationException::withMessages(['security_deposit_value' => [__('vendor.order_wizard_security_deposit_value_required')]]);
            }
            if (in_array($secType, ['order_amount', 'product_security_deposit'], true) && $num > 100) {
                throw ValidationException::withMessages(['security_deposit_value' => [__('vendor.order_wizard_security_deposit_pct_max')]]);
            }
            $secValue = $num;
        }

        $orderNumber = 'ORD-'.strtoupper(uniqid());

        $order = DB::transaction(function () use ($vendor, $payload, $orderNumber, $wizard, $validated, $secType, $secValue) {
            $attrs = $payload->toDirectOrderAttributes();
            $type = $wizard['fulfillment_type'];
            if ($type === 'delivery') {
                $attrs['fulfillment_type'] = 'delivery';
                $attrs['delivery_address'] = trim((string) ($wizard['delivery_address'] ?? ''));
                $attrs['pickup_at'] = null;
                $attrs['delivery_at'] = ! empty($wizard['delivery_at'])
                    ? Carbon::parse($wizard['delivery_at'])
                    : null;
                $attrs['delivery_charge'] = round((float) ($wizard['delivery_charge'] ?? 0), 2);
            } else {
                $attrs['fulfillment_type'] = 'pickup';
                $addr = trim((string) ($wizard['delivery_address'] ?? ''));
                $attrs['delivery_address'] = $addr !== '' ? $addr : null;
                $attrs['pickup_at'] = ! empty($wizard['pickup_at'])
                    ? Carbon::parse($wizard['pickup_at'])
                    : null;
                $attrs['delivery_at'] = null;
                $attrs['delivery_charge'] = 0;
            }

            $created = Order::create(array_merge($attrs, [
                'order_number' => $orderNumber,
                'vendor_id' => $vendor->id,
                'security_deposit_type' => $secType,
                'security_deposit_value' => $secValue,
            ]));

            $this->persistWizardLinesOnOrder($vendor, $created, $wizard['lines']);
            $this->recalculateOrderFinancials($created);
            $created->refresh();

            $detail = is_array($created->payment_detail) ? $created->payment_detail : [];
            $paidDelta = 0.0;

            $payAmt = round((float) ($validated['initial_payment_amount'] ?? 0), 2);
            if ($payAmt > 0.009) {
                $method = trim((string) ($validated['initial_payment_method'] ?? 'Cash'));
                $detail[] = [
                    'payment_for' => 'order_amount',
                    'method' => $method !== '' ? $method : 'Cash',
                    'amount' => $payAmt,
                    'paid_on' => now()->toDateString(),
                    'recorded_at' => now()->toIso8601String(),
                    'entry_kind' => 'payment',
                ];
                $paidDelta += $payAmt;
            }

            $sdPayAmt = round((float) ($validated['security_deposit_payment_amount'] ?? 0), 2);
            if ($sdPayAmt > 0.009) {
                $secDue = round((float) ($created->security_deposit ?? 0), 2);
                if ($secDue <= 0.009) {
                    throw ValidationException::withMessages([
                        'security_deposit_payment_amount' => [__('vendor.order_wizard_sd_payment_requires_deposit')],
                    ]);
                }
                if ($sdPayAmt > $secDue + 0.009) {
                    throw ValidationException::withMessages([
                        'security_deposit_payment_amount' => [__('vendor.order_wizard_sd_payment_exceeds_deposit')],
                    ]);
                }
                $sdMethod = trim((string) ($validated['security_deposit_payment_method'] ?? 'Cash'));
                $detail[] = [
                    'payment_for' => 'security_deposit',
                    'method' => $sdMethod !== '' ? $sdMethod : 'Cash',
                    'amount' => $sdPayAmt,
                    'paid_on' => now()->toDateString(),
                    'recorded_at' => now()->toIso8601String(),
                    'entry_kind' => 'payment',
                ];
                $paidDelta += $sdPayAmt;
            }

            if ($paidDelta > 0.009) {
                $created->payment_detail = array_values($detail);
                $created->paid_amount = round((float) ($created->paid_amount ?? 0) + $paidDelta, 2);
                $created->save();
            }

            return $created;
        });

        $this->orderCreateWizardClear();

        return $order;
    }

    /**
     * Step 3: read-only summary before fulfillment.
     */
    public function createWizardSummary()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            return redirect()
                ->route('vendor.orders.create.items')
                ->withErrors(['error' => __('vendor.order_wizard_add_items_before_checkout')]);
        }

        return view('vendor.orders.create-summary', $this->wizardSummaryViewData());
    }

    /**
     * Update a single wizard line from the summary step (quantity / billing units).
     */
    public function updateWizardSummaryLine(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $this->wizardUpdateSummaryLine($request->all());
        } catch (ValidationException $e) {
            return redirect()
                ->route('vendor.orders.create.summary')
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('vendor.orders.create.summary')
            ->with('success', __('vendor.order_wizard_summary_line_updated'));
    }

    /**
     * Remove a line from the wizard draft on the summary step.
     */
    public function removeWizardSummaryLine(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $this->wizardRemoveSummaryLine($request->all());
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['error'])) {
                return redirect()
                    ->route('vendor.orders.create.items')
                    ->withErrors($e);
            }

            return redirect()
                ->route('vendor.orders.create.summary')
                ->withErrors($e);
        }

        return redirect()
            ->route('vendor.orders.create.summary')
            ->with('success', __('vendor.order_wizard_summary_line_removed'));
    }

    /**
     * Step 4: pickup or delivery details.
     */
    public function createWizardFulfillment()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            return redirect()
                ->route('vendor.orders.create.items')
                ->withErrors(['error' => __('vendor.order_wizard_add_items_before_checkout')]);
        }

        return view('vendor.orders.create-fulfillment', $this->wizardFulfillmentViewData());
    }

    public function storeWizardFulfillment(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $this->wizardSaveFulfillment($request->all());
        } catch (ValidationException $e) {
            return redirect()
                ->route('vendor.orders.create.fulfillment')
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('vendor.orders.create.payment')
            ->with('success', __('vendor.order_wizard_fulfillment_saved'));
    }

    /**
     * Step 5: optional initial payment, then place order.
     */
    public function createWizardPayment()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $wizard = $this->orderCreateWizardGet();
        if (! $this->orderCreateWizardHasStep1($wizard) || empty($wizard['lines'])) {
            return redirect()
                ->route('vendor.orders.create')
                ->withErrors(['error' => __('vendor.order_wizard_session_expired')]);
        }

        if (! $this->orderCreateWizardHasFulfillment($wizard)) {
            return redirect()
                ->route('vendor.orders.create.fulfillment')
                ->withErrors(['error' => __('vendor.order_wizard_complete_fulfillment_first')]);
        }

        $data = $this->wizardPaymentViewData();
        $data['paymentPreview']['old_type'] = old('security_deposit_type', 'none');
        $data['paymentPreview']['old_value'] = old('security_deposit_value');

        return view('vendor.orders.create-payment', $data);
    }

    public function storeWizardComplete(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $order = $this->wizardPlaceOrder($request->all());
        } catch (ValidationException $e) {
            return redirect()
                ->route('vendor.orders.create.payment')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('vendor.orders.create.payment')
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('vendor.order_created_wizard'));
    }

    /**
     * @return array<string, mixed>
     */
    private function orderCreateWizardStep1Payload(CreateOrder $payload): array
    {
        return [
            'customer_id' => $payload->customerId,
            'event_name' => $payload->eventName,
            'start_time' => $payload->startAt?->format('Y-m-d H:i'),
            'end_time' => $payload->endAt?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @param  array<string, mixed>  $wizard
     */
    private function orderCreateWizardHasStep1(array $wizard): bool
    {
        return isset($wizard['customer_id'], $wizard['event_name'], $wizard['start_time'], $wizard['end_time'])
            && $wizard['event_name'] !== ''
            && $wizard['start_time']
            && $wizard['end_time'];
    }

    /**
     * @param  array<string, mixed>  $wizard
     */
    private function orderCreateWizardHasFulfillment(array $wizard): bool
    {
        $type = $wizard['fulfillment_type'] ?? null;
        if ($type !== 'pickup' && $type !== 'delivery') {
            return false;
        }
        if ($type === 'delivery') {
            return trim((string) ($wizard['delivery_address'] ?? '')) !== '';
        }

        return ! empty($wizard['pickup_at']);
    }

    /**
     * Default billing-unit counts from wizard rental window (step 1), keyed by rental_period.
     * {@see persistWizardLinesOnOrder} uses the same per_day rule as {@see ManagesOrderLive::orderRentDays()}.
     *
     * @param  array<string, mixed>  $wizard
     * @return array<string, float>
     */
    private function orderCreateWizardDefaultBillingUnitsByPriceType(array $wizard): array
    {
        $startStr = $wizard['start_time'] ?? null;
        $endStr = $wizard['end_time'] ?? null;
        if (! is_string($startStr) || ! is_string($endStr) || $startStr === '' || $endStr === '') {
            return [];
        }
        try {
            $start = Carbon::parse($startStr);
            $end = Carbon::parse($endStr);
        } catch (\Throwable) {
            return [];
        }
        if ($end->lte($start)) {
            return [];
        }

        $out = [];
        foreach (Items::rentalPeriodKeys() as $key) {
            if ($key === 'fixed' || ! Items::rentalPeriodUsesBillingUnits($key)) {
                continue;
            }
            $out[$key] = $this->defaultBillingUnitsBetween($start, $end, $key);
        }

        return $out;
    }

    private function defaultBillingUnitsBetween(Carbon $start, Carbon $end, string $rentalPeriod): float
    {
        if ($rentalPeriod === 'per_day') {
            $days = max(1, (int) ceil($start->diffInDays($end)));

            return round((float) $days, 2);
        }

        $seconds = abs($start->diffInSeconds($end));
        $raw = match ($rentalPeriod) {
            'per_minute' => $seconds / 60,
            'per_hour' => $seconds / 3600,
            'per_week' => $seconds / (86400 * 7),
            'per_month' => $seconds / (86400 * 30),
            'per_year' => $seconds / (86400 * 365.25),
            default => $seconds / 86400,
        };

        $raw = max(0.01, $raw);

        return round($raw, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderCreateWizardGet(): array
    {
        $data = session(self::ORDER_CREATE_WIZARD_KEY, []);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function orderCreateWizardPut(array $data): void
    {
        session([self::ORDER_CREATE_WIZARD_KEY => array_merge($this->orderCreateWizardGet(), $data)]);
    }

    private function orderCreateWizardClear(): void
    {
        session()->forget(self::ORDER_CREATE_WIZARD_KEY);
    }

    /**
     * @return list<array{line_key: string, item_id: int, item_variant_id?: int|null, quantity: int, billing_units?: float|null, rental_period?: string, price?: float}>
     */
    private function orderCreateWizardNormalizeLinesFromRequest(Request $request, $vendor): array
    {
        $raw = $request->input('lines', []);
        if (! is_array($raw)) {
            return [];
        }

        $merged = [];
        foreach ($raw as $key => $row) {
            if (! is_array($row)) {
                continue;
            }
            $itemId = (int) ($row['item_id'] ?? $key);
            $qty = (int) ($row['quantity'] ?? 0);
            if ($itemId < 1 || $qty < 1) {
                continue;
            }
            $item = Items::where('id', $itemId)
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->first();
            if (! $item) {
                continue;
            }

            $variantId = isset($row['item_variant_id']) && $row['item_variant_id'] !== '' && $row['item_variant_id'] !== null
                ? (int) $row['item_variant_id']
                : null;
            $lineKey = $this->orderWizardLineKey($itemId, $variantId);
            $type = $row['rental_period'] ?? $item->rental_period ?? 'per_day';
            if (! in_array($type, Items::rentalPeriodKeys(), true)) {
                $type = $item->rental_period ?? 'per_day';
            }
            if (! in_array($type, Items::rentalPeriodKeys(), true)) {
                $type = 'per_day';
            }
            $entry = [
                'line_key' => $lineKey,
                'item_id' => $itemId,
                'quantity' => $qty,
                'rental_period' => $type,
            ];
            if ($variantId) {
                $entry['item_variant_id'] = $variantId;
            }
            if (isset($row['price']) && $row['price'] !== '' && $row['price'] !== null) {
                $entry['price'] = max(0, (float) $row['price']);
            }
            if (Items::rentalPeriodUsesBillingUnits($type)) {
                $bu = $row['billing_units'] ?? null;
                $entry['billing_units'] = $bu !== null && $bu !== '' ? (float) $bu : null;
            }

            if (isset($merged[$lineKey])) {
                $merged[$lineKey]['quantity'] += $qty;
            } else {
                $merged[$lineKey] = $entry;
            }
        }

        return array_values($merged);
    }

    /**
     * @param  list<array{line_key?: string, item_id: int, item_variant_id?: int|null, quantity: int, billing_units?: float|null}>  $lines
     * @return list<array{line_key: string, item_id: int, item_variant_id?: int|null, name: string, variant_label?: string|null, quantity: int, rental_period: string, billing_units: float|null, unit_price: float, line_total: float, photo_url?: string|null}>
     */
    private function orderCreateWizardLineSummaries($vendor, array $lines): array
    {
        $summaries = [];
        foreach ($lines as $row) {
            $item = Items::where('id', $row['item_id'])
                ->where('vendor_id', $vendor->id)
                ->with(['variantAttributes', 'variants'])
                ->first();
            if (! $item) {
                continue;
            }
            $variantId = isset($row['item_variant_id']) ? (int) $row['item_variant_id'] : null;
            $variant = $variantId ? $item->variants->firstWhere('id', $variantId) : null;
            $ctx = $this->resolveWizardLineContext($item, $variant, $row);
            $lineRentalPeriod = $ctx['rental_period'];
            $billingUnits = $ctx['billing_units'];
            $qty = (int) $row['quantity'];
            $unitPrice = $ctx['unit_price'];
            $variantLabel = $variant ? $variant->displayLabel($item->variantAttributes) : null;
            $displayName = $item->name;
            if ($variantLabel) {
                $displayName .= ' ('.$variantLabel.')';
            }
            $lineKey = (string) ($row['line_key'] ?? $this->orderWizardLineKey((int) $row['item_id'], $variantId));
            $temp = new OrderItem([
                'price' => $unitPrice,
                'quantity' => $qty,
                'rental_period' => $lineRentalPeriod,
                'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
            ]);
            $lineTotal = $temp->lineSubtotal();
            $summaries[] = [
                'line_key' => $lineKey,
                'item_id' => (int) $row['item_id'],
                'item_variant_id' => $variantId,
                'name' => $displayName,
                'variant_label' => $variantLabel,
                'photo_url' => $variant?->photo_url ?? $item->photo_url,
                'quantity' => $qty,
                'rental_period' => $lineRentalPeriod,
                'billing_units' => $row['billing_units'] ?? null,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return $summaries;
    }

    /**
     * Subtotal + delivery for payment step preview before the order row exists.
     *
     * @param  array<string, mixed>  $wizard
     * @return array{sub_total: float, delivery_charge: float, discount_total: float, line_grand: float, grand_total: float}
     */
    private function orderCreateWizardFinancialPreview($vendor, array $wizard): array
    {
        $lines = $wizard['lines'] ?? [];
        $subTotal = 0.0;
        foreach ($lines as $row) {
            if (! is_array($row) || ! isset($row['item_id'])) {
                continue;
            }
            $itemId = (int) $row['item_id'];
            $qty = (int) ($row['quantity'] ?? 0);
            if ($itemId < 1 || $qty < 1) {
                continue;
            }
            $item = Items::where('id', $itemId)->where('vendor_id', $vendor->id)->with('variants')->first();
            if (! $item) {
                continue;
            }
            $variantId = isset($row['item_variant_id']) ? (int) $row['item_variant_id'] : null;
            $variant = $variantId ? $item->variants->firstWhere('id', $variantId) : null;
            $ctx = $this->resolveWizardLineContext($item, $variant, $row);
            $lineRentalPeriod = $ctx['rental_period'];
            $billingUnits = $ctx['billing_units'];
            $qty = (int) ($row['quantity'] ?? 0);
            $unitPrice = $ctx['unit_price'];
            $temp = new OrderItem([
                'price' => $unitPrice,
                'quantity' => $qty,
                'rental_period' => $lineRentalPeriod,
                'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
            ]);
            $subTotal += $temp->lineSubtotal();
        }
        $subTotal = round($subTotal, 2);
        $deliveryCharge = 0.0;
        if (($wizard['fulfillment_type'] ?? '') === 'delivery') {
            $deliveryCharge = round((float) ($wizard['delivery_charge'] ?? 0), 2);
        }
        $discountTotal = 0.0;
        $lineGrand = round($subTotal - $discountTotal + $deliveryCharge, 2);
        $grandTotal = round($lineGrand, 2);

        return [
            'sub_total' => $subTotal,
            'delivery_charge' => $deliveryCharge,
            'discount_total' => $discountTotal,
            'line_grand' => $lineGrand,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * @param  list<array{item_id: int, quantity: int, billing_units?: float|null}>  $lines
     */
    private function persistWizardLinesOnOrder($vendor, Order $order, array $lines): void
    {
        $rentDays = $this->orderRentDays($order);

        foreach ($lines as $itemData) {
            $item = Items::where('id', $itemData['item_id'])
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->with(['variantAttributes', 'variants'])
                ->first();

            if (! $item) {
                continue;
            }

            $variantId = isset($itemData['item_variant_id']) ? (int) $itemData['item_variant_id'] : null;
            $variant = $variantId ? $item->variants->firstWhere('id', $variantId) : null;
            $ctx = $this->resolveWizardLineContext($item, $variant, $itemData);
            $lineRentalPeriod = $ctx['rental_period'];
            $billingUnits = $ctx['billing_units'];
            $unitPrice = $ctx['unit_price'];
            $variantLabel = $variant ? $variant->displayLabel($item->variantAttributes) : null;
            $itemName = $item->name;
            if ($variantLabel) {
                $itemName .= ' ('.$variantLabel.')';
            }

            $existingQuery = OrderItem::where('order_id', $order->id)->where('item_id', $item->id);
            if ($variantId) {
                $existingQuery->where('item_variant_id', $variantId);
            } else {
                $existingQuery->whereNull('item_variant_id');
            }
            $existing = $existingQuery->first();

            if ($existing) {
                $existing->update([
                    'quantity' => $existing->quantity + (int) $itemData['quantity'],
                    'price' => $unitPrice,
                    'rental_period' => $lineRentalPeriod,
                    'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                ]);
                $existing->refresh();
                $existing->refreshLineTotals();
            } else {
                $oi = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_variant_id' => $variantId,
                    'item_name' => $itemName,
                    'variant_label' => $variantLabel,
                    'price' => $unitPrice,
                    'quantity' => (int) $itemData['quantity'],
                    'rental_period' => $lineRentalPeriod,
                    'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                    'total_price' => 0,
                ]);
                $oi->refresh();
                $oi->refreshLineTotals();
            }
        }

        $order->refresh()->load('items');
        if ($order->items->isEmpty()) {
            throw new \InvalidArgumentException(__('vendor.order_wizard_no_valid_lines'));
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor to continue']);
        }

        if ($order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.orders.index')->withErrors(['error' => 'This order belongs to another store.']);
        }

        if ($redirect = $this->redirectIfNumericRouteKey($request, $order, 'vendor.orders.show')) {
            return $redirect;
        }

        $order->load([
            'customer' => fn ($q) => $q->withTrashed(),
            'items.item',
            'coupon',
        ]);

        $catalogItems = Items::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $orderBillingLabels = collect(Items::rentalPeriodKeys())
            ->filter(fn ($k) => Items::rentalPeriodUsesBillingUnits($k))
            ->mapWithKeys(fn ($k) => [$k => Items::billingUnitsFieldLabel($k)])
            ->all();

        $availableItems = $catalogItems;
        $cartBillingUnitsLabels = $orderBillingLabels;

        $categories = Category::query()
            ->whereIn('id', $catalogItems->pluck('category_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        $orderCartJson = $this->orderJsonPayload($order);

        return view('vendor.orders.show', compact('order', 'catalogItems', 'orderBillingLabels', 'availableItems', 'cartBillingUnitsLabels', 'categories', 'orderCartJson'));
    }

    /**
     * Printable order sheet (opens in browser; use ?autoprint=1 to trigger print dialog).
     */
    public function printOrder(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->load([
            'customer' => fn ($q) => $q->withTrashed(),
            'items.item.category',
            'vendor',
        ]);

        return view('vendor.orders.print', [
            'order' => $order,
            'autoprint' => $request->boolean('autoprint'),
            'forPdf' => false,
        ]);
    }

    /**
     * Download invoice file for an order.
     */
    public function downloadInvoice(Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->load([
            'customer' => fn ($q) => $q->withTrashed(),
            'items.item.category',
            'vendor',
        ]);

        $locale = session('language') ?? Auth::user()?->language ?? config('app.locale');
        app()->setLocale($locale);

        $safeOrderNumber = preg_replace('/[^A-Za-z0-9_\-]/', '-', (string) ($order->order_number ?? 'invoice-'.$order->id));
        $filename = 'invoice-'.$safeOrderNumber.'.pdf';

        PdfIndicFonts::ensureInstalled();

        $pdf = Pdf::loadView('vendor.orders.print', [
            'order' => $order,
            'autoprint' => false,
            'forPdf' => true,
            'isInvoice' => true,
            'pdfFontFamily' => PdfIndicFonts::cssFontFamily(),
        ])->setPaper('a4')
            ->setOption('defaultFont', PdfIndicFonts::defaultFontFamily())
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('enable_font_subsetting', true);

        return $pdf->download($filename);
    }

    /**
     * Update order (dates, fulfillment, lines, discounts, deposit, payments, status).
     */
    public function update(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        if ($order->isLockedForEditing()) {
            return back()->withErrors(['error' => __('vendor.order_edit_not_allowed_locked')]);
        }

        $validated = $request->validate([
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'fulfillment_type' => 'required|in:pickup,delivery',
            'delivery_address' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'delivery'),
                'nullable',
                'string',
                'max:5000',
            ],
            'pickup_at' => 'nullable|date',
            'delivery_at' => 'nullable|date',
            'delivery_charge' => 'nullable|numeric|min:0|max:999999',
            'discount_amount' => 'nullable|numeric|min:0',
            'coupon_discount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:64',
            'security_deposit_type' => 'required|in:none,order_amount,product_security_deposit,fixed_amount',
            'security_deposit_value' => 'nullable|numeric|min:0',
            'token_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_detail_json' => 'nullable|string|max:65535',
            'status' => 'required|in:'.implode(',', Order::STATUSES),
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => [
                'nullable',
                'integer',
                Rule::exists('order_items', 'id')->where(fn ($q) => $q->where('order_id', $order->id)),
            ],
            'items.*.item_id' => [
                'nullable',
                'integer',
                Rule::exists('items', 'id')->where(fn ($q) => $q->where('vendor_id', $vendor->id)),
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.billing_units' => 'nullable|numeric|min:0',
            'items.*.rental_period' => ['nullable', 'string', Rule::in(Items::rentalPeriodKeys())],
            'remove_item_ids' => 'nullable|array',
            'remove_item_ids.*' => [
                'integer',
                Rule::exists('order_items', 'id')->where(fn ($q) => $q->where('order_id', $order->id)),
            ],
        ]);

        if (! $order->canTransitionTo((string) $validated['status'])) {
            return back()->withErrors(['status' => __('vendor.order_invalid_status_transition')])->withInput();
        }

        $paymentDetail = [];
        if ($request->has('payment_detail_json')) {
            $raw = trim((string) $request->input('payment_detail_json', ''));
            if ($raw === '') {
                $paymentDetail = [];
            } else {
                $decoded = json_decode($raw, true);
                if (! is_array($decoded)) {
                    return back()->withErrors(['payment_detail_json' => __('vendor.order_invalid_payment_json')])->withInput();
                }
                $paymentDetail = array_values($decoded);
            }
        } else {
            $paymentDetail = is_array($order->payment_detail) ? $order->payment_detail : [];
        }

        $removeIds = array_values(array_map('intval', array_filter((array) $request->input('remove_item_ids', []))));

        $lineRows = collect($validated['items'])->filter(function ($row) {
            return ! empty($row['order_item_id']) || ! empty($row['item_id']);
        })->filter(function ($row) use ($removeIds) {
            if (! empty($row['order_item_id']) && in_array((int) $row['order_item_id'], $removeIds, true)) {
                return false;
            }

            return true;
        })->values()->all();

        if (count($lineRows) < 1) {
            return back()->withErrors(['items' => __('vendor.order_needs_one_line')])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $order, $vendor, $paymentDetail, $removeIds, $lineRows) {
                $startAt = ! empty($validated['start_at']) ? Carbon::parse($validated['start_at']) : null;
                $endAt = ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;
                $rentDays = ($startAt && $endAt) ? max(1, (int) ceil($startAt->diffInDays($endAt))) : 1;

                if (count($removeIds)) {
                    OrderItem::where('order_id', $order->id)->whereIn('id', $removeIds)->delete();
                }

                $addr = trim((string) ($validated['delivery_address'] ?? ''));
                $deliveryAddress = $addr !== '' ? $addr : null;

                $prevCouponCode = $order->coupon_code;
                $isDelivery = $validated['fulfillment_type'] === 'delivery';
                $pickupAt = ! $isDelivery && ! empty($validated['pickup_at'])
                    ? Carbon::parse($validated['pickup_at'])
                    : null;
                $deliveryAt = $isDelivery && ! empty($validated['delivery_at'])
                    ? Carbon::parse($validated['delivery_at'])
                    : null;

                $order->fill([
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'fulfillment_type' => $validated['fulfillment_type'],
                    'delivery_address' => $deliveryAddress,
                    'pickup_at' => $pickupAt,
                    'delivery_at' => $deliveryAt,
                    'delivery_charge' => $validated['fulfillment_type'] === 'delivery'
                        ? round((float) ($validated['delivery_charge'] ?? 0), 2)
                        : 0,
                    'discount_amount' => round((float) ($validated['discount_amount'] ?? 0), 2),
                    'coupon_discount' => round((float) ($validated['coupon_discount'] ?? 0), 2),
                    'coupon_code' => $validated['coupon_code'] ?: null,
                    'security_deposit_type' => $validated['security_deposit_type'],
                    'security_deposit_value' => isset($validated['security_deposit_value'])
                        ? round((float) $validated['security_deposit_value'], 2)
                        : null,
                    'token_amount' => round((float) ($validated['token_amount'] ?? 0), 2),
                    'paid_amount' => round((float) ($validated['paid_amount'] ?? 0), 2),
                    'payment_detail' => $paymentDetail,
                    'status' => $validated['status'],
                ]);

                if (($validated['coupon_code'] ?? '') !== ($prevCouponCode ?? '')) {
                    $order->coupon_id = null;
                }

                $order->save();

                foreach ($lineRows as $row) {
                    $qty = (int) $row['quantity'];
                    $rentalPeriodInput = $row['rental_period'] ?? null;
                    $billingIn = isset($row['billing_units']) ? (float) $row['billing_units'] : null;

                    if (! empty($row['order_item_id'])) {
                        $oi = OrderItem::where('order_id', $order->id)->where('id', $row['order_item_id'])->first();
                        if (! $oi) {
                            continue;
                        }
                        $lineType = $rentalPeriodInput ?: ($oi->rental_period ?? ($oi->item?->rental_period ?? 'per_day'));
                        $lineType = in_array($lineType, Items::rentalPeriodKeys(), true) ? $lineType : 'per_day';
                        $units = $this->normalizedBillingUnits($billingIn, $lineType);

                        $oi->update([
                            'quantity' => $qty,
                            'rental_period' => $lineType,
                            'billing_units' => Items::rentalPeriodUsesBillingUnits($lineType) ? $units : null,
                            'start_at' => $order->start_at,
                            'end_at' => $order->end_at,
                            'rent_days' => $rentDays,
                        ]);
                        $oi->refresh();
                        $oi->refreshLineTotals();
                    } elseif (! empty($row['item_id'])) {
                        $item = Items::where('vendor_id', $vendor->id)->where('id', $row['item_id'])->firstOrFail();
                        $lineType = $rentalPeriodInput ?: ($item->rental_period ?? 'per_day');
                        $lineType = in_array($lineType, Items::rentalPeriodKeys(), true) ? $lineType : 'per_day';
                        $units = $this->normalizedBillingUnits($billingIn, $lineType);

                        $oi = OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'item_name' => $item->name,
                            'price' => $item->price,
                            'quantity' => $qty,
                            'rental_period' => $lineType,
                            'billing_units' => Items::rentalPeriodUsesBillingUnits($lineType) ? $units : null,
                            'start_at' => $order->start_at,
                            'end_at' => $order->end_at,
                            'rent_days' => $rentDays,
                            'total_price' => 0,
                        ]);
                        $oi->refresh();
                        $oi->refreshLineTotals();
                    }
                }

                $order->refresh()->load('items');

                if ($order->items->isEmpty()) {
                    throw new \InvalidArgumentException(__('vendor.order_needs_one_line'));
                }

                $this->recalculateOrderFinancials($order);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('vendor.order_updated'));
    }

    protected function recalculateOrderFinancials(Order $order): void
    {
        $order->load('items');

        $subTotal = round($order->items->sum(fn (OrderItem $i) => $i->lineSubtotal()), 2);
        $discountAmount = round((float) ($order->discount_amount ?? 0), 2);
        $couponDiscount = round((float) ($order->coupon_discount ?? 0), 2);
        $discountTotal = round($discountAmount + $couponDiscount, 2);

        $deliveryCharge = 0.0;
        if (($order->fulfillment_type ?? 'pickup') === 'delivery') {
            $deliveryCharge = round((float) ($order->delivery_charge ?? 0), 2);
        }

        $lateFeesTotal = round($order->items->sum(fn (OrderItem $i) => (float) ($i->late_fee ?? 0)), 2);
        $damageFeesTotal = round($order->items->sum(fn (OrderItem $i) => (float) ($i->damage_fee ?? 0)), 2);
        $lostFeesTotal = round($order->items->sum(fn (OrderItem $i) => (float) ($i->lost_fee ?? 0)), 2);
        $refundsTotal = round($order->items->sum(fn (OrderItem $i) => (float) ($i->refund_amount ?? 0)), 2);
        $taxFromLines = round($order->items->sum(fn (OrderItem $i) => (float) ($i->tax_amount ?? 0)), 2);

        $lineGrand = round($subTotal - $discountTotal + $deliveryCharge, 2);
        $extraChargesTotal = round((float) ($order->extra_charges_total ?? 0), 2);
        $grandTotal = round(
            $lineGrand + $extraChargesTotal + $lateFeesTotal + $damageFeesTotal + $lostFeesTotal + $taxFromLines - $refundsTotal,
            2
        );

        $securityDeposit = $this->computeSecurityDepositFromState($order, $subTotal, $lineGrand);

        $order->update([
            'sub_total' => $subTotal,
            'tax_total' => $taxFromLines,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
            'late_fees_total' => $lateFeesTotal,
            'damage_fees_total' => $damageFeesTotal,
            'lost_fees_total' => $lostFeesTotal,
            'refunds_total' => $refundsTotal,
            'security_deposit' => $securityDeposit,
        ]);
    }

    protected function computeSecurityDepositFromState(Order $order, float $subTotal, float $grandTotal): float
    {
        $type = $order->security_deposit_type ?? 'none';
        $value = (float) ($order->security_deposit_value ?? 0);

        if ($type === 'none' || $value <= 0) {
            return 0.0;
        }

        if ($type === 'fixed_amount') {
            return round($value, 2);
        }

        if ($type === 'order_amount') {
            return round($grandTotal * $value / 100, 2);
        }

        if ($type === 'product_security_deposit') {
            return round($subTotal * $value / 100, 2);
        }

        return 0.0;
    }

    protected function normalizedBillingUnits(?float $value, string $lineRentalPeriod): float
    {
        if (! Items::rentalPeriodUsesBillingUnits($lineRentalPeriod)) {
            return 1.0;
        }

        $v = $value !== null ? (float) $value : 1.0;
        if ($v < 0.01) {
            $v = 1.0;
        }

        return round($v, 2);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{rental_period: string, unit_price: float, billing_units: float|null}
     */
    private function resolveWizardLineContext(Items $item, ?ItemVariant $variant, array $row): array
    {
        $lineRentalPeriod = $row['rental_period'] ?? $item->rental_period ?? 'per_day';
        if (! is_string($lineRentalPeriod) || ! in_array($lineRentalPeriod, Items::rentalPeriodKeys(), true)) {
            $lineRentalPeriod = $item->rental_period ?? 'per_day';
        }
        if (! in_array($lineRentalPeriod, Items::rentalPeriodKeys(), true)) {
            $lineRentalPeriod = 'per_day';
        }

        $defaultPrice = $variant ? (float) $variant->price : (float) $item->price;
        $unitPrice = isset($row['price']) && $row['price'] !== '' && $row['price'] !== null
            ? (float) $row['price']
            : $defaultPrice;
        if ($unitPrice < 0) {
            $unitPrice = $defaultPrice;
        }

        $billingUnits = null;
        if (Items::rentalPeriodUsesBillingUnits($lineRentalPeriod)) {
            $billingUnits = $this->normalizedBillingUnits(
                isset($row['billing_units']) ? (float) $row['billing_units'] : null,
                $lineRentalPeriod
            );
        }

        return [
            'rental_period' => $lineRentalPeriod,
            'unit_price' => round($unitPrice, 2),
            'billing_units' => $billingUnits,
        ];
    }

    private function orderWizardLineKey(int $itemId, ?int $variantId): string
    {
        return $variantId ? "{$itemId}_v{$variantId}" : (string) $itemId;
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogItemPayloadForOrderWizard(Items $i): array
    {
        $pt = in_array($i->rental_period ?? '', Items::rentalPeriodKeys(), true) ? $i->rental_period : 'per_day';
        $payload = [
            'id' => $i->id,
            'uuid' => $i->uuid,
            'item_code' => $i->item_code,
            'slug' => $i->slug,
            'name' => $i->name,
            'price' => (float) $i->price,
            'photo_url' => $i->photo_url,
            'category_id' => $i->category_id,
            'category' => $i->category ? ['id' => $i->category->id, 'name' => $i->category->name] : null,
            'stock' => (int) ($i->usesVariants() ? $i->effectiveStock() : ($i->stock ?? 0)),
            'manage_stock' => (bool) ($i->manage_stock ?? false),
            'rental_period' => $pt,
            'uses_billing_units' => Items::rentalPeriodUsesBillingUnits($pt),
            'has_variants' => $i->usesVariants(),
            'variants' => [],
        ];

        if ($i->usesVariants()) {
            $attributes = $i->relationLoaded('variantAttributes') ? $i->variantAttributes : $i->variantAttributes()->ordered()->get();
            $variants = $i->relationLoaded('variants') ? $i->variants : $i->variants()->ordered()->get();
            $activeVariants = $variants->filter(fn (ItemVariant $v) => $v->is_active && $v->is_available);
            $prices = $activeVariants->map(fn (ItemVariant $v) => (float) $v->price);

            $payload['variants'] = $variants->map(fn (ItemVariant $v) => [
                'id' => $v->id,
                'label' => $v->displayLabel($attributes),
                'price' => (float) $v->price,
                'stock' => (int) $v->stock,
                'manage_stock' => (bool) $v->manage_stock,
                'is_available' => (bool) ($v->is_active && $v->is_available),
                'variant_code' => $v->variant_code,
            ])->values()->all();
            $payload['price'] = $prices->isNotEmpty() ? (float) $prices->min() : (float) $i->price;
            $payload['price_min'] = $prices->isNotEmpty() ? (float) $prices->min() : null;
            $payload['price_max'] = $prices->isNotEmpty() ? (float) $prices->max() : null;
        }

        return $payload;
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        $request->validate([
            'status' => 'required|in:'.implode(',', Order::STATUSES),
        ]);

        if ($order->isLockedForEditing()) {
            return back()->withErrors(['status' => __('vendor.order_edit_not_allowed_locked')]);
        }

        if (! $order->canTransitionTo((string) $request->input('status'))) {
            return back()->withErrors(['status' => __('vendor.order_invalid_status_transition')]);
        }

        $order->update([
            'status' => $request->input('status'),
        ]);

        return back()->with('success', __('vendor.status_updated'));
    }
}
