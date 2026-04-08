<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorCalendarController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->currentVendor();

        $events = [];

        if ($vendor) {
            $orders = Order::where('vendor_id', $vendor->id)
                ->with(['customer:id,name,mobile', 'items:id,order_id,item_name,quantity,start_at,end_at'])
                ->get();

            $statusColors = [
                'pending'    => ['bg' => '#f59e0b', 'border' => '#d97706', 'text' => '#000'],
                'confirmed'  => ['bg' => '#3b82f6', 'border' => '#2563eb', 'text' => '#fff'],
                'ongoing'    => ['bg' => '#10b981', 'border' => '#059669', 'text' => '#fff'],
                'completed'  => ['bg' => '#6b7280', 'border' => '#4b5563', 'text' => '#fff'],
                'cancelled'  => ['bg' => '#ef4444', 'border' => '#dc2626', 'text' => '#fff'],
                'overdue'    => ['bg' => '#f97316', 'border' => '#ea580c', 'text' => '#fff'],
            ];

            $events = $orders->map(function ($order) use ($statusColors) {
                $colors = $statusColors[$order->status] ?? $statusColors['pending'];

                return [
                    'id'              => $order->id,
                    'title'           => '#' . $order->order_number . ' - ' . ($order->customer->name ?? 'Walk-in'),
                    'start'           => $order->start_at->toIso8601String(),
                    'backgroundColor' => $colors['bg'],
                    'borderColor'     => $colors['border'],
                    'textColor'       => $colors['text'],
                    'extendedProps'   => [
                        'order_number'    => $order->order_number,
                        'customer_name'   => $order->customer->name ?? 'Walk-in',
                        'customer_mobile' => $order->customer->mobile ?? '',
                        'status'          => $order->status,
                        'start_at'        => $order->start_at->format('M j, Y'),
                        'end_at'          => $order->end_at->format('M j, Y'),
                        'grand_total'     => number_format($order->grand_total, 2),
                        'items_count'     => $order->items->count(),
                        'items'           => $order->items->map(fn($i) => [
                            'name'     => $i->item_name,
                            'qty'      => $i->quantity,
                            'start_at' => $i->start_at?->format('M j, Y'),
                            'end_at'   => $i->end_at?->format('M j, Y'),
                        ]),
                    ],
                ];
            })->values();
        }

        return view('vendor.calendar.index', compact('events'));
    }

    public function events(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor) {
            return response()->json([]);
        }

        $query = Order::where('vendor_id', $vendor->id)
            ->with(['customer:id,name,mobile', 'items:id,order_id,item_name,quantity,start_at,end_at']);

        if ($request->filled('start')) {
            $query->where('end_at', '>=', Carbon::parse($request->start));
        }
        if ($request->filled('end')) {
            $query->where('start_at', '<=', Carbon::parse($request->end));
        }

        $orders = $query->get();

        $events = $orders->map(function ($order) {
            $statusColors = [
                'pending'    => ['bg' => '#f59e0b', 'border' => '#d97706', 'text' => '#000'],
                'confirmed'  => ['bg' => '#3b82f6', 'border' => '#2563eb', 'text' => '#fff'],
                'ongoing'    => ['bg' => '#10b981', 'border' => '#059669', 'text' => '#fff'],
                'completed'  => ['bg' => '#6b7280', 'border' => '#4b5563', 'text' => '#fff'],
                'cancelled'  => ['bg' => '#ef4444', 'border' => '#dc2626', 'text' => '#fff'],
                'overdue'    => ['bg' => '#f97316', 'border' => '#ea580c', 'text' => '#fff'],
            ];

            $colors = $statusColors[$order->status] ?? $statusColors['pending'];

            return [
                'id'              => $order->id,
                'title'           => '#' . $order->order_number . ' - ' . ($order->customer->name ?? 'Walk-in'),
                'start'           => $order->start_at->toIso8601String(),
                'backgroundColor' => $colors['bg'],
                'borderColor'     => $colors['border'],
                'textColor'       => $colors['text'],
                'extendedProps'   => [
                    'order_number'  => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Walk-in',
                    'customer_mobile' => $order->customer->mobile ?? '',
                    'status'        => $order->status,
                    'start_at'      => $order->start_at->format('M j, Y'),
                    'end_at'        => $order->end_at->format('M j, Y'),
                    'grand_total'   => number_format($order->grand_total, 2),
                    'items_count'   => $order->items->count(),
                    'items'         => $order->items->map(fn($i) => [
                        'name'     => $i->item_name,
                        'qty'      => $i->quantity,
                        'start_at' => $i->start_at?->format('M j, Y'),
                        'end_at'   => $i->end_at?->format('M j, Y'),
                    ]),
                ],
            ];
        });

        return response()->json($events);
    }
}
