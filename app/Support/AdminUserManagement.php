<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCustomer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminUserManagement
{
    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        return [
            'all_users' => User::count(),
            'customers' => VendorCustomer::count(),
            'vendor_accounts' => User::whereHas('vendors')->count(),
            'admins' => Admin::count(),
            'kyc_pending' => Vendor::where('is_verified', false)->count(),
            'suspended' => $this->suspendedCount(),
            'active_sessions' => (int) DB::table('sessions')->whereNotNull('user_id')->count(),
        ];
    }

    public function suspendedCount(): int
    {
        $inactiveVendors = Vendor::where('is_active', false)->count();
        $inactiveStaff = (int) DB::table('vendor_users')->where('is_active', false)->count();

        return $inactiveVendors + $inactiveStaff;
    }

    public function allUsers(?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->withCount('vendors')
            ->when($search, fn ($q) => $this->applyUserSearch($q, $search))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function customers(?string $search = null): LengthAwarePaginator
    {
        return VendorCustomer::query()
            ->with(['vendor:id,name', 'user:id,name,mobile'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function vendorAccounts(?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->whereHas('vendors')
            ->with(['vendors:id,name'])
            ->when($search, fn ($q) => $this->applyUserSearch($q, $search))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function admins(?string $search = null): LengthAwarePaginator
    {
        return Admin::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }

    public function kycPending(?string $search = null): LengthAwarePaginator
    {
        return Vendor::query()
            ->where('is_verified', false)
            ->with(['user:id,name,email,mobile'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return array{inactive_vendors: LengthAwarePaginator, inactive_staff: \Illuminate\Support\Collection<int, object>}
     */
    public function suspended(?string $search = null): array
    {
        $inactiveVendors = Vendor::query()
            ->with('user:id,name,email')
            ->where('is_active', false)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10, ['*'], 'vendors_page')
            ->withQueryString();

        $inactiveStaff = DB::table('vendor_users')
            ->join('users', 'vendor_users.user_id', '=', 'users.id')
            ->join('vendors', 'vendor_users.vendor_id', '=', 'vendors.id')
            ->where('vendor_users.is_active', false)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                        ->orWhere('vendors.name', 'like', "%{$search}%");
                });
            })
            ->select(
                'vendor_users.id',
                'vendor_users.is_owner',
                'vendor_users.role',
                'users.name as user_name',
                'users.mobile',
                'vendors.name as vendor_name',
                'vendor_users.updated_at'
            )
            ->orderByDesc('vendor_users.updated_at')
            ->limit(50)
            ->get();

        return [
            'inactive_vendors' => $inactiveVendors,
            'inactive_staff' => $inactiveStaff,
        ];
    }

    public function loginActivity(?string $search = null): LengthAwarePaginator
    {
        return DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->whereNotNull('sessions.user_id')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.mobile', 'like', "%{$search}%");
                });
            })
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name',
                'users.email',
                'users.mobile'
            )
            ->orderByDesc('sessions.last_activity')
            ->paginate(25)
            ->withQueryString();
    }

    private function applyUserSearch($query, string $search): void
    {
        $query->where(function ($inner) use ($search) {
            $inner->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%");
        });
    }
}
