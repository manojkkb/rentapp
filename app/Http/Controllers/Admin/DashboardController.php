<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminDashboardAnalytics;

class DashboardController extends Controller
{
    public function __construct(
        private AdminDashboardAnalytics $analytics
    ) {}

    public function index()
    {
        return view('admin.dashboard.index', [
            'dashboard' => $this->analytics->overview(),
        ]);
    }

    public function revenue()
    {
        return view('admin.dashboard.revenue', [
            'data' => $this->analytics->revenue(),
        ]);
    }

    public function bookings()
    {
        return view('admin.dashboard.bookings', [
            'data' => $this->analytics->bookings(),
        ]);
    }

    public function vendors()
    {
        return view('admin.dashboard.vendors', [
            'data' => $this->analytics->vendorPerformance(),
        ]);
    }

    public function cities()
    {
        return view('admin.dashboard.cities', [
            'data' => $this->analytics->cities(),
        ]);
    }
}
