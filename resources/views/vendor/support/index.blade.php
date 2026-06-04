@extends('vendor.layouts.app')

@section('title', __('vendor.help_support'))
@section('page-title', __('vendor.help_support'))

@section('main_bottom_class', 'flex min-h-0 flex-col overflow-hidden p-0 sm:p-4 md:p-6 pb-[4.5rem] md:pb-6')

@push('vite-before-app')
    @vite(['resources/js/vendor-support.js'])
@endpush

@section('content')
<div
    class="mx-auto flex min-h-0 w-full max-w-6xl flex-1 flex-col"
    x-data="vendorSupportChat(@js([
        'messages' => $messages,
        'socketUrl' => $socketUrl,
        'socketToken' => $socketToken,
        'socketConfigured' => $socketConfigured,
        'sendUrl' => route('vendor.support.messages.store'),
        'ticketStatus' => $ticketStatus,
    ]))"
    x-init="init()"
>
    {{-- Header --}}
    <div class="shrink-0 border-b border-gray-200 bg-white px-3 py-3 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:pb-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-bold text-gray-900 sm:text-xl">{{ __('vendor.help_support') }}</h1>
                <p class="mt-0.5 line-clamp-2 text-[11px] leading-snug text-gray-500 sm:text-sm">{{ __('vendor.support_subtitle') }}</p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-1.5">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-semibold sm:text-xs"
                    :class="ticketStatus === 'open'
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-gray-100 text-gray-600'"
                >
                    <span class="h-1.5 w-1.5 rounded-full sm:h-2 sm:w-2"
                          :class="ticketStatus === 'open' ? 'bg-amber-500' : 'bg-gray-400'"></span>
                    <span x-text="ticketStatus === 'open' ? @js(__('vendor.support_ticket_open')) : @js(__('vendor.support_ticket_closed'))"></span>
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-medium sm:text-xs"
                    :class="connected ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'"
                >
                    <span class="h-1.5 w-1.5 rounded-full sm:h-2 sm:w-2" :class="connected ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                    <span x-text="connected ? @js(__('vendor.support_connected')) : @js(__('vendor.support_connecting'))"></span>
                </span>
            </div>
        </div>

        <p
            x-show="socketError"
            x-cloak
            class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"
            x-text="socketError"
        ></p>

        {{-- Mobile tabs: Chat | Contact --}}
        <div class="mt-3 grid grid-cols-2 gap-2 md:hidden">
            <button
                type="button"
                @click="mobileTab = 'chat'"
                class="rounded-lg py-2.5 text-xs font-semibold transition-colors touch-manipulation"
                :class="mobileTab === 'chat' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700'"
            >
                <i class="fas fa-comments mr-1"></i>
                {{ __('vendor.support_chat_title') }}
            </button>
            <button
                type="button"
                @click="mobileTab = 'contact'"
                class="rounded-lg py-2.5 text-xs font-semibold transition-colors touch-manipulation"
                :class="mobileTab === 'contact' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700'"
            >
                <i class="fas fa-headset mr-1"></i>
                {{ __('vendor.support_contact_title') }}
            </button>
        </div>
    </div>

    <div class="grid min-h-0 flex-1 grid-cols-1 gap-0 md:gap-6 lg:grid-cols-3">
        {{-- Contact card --}}
        <div
            class="min-h-0 shrink-0 overflow-y-auto px-3 py-3 sm:px-0 lg:col-span-1"
            :class="mobileTab === 'contact' ? 'block' : 'hidden md:block'"
        >
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="mb-3 hidden items-center gap-2 text-sm font-bold text-gray-900 md:flex">
                    <i class="fas fa-headset text-emerald-600"></i>
                    {{ __('vendor.support_contact_title') }}
                </h2>

                @if($supportPhone !== '')
                    <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50/50 p-3 sm:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">{{ __('vendor.support_phone_label') }}</p>
                        <p class="mt-1 text-base font-semibold text-gray-900 sm:text-sm">{{ $supportPhone }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}"
                               class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white active:bg-emerald-800 touch-manipulation">
                                <i class="fas fa-phone"></i>
                                {{ __('vendor.support_call') }}
                            </a>
                            @if($whatsappUrl)
                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                                   class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 active:bg-emerald-50 touch-manipulation">
                                    <i class="fab fa-whatsapp text-lg"></i>
                                    WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="mb-4 text-sm text-gray-500">{{ __('vendor.support_no_phone') }}</p>
                @endif

                @if($supportEmail !== '')
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 sm:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.support_email_label') }}</p>
                        <p class="mt-1 break-all text-base font-medium text-gray-900 sm:text-sm">{{ $supportEmail }}</p>
                        <a href="mailto:{{ $supportEmail }}"
                           class="mt-3 flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 active:bg-gray-50 touch-manipulation">
                            <i class="fas fa-envelope"></i>
                            {{ __('vendor.support_email_action') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Chat --}}
        <div
            class="flex min-h-0 flex-1 flex-col border-gray-200 bg-white md:rounded-xl md:border md:shadow-sm lg:col-span-2"
            :class="mobileTab === 'chat' ? 'flex' : 'hidden md:flex'"
        >
            <div class="hidden shrink-0 border-b border-gray-200 px-4 py-3 md:block">
                <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.support_chat_title') }}</h2>
                <p class="text-xs text-gray-500">{{ __('vendor.support_chat_hint') }}</p>
            </div>

            <div
                x-ref="messageList"
                class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-y-contain px-3 py-3 [-webkit-overflow-scrolling:touch] sm:px-4"
                style="min-height: 0;"
            >
                <template x-if="messages.length === 0">
                    <div class="flex min-h-[12rem] flex-col items-center justify-center px-4 text-center text-sm text-gray-400 sm:min-h-[200px]">
                        <i class="fas fa-comments mb-3 text-4xl text-gray-300"></i>
                        <p class="max-w-xs leading-relaxed">{{ __('vendor.support_empty_chat') }}</p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div
                        class="flex"
                        :class="msg.sender_type === 'vendor' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[92%] rounded-2xl px-3.5 py-2.5 text-[15px] leading-snug shadow-sm sm:max-w-[75%] sm:text-sm"
                            :class="msg.sender_type === 'vendor'
                                ? 'rounded-br-md bg-emerald-600 text-white'
                                : 'rounded-bl-md border border-gray-200 bg-gray-50 text-gray-900'"
                        >
                            <p class="whitespace-pre-wrap break-words" x-text="msg.body"></p>
                            <p
                                class="mt-1 text-[10px] opacity-70"
                                x-text="formatTime(msg.created_at)"
                            ></p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="shrink-0 border-t border-gray-200 bg-white p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:p-4 md:pb-4">
                <form @submit.prevent="send()" class="flex items-end gap-2">
                    <textarea
                        x-model="body"
                        rows="1"
                        maxlength="2000"
                        :readonly="sending"
                        x-ref="messageInput"
                        @focus="onComposerFocus()"
                        class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border border-gray-300 px-3 py-2.5 text-base leading-snug focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 read-only:bg-gray-50 sm:text-sm"
                        placeholder="{{ __('vendor.support_message_placeholder') }}"
                        @keydown="onComposerKeydown($event)"
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="sending || !body.trim()"
                        aria-label="{{ __('vendor.support_send') }}"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white active:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50 touch-manipulation sm:h-auto sm:min-h-[44px] sm:w-auto sm:px-4"
                    >
                        <i class="fas fa-paper-plane text-base" :class="{ 'fa-spin': sending }"></i>
                        <span class="hidden sm:ml-2 sm:inline text-sm font-semibold">{{ __('vendor.support_send') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
