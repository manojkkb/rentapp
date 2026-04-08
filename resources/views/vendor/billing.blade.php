@extends('vendor.layouts.app')

@section('title', __('vendor.billing'))
@section('page-title', __('vendor.billing'))

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Billing</h2>
        <!-- Billing Summary -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-700 font-medium">Outstanding Amount</span>
                <span class="text-lg font-bold text-red-600">₹0.00</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-700 font-medium">Total Paid</span>
                <span class="text-lg font-bold text-emerald-600">₹0.00</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-700 font-medium">Total Billed</span>
                <span class="text-lg font-bold text-gray-900">₹0.00</span>
            </div>
        </div>
        <!-- Billing Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Invoice #</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Example row -->
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">INV-0001</td>
                        <td class="px-4 py-2 text-sm text-gray-700">2026-04-07</td>
                        <td class="px-4 py-2 text-sm text-gray-900">₹1,000.00</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-emerald-100 text-emerald-700">Paid</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                        </td>
                    </tr>
                    <!-- More rows can be added dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
