<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Services\VendorRoleProvisioner;
use Illuminate\Console\Command;

class ProvisionVendorDefaultRoles extends Command
{
    protected $signature = 'vendor:provision-roles {--vendor= : Specific vendor ID}';

    protected $description = 'Create default Manager, Staff, and Cashier roles for vendor(s)';

    public function handle(VendorRoleProvisioner $provisioner): int
    {
        $query = Vendor::query();

        if ($vendorId = $this->option('vendor')) {
            $query->whereKey($vendorId);
        }

        $count = 0;

        $query->each(function (Vendor $vendor) use ($provisioner, &$count) {
            $provisioner->ensureDefaultRoles($vendor, $vendor->user_id);
            $count++;
            $this->line("Provisioned roles for vendor #{$vendor->id} ({$vendor->name})");
        });

        $this->info("Done. Processed {$count} vendor(s).");

        return self::SUCCESS;
    }
}
