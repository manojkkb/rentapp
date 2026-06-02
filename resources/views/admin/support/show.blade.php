@extends('admin.layouts.app')

@section('title', 'Support Chat')

@section('main_class', 'flex min-h-0 flex-col overflow-hidden p-0 sm:p-6')

@push('vite-before-app')
    @vite(['resources/js/vendor-support.js'])
@endpush

@section('content')
<div
    class="mx-auto flex min-h-0 w-full max-w-3xl flex-1 flex-col"
    x-data="vendorSupportChat(@js([
        'messages' => $messages,
        'socketUrl' => $socketUrl,
        'socketToken' => $socketToken,
        'socketConfigured' => $socketConfigured,
        'sendUrl' => route('admin.support.messages.store', $conversation),
        'ticketStatus' => $ticketStatus,
    ]))"
    x-init="init()"
>
    {{-- Header --}}
    <div class="shrink-0 border-b border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:pb-4">
        <a href="{{ route('admin.support.index') }}"
           class="inline-flex min-h-[44px] items-center gap-1 text-xs font-semibold text-green-600 hover:underline touch-manipulation dark:text-green-400">
            <i class="fas fa-arrow-left text-[10px]"></i>
            All chats
        </a>

        <div class="mt-1 flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-black text-gray-900 dark:text-white sm:text-xl">
                    {{ $conversation->vendor?->name ?? 'Vendor' }}
                </h1>
                <p class="mt-0.5 truncate text-[11px] text-gray-500 dark:text-gray-400 sm:text-sm">
                    {{ $conversation->user?->name ?? '—' }}
                    @if($conversation->user?->mobile)
                        · {{ $conversation->user->mobile }}
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-1.5">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-semibold sm:text-xs"
                    :class="ticketStatus === 'open'
                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                >
                    <span class="h-1.5 w-1.5 rounded-full sm:h-2 sm:w-2"
                          :class="ticketStatus === 'open' ? 'bg-amber-500' : 'bg-gray-400'"></span>
                    <span x-text="ticketStatus === 'open' ? 'Open' : 'Closed'"></span>
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-medium sm:text-xs"
                    :class="connected ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                >
                    <span class="h-1.5 w-1.5 rounded-full sm:h-2 sm:w-2" :class="connected ? 'bg-green-500' : 'bg-gray-400'"></span>
                    <span x-text="connected ? 'Live' : '…'"></span>
                </span>
            </div>
        </div>

        <p class="mt-2 hidden text-xs text-gray-500 dark:text-gray-400 sm:block"
           x-text="ticketStatus === 'open' ? 'Vendor is waiting for your reply.' : 'You replied last — ticket is closed until vendor messages again.'"></p>

        <p
            x-show="socketError"
            x-cloak
            class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100"
            x-text="socketError"
        ></p>
    </div>

    {{-- Chat panel --}}
    <div class="flex min-h-0 flex-1 flex-col bg-white dark:bg-gray-800 sm:overflow-hidden sm:rounded-2xl sm:border sm:border-gray-200 sm:shadow-sm dark:sm:border-gray-700">
        <div
            x-ref="messageList"
            class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-y-contain px-3 py-3 [-webkit-overflow-scrolling:touch] sm:p-4"
        >
            <template x-if="messages.length === 0">
                <div class="flex min-h-[10rem] flex-col items-center justify-center px-4 text-center text-sm text-gray-400 sm:min-h-[200px]">
                    <i class="fas fa-comments mb-3 text-4xl text-gray-300 dark:text-gray-600"></i>
                    <p>No messages yet. Send a reply to start the conversation.</p>
                </div>
            </template>

            <template x-for="msg in messages" :key="msg.id">
                <div
                    class="flex"
                    :class="msg.sender_type === 'admin' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[92%] rounded-2xl px-3.5 py-2.5 text-[15px] leading-snug shadow-sm sm:max-w-[85%] sm:text-sm"
                        :class="msg.sender_type === 'admin'
                            ? 'rounded-br-md bg-green-600 text-white'
                            : 'rounded-bl-md border border-gray-200 bg-gray-50 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100'"
                    >
                        <p class="whitespace-pre-wrap break-words" x-text="msg.body"></p>
                        <p class="mt-1 text-[10px] opacity-70" x-text="formatTime(msg.created_at)"></p>
                    </div>
                </div>
            </template>
        </div>

        <div class="shrink-0 border-t border-gray-200 bg-white p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] dark:border-gray-700 dark:bg-gray-800 sm:p-4 sm:pb-4">
            <form @submit.prevent="send()" class="flex items-end gap-2">
                <textarea
                    x-model="body"
                    rows="1"
                    maxlength="2000"
                    :disabled="sending"
                    x-ref="messageInput"
                    @focus="onComposerFocus()"
                    class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border border-gray-300 px-3 py-2.5 text-base leading-snug focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20 disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-white sm:text-sm"
                    placeholder="Reply to vendor…"
                    @keydown.enter.prevent="if (!$event.shiftKey) send()"
                ></textarea>
                <button
                    type="submit"
                    :disabled="sending || !body.trim()"
                    aria-label="Send reply"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-600 text-white active:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50 touch-manipulation sm:min-h-[44px] sm:w-auto sm:px-4"
                >
                    <i class="fas fa-paper-plane text-base" :class="{ 'fa-spin': sending }"></i>
                    <span class="hidden sm:ml-2 sm:inline text-sm font-bold">Send</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
