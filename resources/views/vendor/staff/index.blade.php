@extends('vendor.layouts.app')

@section('title', 'Staff Management - RentApp')
@section('page-title', 'Staff Management')

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Staff Members</h2>
        <p class="text-sm text-gray-600 mt-1">Manage team members and their access</p>
    </div>
    <a href="{{ route('vendor.staff.create') }}" 
       class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
        <i class="fas fa-plus mr-2"></i>
        Add Staff Member
    </a>
</div>

<!-- Staff List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    @if($staff->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Last Login
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($staff as $member)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- User Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $member->name }}</p>
                                    @if($member->pivot->is_owner)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                            <i class="fas fa-crown mr-1"></i>Owner
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Contact -->
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $member->mobile }}</p>
                            @if($member->email && !str_contains($member->email, '@staff.temp') && !str_contains($member->email, '@rentapp.temp'))
                                <p class="text-xs text-gray-500">{{ $member->email }}</p>
                            @endif
                        </td>

                        <!-- Role -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full
                                @if($member->pivot->role == 'manager') bg-purple-100 text-purple-800
                                @elseif($member->pivot->role == 'cashier') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                <i class="fas fa-user-tag mr-1.5"></i>
                                {{ ucfirst($member->pivot->role ?? 'staff') }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            @if($member->pivot->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <!-- Last Login -->
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($member->pivot->last_login_at)
                                {{ \Carbon\Carbon::parse($member->pivot->last_login_at)->diffForHumans() }}
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            @if(!$member->pivot->is_owner)
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Toggle Status -->
                                    <form action="{{ route('vendor.staff.toggle', $member->pivot->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 {{ $member->pivot->is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                                title="{{ $member->pivot->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <a href="{{ route('vendor.staff.edit', $member->pivot->id) }}" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('vendor.staff.destroy', $member->pivot->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to remove this staff member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($staff as $member)
            <div class="p-4">
                <!-- User Info -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $member->name }}</p>
                            <p class="text-xs text-gray-600">{{ $member->mobile }}</p>
                        </div>
                    </div>
                    @if($member->pivot->is_active)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                            Inactive
                        </span>
                    @endif
                </div>

                <!-- Metadata -->
                <div class="flex items-center space-x-4 mb-3 text-xs">
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded">
                        <i class="fas fa-user-tag mr-1"></i>
                        {{ ucfirst($member->pivot->role ?? 'staff') }}
                    </span>
                    @if($member->pivot->is_owner)
                        <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded">
                            <i class="fas fa-crown mr-1"></i>Owner
                        </span>
                    @endif
                </div>

                @if($member->pivot->last_login_at)
                    <p class="text-xs text-gray-500 mb-3">
                        <i class="fas fa-clock mr-1"></i>
                        Last login: {{ \Carbon\Carbon::parse($member->pivot->last_login_at)->diffForHumans() }}
                    </p>
                @endif

                <!-- Actions -->
                @if(!$member->pivot->is_owner)
                    <div class="flex items-center space-x-2 pt-3 border-t border-gray-100">
                        <form action="{{ route('vendor.staff.toggle', $member->pivot->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-3 py-2 {{ $member->pivot->is_active ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700' }} rounded-lg text-sm font-medium">
                                <i class="fas fa-power-off mr-1"></i>
                                {{ $member->pivot->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <a href="{{ route('vendor.staff.edit', $member->pivot->id) }}" 
                           class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium text-center">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>

                        <form action="{{ route('vendor.staff.destroy', $member->pivot->id) }}" 
                              method="POST"
                              onsubmit="return confirm('Remove this staff member?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($staff->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $staff->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Staff Members Yet</h3>
            <p class="text-gray-600 mb-6">Start building your team by adding staff members</p>
            <a href="{{ route('vendor.staff.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                <i class="fas fa-plus mr-2"></i>
                Add First Staff Member
            </a>
        </div>
    @endif
</div>
@endsection
