<?php

namespace App\Livewire\Vendor\Customers;

use App\Livewire\Vendor\VendorComponent;
use App\Models\User;
use App\Models\VendorCustomer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public bool $showCreateModal = false;

    public string $newCustomerName = '';

    public string $newCustomerMobile = '';

    public string $newCustomerAddress = '';

    public ?string $flashMessage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerAddress']);
        $this->resetErrorBag();
    }

    public function saveCustomer(): void
    {
        $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255'],
            'newCustomerMobile' => ['required', 'digits:10', 'unique:vendor_customers,mobile,NULL,id,vendor_id,'.$this->vendorId()],
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

        VendorCustomer::create([
            'vendor_id' => $this->vendorId(),
            'user_id' => $user->id,
            'name' => $this->newCustomerName,
            'mobile' => $this->newCustomerMobile,
            'address' => $this->newCustomerAddress !== '' ? $this->newCustomerAddress : null,
            'is_active' => true,
        ]);

        $this->flashMessage = __('vendor.customer_added');
        $this->closeCreateModal();
        $this->resetPage();
    }

    public function toggleStatus(int $customerId): void
    {
        $customer = VendorCustomer::query()
            ->where('vendor_id', $this->vendorId())
            ->whereKey($customerId)
            ->firstOrFail();

        $customer->update(['is_active' => ! $customer->is_active]);

        $this->flashMessage = $customer->is_active
            ? __('vendor.customer_activated')
            : __('vendor.customer_deactivated');
    }

    public function render()
    {
        $query = VendorCustomer::query()
            ->where('vendor_id', $this->vendorId());

        if ($this->search !== '') {
            $term = '%'.mb_strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(mobile) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(address, '')) LIKE ?", [$term]);
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.vendor.customers.index', [
            'customers' => $customers,
        ]);
    }
}
