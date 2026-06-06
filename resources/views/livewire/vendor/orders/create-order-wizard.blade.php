<div class="mx-auto w-full max-w-5xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0">
    @include('vendor.orders.partials.wizard-steps', ['current' => $step, 'compact' => true])

    @if($flashMessage)
        <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50/90 px-3 py-2 text-xs font-medium text-emerald-900 sm:text-sm">
            {{ $flashMessage }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 sm:text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($step === 1)
        <div wire:key="wizard-step-1">
            @include('vendor.orders.partials.wizard-step-one-inner')
        </div>
    @elseif($step === 2)
        @error('lines')
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 sm:text-sm">{{ $message }}</div>
        @enderror
        <div wire:key="wizard-step-2">
            @include('vendor.orders.partials.wizard-items-inner', ['livewireWizard' => true])
        </div>
    @elseif($step === 3)
        <div wire:key="wizard-step-3">
            @include('vendor.orders.partials.wizard-summary-inner', ['livewireWizard' => true])
        </div>
    @elseif($step === 4)
        <div wire:key="wizard-step-4">
            @include('vendor.orders.partials.wizard-fulfillment-inner', ['livewireWizard' => true])
        </div>
    @elseif($step === 5)
        <div wire:key="wizard-step-5">
            @include('vendor.orders.partials.wizard-payment-inner', ['livewireWizard' => true])
        </div>
    @endif
</div>
