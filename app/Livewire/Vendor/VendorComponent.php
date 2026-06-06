<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

abstract class VendorComponent extends Component
{
    protected ?Vendor $vendor = null;

    public function booted(): void
    {
        $this->vendor = Auth::user()?->currentVendor();

        if (! $this->vendor) {
            $this->redirectRoute('vendor.select');
        }
    }

    protected function vendorId(): int
    {
        return (int) $this->vendor->id;
    }
}
