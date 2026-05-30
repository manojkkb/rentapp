@extends('admin.layouts.app')

@section('title', 'Support Chats')

@section('main_class', 'overflow-y-auto p-3 sm:p-6')

@section('content')
@php
    $filterLink = fn (string $status) => route('admin.support.index', array_filter([
        'status' => $status === 'all' ? null : $status,
    ]));
@endphp

<div class="mx-auto max-w-6xl space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-xl font-black text-gray-900 dark:text-white sm:text-3xl">Support Chats</h1>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 sm:text-sm">Vendor conversations — status updates from last message</p>
    </div>

    {{-- Status filter tabs --}}
    <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:gap-2">
        <a href="{{ $filterLink('all') }}"
           class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl px-3 py-2.5 text-xs font-bold transition-colors touch-manipulation sm:min-h-0 sm:justify-start sm:px-4
                  {{ ($statusFilter ?? 'all') === 'all'
                      ? 'bg-green-600 text-white shadow-sm'
                      : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700' }}">
            All
            <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ ($statusFilter ?? 'all') === 'all' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
                {{ ($openTicketsCount ?? 0) + ($closedTicketsCount ?? 0) }}
            </span>
        </a>
        <a href="{{ $filterLink('open') }}"
           class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl px-3 py-2.5 text-xs font-bold transition-colors touch-manipulation sm:min-h-0 sm:justify-start sm:px-4
                  {{ ($statusFilter ?? 'all') === 'open'
                      ? 'bg-amber-500 text-white shadow-sm'
                      : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700' }}">
            <span class="h-2 w-2 rounded-full {{ ($statusFilter ?? 'all') === 'open' ? 'bg-white' : 'bg-amber-500' }}"></span>
            Open
            <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ ($statusFilter ?? 'all') === 'open' ? 'bg-white/20' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">
                {{ $openTicketsCount ?? 0 }}
            </span>
        </a>
        <a href="{{ $filterLink('closed') }}"
           class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl px-3 py-2.5 text-xs font-bold transition-colors touch-manipulation sm:min-h-0 sm:justify-start sm:px-4
                  {{ ($statusFilter ?? 'all') === 'closed'
                      ? 'bg-gray-600 text-white shadow-sm dark:bg-gray-500'
                      : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700' }}">
            <span class="h-2 w-2 rounded-full {{ ($statusFilter ?? 'all') === 'closed' ? 'bg-white' : 'bg-gray-400' }}"></span>
            Closed
            <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ ($statusFilter ?? 'all') === 'closed' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
                {{ $closedTicketsCount ?? 0 }}
            </span>
        </a>
    </div>

    {{-- Mobile card list --}}
    <div class="space-y-3 md:hidden">
        @forelse($conversations as $conversation)
            @php
                $last = $conversation->lastMessage();
                $ticketStatus = $conversation->resolveTicketStatus();
                $isOpen = $ticketStatus === \App\Models\SupportConversation::STATUS_OPEN;
            @endphp
            <a href="{{ route('admin.support.show', $conversation) }}"
               class="block rounded-2xl border bg-white p-4 shadow-sm transition-colors active:bg-gray-50 touch-manipulation
                      {{ $isOpen
                          ? 'border-amber-200 dark:border-amber-800/50 dark:bg-gray-800'
                          : 'border-gray-200 dark:border-gray-700 dark:bg-gray-800' }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                            {{ $conversation->vendor?->name ?? '—' }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $conversation->user?->name ?? $conversation->user?->mobile ?? '—' }}
                        </p>
                    </div>
                    @if($isOpen)
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Open
                        </span>
                    @else
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                            Closed
                        </span>
                    @endif
                </div>

                @if($last)
                    <p class="mt-3 line-clamp-2 text-sm leading-snug text-gray-600 dark:text-gray-300">
                        <span class="font-semibold {{ $last->sender_type === 'vendor' ? 'text-amber-700 dark:text-amber-300' : 'text-green-700 dark:text-green-300' }}">
                            {{ $last->sender_type === 'vendor' ? 'Vendor' : 'You' }}:
                        </span>
                        {{ $last->body }}
                    </p>
                @else
                    <p class="mt-3 text-sm text-gray-400">No messages yet</p>
                @endif

                <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                    <span>{{ $conversation->messages_count }} {{ $conversation->messages_count === 1 ? 'message' : 'messages' }}</span>
                    <span>{{ $conversation->updated_at?->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-12 text-center dark:border-gray-600 dark:bg-gray-800">
                <i class="fas fa-inbox mb-3 text-3xl text-gray-300 dark:text-gray-600"></i>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if(($statusFilter ?? 'all') === 'open')
                        No open tickets right now.
                    @elseif(($statusFilter ?? 'all') === 'closed')
                        No closed tickets yet.
                    @else
                        No support conversations yet.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Vendor</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Opened by</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Messages</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Last activity</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($conversations as $conversation)
                        @php
                            $last = $conversation->lastMessage();
                            $ticketStatus = $conversation->resolveTicketStatus();
                            $isOpen = $ticketStatus === \App\Models\SupportConversation::STATUS_OPEN;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $isOpen ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                            <td class="px-4 py-3">
                                @if($isOpen)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Open
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                        Closed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $conversation->vendor?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $conversation->user?->name ?? $conversation->user?->mobile ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $conversation->messages_count }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $conversation->updated_at?->diffForHumans() }}
                                @if($last)
                                    <div class="mt-0.5 max-w-xs truncate text-xs text-gray-400">
                                        <span class="font-medium {{ $last->sender_type === 'vendor' ? 'text-amber-600' : 'text-green-600' }}">
                                            {{ $last->sender_type === 'vendor' ? 'Vendor' : 'Admin' }}:
                                        </span>
                                        {{ \Illuminate\Support\Str::limit($last->body, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.support.show', $conversation) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700">
                                    <i class="fas fa-comments"></i>
                                    Reply
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                @if(($statusFilter ?? 'all') === 'open')
                                    No open tickets right now.
                                @elseif(($statusFilter ?? 'all') === 'closed')
                                    No closed tickets yet.
                                @else
                                    No support conversations yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($conversations->hasPages())
        <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-4">
            {{ $conversations->links() }}
        </div>
    @endif
</div>
@endsection
