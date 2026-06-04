<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerReview;
use App\Models\Items;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorCustomer;
use App\Models\VendorUser;
use App\Support\VendorAccess;
use Illuminate\Support\Str;

class VendorGlobalSearch
{
    private const LIMIT = 5;

    /**
     * @return array{groups: list<array<string, mixed>>, query: string}
     */
    public function search(Vendor $vendor, string $query, ?VendorAccess $access): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return ['groups' => [], 'query' => $query];
        }

        $term = $this->likeTerm($query);
        $groups = [];

        if ($access?->can('orders.view')) {
            $groups[] = $this->searchOrders($vendor, $term, $query);
        }

        if ($access?->can('items.view')) {
            $groups[] = $this->searchItems($vendor, $term, $query);
        }

        if ($access?->can('customers.view')) {
            $groups[] = $this->searchCustomers($vendor, $term, $query);
        }

        if ($access?->can('categories.manage')) {
            $groups[] = $this->searchCategories($vendor, $term, $query);
        }

        if ($access?->can('coupons.manage')) {
            $groups[] = $this->searchCoupons($vendor, $term, $query);
        }

        if ($access?->can('staff.view')) {
            $groups[] = $this->searchStaff($vendor, $term, $query);
        }

        if ($access?->can('reviews.view')) {
            $groups[] = $this->searchReviews($vendor, $term, $query);
        }

        return [
            'groups' => array_values(array_filter($groups, fn (array $group) => $group['items'] !== [])),
            'query' => $query,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchOrders(Vendor $vendor, string $term, string $query): array
    {
        $orders = Order::query()
            ->where('vendor_id', $vendor->id)
            ->with(['customer' => fn ($q) => $q->withTrashed()])
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(order_number) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(event_name, '')) LIKE ?", [$term])
                    ->orWhereHas('customer', function ($customerQuery) use ($term) {
                        $customerQuery->withTrashed()
                            ->whereRaw('LOWER(name) LIKE ?', [$term]);
                    });
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'orders',
            'label' => __('vendor.orders'),
            'icon' => 'fa-receipt',
            'view_all_url' => route('vendor.orders.index', ['search' => $query]),
            'items' => $orders->map(function (Order $order) {
                $customerName = $order->customer?->name ?? __('vendor.order_customer_unavailable');
                $subtitle = $customerName;
                if ($order->event_name) {
                    $subtitle .= ' · '.$order->event_name;
                }

                return [
                    'title' => $order->order_number,
                    'subtitle' => $subtitle,
                    'url' => route('vendor.orders.show', $order),
                    'image' => null,
                ];
            })->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchItems(Vendor $vendor, string $term, string $query): array
    {
        $items = Items::query()
            ->where('vendor_id', $vendor->id)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(item_code, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$term]);
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'items',
            'label' => __('vendor.items'),
            'icon' => 'fa-box',
            'view_all_url' => route('vendor.items.index', ['search' => $query]),
            'items' => $items->map(fn (Items $item) => [
                'title' => $item->name,
                'subtitle' => $item->item_code ?: null,
                'url' => route('vendor.items.show', $item),
                'image' => $item->photo_url,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchCustomers(Vendor $vendor, string $term, string $query): array
    {
        $customers = VendorCustomer::query()
            ->where('vendor_id', $vendor->id)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(mobile) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(address, '')) LIKE ?", [$term]);
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'customers',
            'label' => __('vendor.customers'),
            'icon' => 'fa-users',
            'view_all_url' => route('vendor.customers.index', ['search' => $query]),
            'items' => $customers->map(fn (VendorCustomer $customer) => [
                'title' => $customer->name,
                'subtitle' => $customer->mobile,
                'url' => route('vendor.customers.edit', $customer),
                'image' => null,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchCategories(Vendor $vendor, string $term, string $query): array
    {
        $categories = Category::query()
            ->where('vendor_id', $vendor->id)
            ->whereRaw('LOWER(name) LIKE ?', [$term])
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'categories',
            'label' => __('vendor.categories'),
            'icon' => 'fa-tags',
            'view_all_url' => route('vendor.categories.index', ['search' => $query]),
            'items' => $categories->map(fn (Category $category) => [
                'title' => $category->name,
                'subtitle' => null,
                'url' => route('vendor.categories.edit', $category),
                'image' => $category->image_url,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchCoupons(Vendor $vendor, string $term, string $query): array
    {
        $coupons = Coupon::query()
            ->where('vendor_id', $vendor->id)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(code) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$term]);
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'coupons',
            'label' => __('vendor.coupons'),
            'icon' => 'fa-ticket-alt',
            'view_all_url' => route('vendor.coupons.index', ['search' => $query]),
            'items' => $coupons->map(fn (Coupon $coupon) => [
                'title' => $coupon->code,
                'subtitle' => $coupon->name,
                'url' => route('vendor.coupons.edit', $coupon),
                'image' => null,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchStaff(Vendor $vendor, string $term, string $query): array
    {
        $staff = VendorUser::query()
            ->where('vendor_id', $vendor->id)
            ->with(['user', 'vendorRole'])
            ->where(function ($q) use ($term) {
                $q->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(mobile) LIKE ?', [$term]);
                })
                    ->orWhereHas('vendorRole', fn ($roleQuery) => $roleQuery->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhereRaw('LOWER(role) LIKE ?', [$term]);
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'staff',
            'label' => __('vendor.staff'),
            'icon' => 'fa-user-tie',
            'view_all_url' => route('vendor.staff.index', ['search' => $query]),
            'items' => $staff->map(function (VendorUser $member) {
                $user = $member->user;

                return [
                    'title' => $user?->name ?? __('vendor.user'),
                    'subtitle' => $member->roleLabel(),
                    'url' => route('vendor.staff.edit', $member),
                    'image' => $user?->avatar_url,
                ];
            })->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function searchReviews(Vendor $vendor, string $term, string $query): array
    {
        $reviews = CustomerReview::query()
            ->where('vendor_id', $vendor->id)
            ->with(['user', 'order'])
            ->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(COALESCE(review, '')) LIKE ?", [$term])
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->whereRaw('LOWER(order_number) LIKE ?', [$term]));
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return [
            'key' => 'reviews',
            'label' => __('vendor.reviews'),
            'icon' => 'fa-star',
            'view_all_url' => route('vendor.reviews.index'),
            'items' => $reviews->map(function (CustomerReview $review) {
                $reviewText = trim((string) $review->review);

                return [
                    'title' => ($review->user?->name ?? __('vendor.customer')).' · '.$review->rating.'/5',
                    'subtitle' => $reviewText !== '' ? Str::limit($reviewText, 60) : null,
                    'url' => route('vendor.reviews.index'),
                    'image' => $review->user?->avatar_url,
                ];
            })->all(),
        ];
    }

    private function likeTerm(string $query): string
    {
        return '%'.mb_strtolower(trim($query)).'%';
    }
}
