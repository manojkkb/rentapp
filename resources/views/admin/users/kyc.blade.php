@extends('admin.layouts.app')

@section('title', 'KYC Verification - Admin')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">KYC Verification</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vendors pending verification approval</p>
    </div>

    @include('admin.users.partials.alerts')
    @include('admin.users.partials.tabs')
    @include('admin.users.partials.search', ['action' => route('admin.users.kyc'), 'placeholder' => 'Store name or city...'])

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Store</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Owner</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Location</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">GST</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Registered</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($vendors as $vendor)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $vendor->name }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ $vendor->user->name ?? $vendor->owner_name ?? '—' }}
                            @if($vendor->user?->mobile)<div class="text-xs">{{ $vendor->user->mobile }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $vendor->city ?? '—' }}{{ $vendor->state ? ', '.$vendor->state : '' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $vendor->gst_number ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $vendor->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <form action="{{ route('admin.users.kyc.approve', $vendor) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700">
                                    Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No pending KYC verifications.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $vendors->links() }}
</div>
@endsection
