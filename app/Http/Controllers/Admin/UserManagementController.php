<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\AdminUserManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function __construct(
        private AdminUserManagement $users
    ) {}

    public function index(Request $request)
    {
        return view('admin.users.index', [
            'counts' => $this->users->counts(),
            'users' => $this->users->allUsers($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function customers(Request $request)
    {
        return view('admin.users.customers', [
            'counts' => $this->users->counts(),
            'customers' => $this->users->customers($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function vendorAccounts(Request $request)
    {
        return view('admin.users.vendor-accounts', [
            'counts' => $this->users->counts(),
            'users' => $this->users->vendorAccounts($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function admins(Request $request)
    {
        return view('admin.users.admins', [
            'counts' => $this->users->counts(),
            'admins' => $this->users->admins($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function kyc(Request $request)
    {
        return view('admin.users.kyc', [
            'counts' => $this->users->counts(),
            'vendors' => $this->users->kycPending($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function suspended(Request $request)
    {
        $data = $this->users->suspended($request->query('q'));

        return view('admin.users.suspended', [
            'counts' => $this->users->counts(),
            'inactiveVendors' => $data['inactive_vendors'],
            'inactiveStaff' => $data['inactive_staff'],
            'search' => $request->query('q', ''),
        ]);
    }

    public function loginActivity(Request $request)
    {
        return view('admin.users.login-activity', [
            'counts' => $this->users->counts(),
            'sessions' => $this->users->loginActivity($request->query('q')),
            'search' => $request->query('q', ''),
        ]);
    }

    public function approveKyc(Vendor $vendor)
    {
        $vendor->update(['is_verified' => true]);

        return back()->with('success', "{$vendor->name} has been verified.");
    }

    public function toggleVendorActive(Vendor $vendor)
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);

        $status = $vendor->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$vendor->name} has been {$status}.");
    }

    public function toggleStaffActive(int $vendorUser)
    {
        $pivot = DB::table('vendor_users')->where('id', $vendorUser)->first();

        if (! $pivot) {
            return back()->withErrors(['error' => 'Staff record not found.']);
        }

        DB::table('vendor_users')
            ->where('id', $vendorUser)
            ->update(['is_active' => ! $pivot->is_active]);

        return back()->with('success', 'Staff access updated.');
    }
}
