<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $status = $request->query('status', 'all');

        $subscriptions = Subscription::query()
            ->with([
                'vendor:id,name,slug,city',
                'user:id,name,mobile,email',
                'subscriptionPlan:id,name,type,billing_cycle',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('subscriptionPlan', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
        ];

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'search' => $search,
            'status' => $status,
            'counts' => $counts,
        ]);
    }
}
