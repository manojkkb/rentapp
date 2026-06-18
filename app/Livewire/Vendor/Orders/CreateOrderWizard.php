<?php

namespace App\Livewire\Vendor\Orders;

use App\Livewire\Vendor\VendorComponent;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Models\CreateOrder;
use App\Models\User;
use App\Models\VendorCustomer;
use App\Support\OrderCreateWizardSession;
use App\Support\OrderWizardDateTimeDefaults;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderWizard extends VendorComponent
{
    public int $step = 1;

    public ?int $customerId = null;

    public string $eventName = '';

    public string $startTime = '';

    public string $endTime = '';

    public string $newCustomerName = '';

    public string $newCustomerMobile = '';

    public string $newCustomerAddress = '';

    /** @var list<array{id: int, name: string, mobile: string}> */
    public array $customers = [];

    public bool $showNewCustomerForm = false;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            $this->redirectRoute('vendor.select');

            return;
        }

        if (request()->boolean('reset')) {
            OrderCreateWizardSession::clear();
        }

        $wizard = OrderCreateWizardSession::get();
        if ($wizard !== []) {
            $this->customerId = isset($wizard['customer_id']) ? (int) $wizard['customer_id'] : null;
            $this->eventName = (string) ($wizard['event_name'] ?? '');
            $this->startTime = (string) ($wizard['start_time'] ?? '');
            $this->endTime = (string) ($wizard['end_time'] ?? '');
        }

        if ($this->startTime === '') {
            $this->startTime = OrderWizardDateTimeDefaults::format(OrderWizardDateTimeDefaults::defaultStartAt());
        }
        if ($this->endTime === '') {
            $startAt = Carbon::createFromFormat('Y-m-d H:i', $this->startTime);
            $this->endTime = OrderWizardDateTimeDefaults::format(
                OrderWizardDateTimeDefaults::defaultEndAt($startAt),
            );
        }

        $requestedStep = (int) request()->query('step', 0);
        if ($requestedStep >= 1 && $requestedStep <= 5) {
            $this->step = $requestedStep;
        }

        $this->loadCustomers();
    }

    public function goToStep(int $step): void
    {
        $step = max(1, min(5, $step));

        if ($step > 1 && ! $this->guardStep1()) {
            $this->step = 1;

            return;
        }

        if ($step > 2 && empty(OrderCreateWizardSession::get()['lines'])) {
            $this->step = 2;
            throw ValidationException::withMessages([
                'lines' => [__('vendor.order_wizard_select_at_least_one_item')],
            ]);
        }

        if ($step > 4 && ! OrderCreateWizardSession::hasFulfillment(OrderCreateWizardSession::get())) {
            $this->step = 4;
            throw ValidationException::withMessages([
                'fulfillment' => [__('vendor.order_wizard_complete_fulfillment_first')],
            ]);
        }

        $this->step = $step;
        $this->flashMessage = null;
        $this->resetErrorBag();
        $this->dispatch('wizard-step-changed');
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->showNewCustomerForm = false;
        $this->resetErrorBag('customer_id');
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
    }

    public function openNewCustomerForm(?string $searchQuery = null): void
    {
        $q = trim((string) $searchQuery);
        $this->newCustomerName = ($q !== '' && ! preg_match('/^\d+$/', $q)) ? $q : '';
        $this->newCustomerMobile = preg_match('/^\d{10}$/', $q) ? $q : '';
        $this->newCustomerAddress = '';
        $this->showNewCustomerForm = true;
        $this->resetErrorBag(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);
    }

    public function closeNewCustomerForm(): void
    {
        $this->showNewCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);
        $this->resetErrorBag(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);
    }

    public function saveNewCustomer(): void
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            $this->redirectRoute('vendor.select');

            return;
        }

        $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255'],
            'newCustomerMobile' => ['required', 'digits:10', 'unique:vendor_customers,mobile,NULL,id,vendor_id,'.$vendor->id],
            'newCustomerAddress' => ['nullable', 'string', 'max:500'],
        ], [], [
            'newCustomerName' => __('vendor.customer_name'),
            'newCustomerMobile' => __('vendor.mobile'),
            'newCustomerAddress' => __('vendor.address'),
        ]);

        $user = User::where('mobile', $this->newCustomerMobile)->first();

        if (! $user) {
            $user = User::create([
                'name' => $this->newCustomerName,
                'mobile' => $this->newCustomerMobile,
                'email' => $this->newCustomerMobile.'@rentkia.temp',
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        $customer = VendorCustomer::create([
            'vendor_id' => $vendor->id,
            'user_id' => $user->id,
            'name' => $this->newCustomerName,
            'mobile' => $this->newCustomerMobile,
            'address' => $this->newCustomerAddress !== '' ? $this->newCustomerAddress : null,
            'is_active' => true,
        ]);

        array_unshift($this->customers, [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
        ]);

        $this->customerId = $customer->id;
        $this->showNewCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);

        $this->dispatch('customer-saved', message: __('vendor.customer_added'), customers: $this->customers);
    }

    public function saveStep1(): void
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            $this->redirectRoute('vendor.select');

            return;
        }

        if ($this->customerId === null) {
            throw ValidationException::withMessages([
                'customer_id' => [__('vendor.order_wizard_select_customer_required')],
            ]);
        }

        $request = Request::create('/', 'POST', [
            'customer_id' => $this->customerId,
            'event_name' => $this->eventName,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);

        $payload = CreateOrder::validateForDirectOrder($request, $vendor->id);

        OrderCreateWizardSession::put(array_merge(
            OrderCreateWizardSession::get(),
            [
                'customer_id' => $payload->customerId,
                'event_name' => $payload->eventName,
                'start_time' => $payload->startAt?->format('Y-m-d H:i'),
                'end_time' => $payload->endAt?->format('Y-m-d H:i'),
                'lines' => OrderCreateWizardSession::get()['lines'] ?? [],
            ],
        ));

        $this->step = 2;
        $this->flashMessage = __('vendor.order_wizard_step1_saved');
        $this->resetErrorBag();
        $this->dispatch('wizard-step-changed');
    }

    /**
     * @param  array<string, array<string, mixed>>  $lines
     */
    public function saveItemsStep(array $lines): void
    {
        app(VendorOrderController::class)->wizardSaveLines($lines);
        $this->step = 3;
        $this->flashMessage = __('vendor.order_wizard_step2_saved');
        $this->resetErrorBag();
        $this->dispatch('wizard-step-changed');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSummaryLine(array $data): void
    {
        app(VendorOrderController::class)->wizardUpdateSummaryLine($data);
        $this->flashMessage = __('vendor.order_wizard_summary_line_updated');
        $this->resetErrorBag();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function removeSummaryLine(array $data): void
    {
        app(VendorOrderController::class)->wizardRemoveSummaryLine($data);

        if (empty(OrderCreateWizardSession::get()['lines'])) {
            $this->step = 2;
            throw ValidationException::withMessages([
                'error' => [__('vendor.order_wizard_select_at_least_one_item')],
            ]);
        }

        $this->flashMessage = __('vendor.order_wizard_summary_line_removed');
        $this->resetErrorBag();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveFulfillment(array $data): void
    {
        app(VendorOrderController::class)->wizardSaveFulfillment($data);
        $this->step = 5;
        $this->flashMessage = __('vendor.order_wizard_fulfillment_saved');
        $this->resetErrorBag();
        $this->dispatch('wizard-step-changed');
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    public function placeOrder(array $payment): void
    {
        $order = app(VendorOrderController::class)->wizardPlaceOrder($payment);

        session()->flash('success', __('vendor.order_created_wizard'));

        $this->redirectRoute('vendor.orders.show', $order, navigate: false);
    }

    public function render()
    {
        $vendor = Auth::user()?->currentVendor();
        if (! $vendor) {
            return view('livewire.vendor.orders.create-order-wizard', ['step' => 1]);
        }

        $controller = app(VendorOrderController::class);
        $stepData = [];

        try {
            if ($this->step === 2) {
                $stepData = $controller->wizardItemsViewData();
                $stepData['livewireWizard'] = true;
            } elseif ($this->step === 3) {
                $stepData = $controller->wizardSummaryViewData();
                $stepData['livewireWizard'] = true;
            } elseif ($this->step === 4) {
                $stepData = $controller->wizardFulfillmentViewData();
                $stepData['livewireWizard'] = true;
            } elseif ($this->step === 5) {
                $stepData = $controller->wizardPaymentViewData();
                $stepData['livewireWizard'] = true;
            }
        } catch (\Throwable $e) {
            report($e);
            $this->step = 1;
            session()->flash('error', $e->getMessage());
        }

        return view('livewire.vendor.orders.create-order-wizard', array_merge($stepData, [
            'step' => $this->step,
        ]));
    }

    private function loadCustomers(): void
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            $this->customers = [];

            return;
        }

        $this->customers = VendorCustomer::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'mobile'])
            ->map(fn (VendorCustomer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'mobile' => $c->mobile,
            ])
            ->all();
    }

    private function guardStep1(): bool
    {
        return OrderCreateWizardSession::hasStep1([
            'customer_id' => $this->customerId,
            'event_name' => $this->eventName,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);
    }
}
