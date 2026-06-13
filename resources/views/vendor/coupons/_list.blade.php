@include('vendor.coupons.partials.coupons-list', [
    'coupons' => $coupons,
    'livewireList' => $livewireList ?? false,
    'search' => $search ?? '',
    'typeFilter' => $typeFilter ?? '',
    'statusFilter' => $statusFilter ?? '',
])
