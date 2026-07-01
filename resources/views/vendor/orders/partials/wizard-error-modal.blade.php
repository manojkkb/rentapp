<template x-teleport="body">
    <div x-data="orderWizardErrorModal()"
         @wizard-show-error.window="show($event)"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-[95] flex items-end justify-center sm:items-center sm:p-4"
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="orderWizardErrorTitle"
         @keydown.escape.window="close()">
        <div class="fixed inset-0 bg-black/50"
             @click="close()"
             aria-hidden="true"></div>

        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl border border-red-200 bg-white shadow-2xl sm:rounded-2xl"
             @click.stop>
            <div class="border-b border-red-100 bg-gradient-to-r from-red-50 to-rose-50/80 px-4 py-4 sm:px-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                        <i class="fas fa-exclamation-triangle text-sm" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h3 id="orderWizardErrorTitle" class="text-base font-bold text-gray-900 sm:text-lg">
                            {{ __('vendor.error') }}
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-600">{{ __('vendor.order_wizard_error_subtitle') }}</p>
                    </div>
                    <button type="button"
                            @click="close()"
                            class="rounded-lg p-2 text-gray-500 hover:bg-white/80 hover:text-gray-700"
                            aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="max-h-[50vh] overflow-y-auto px-4 py-4 sm:px-5 sm:py-5">
                <template x-if="messages.length === 1">
                    <p class="text-sm leading-relaxed text-gray-800" x-text="messages[0]"></p>
                </template>
                <template x-if="messages.length > 1">
                    <ul class="list-disc space-y-1.5 pl-5 text-sm text-gray-800">
                        <template x-for="(message, index) in messages" :key="index">
                            <li x-text="message"></li>
                        </template>
                    </ul>
                </template>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 sm:px-5">
                <button type="button"
                        @click="close()"
                        class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-800 sm:min-h-[40px]">
                    {{ __('vendor.order_wizard_error_ok') }}
                </button>
            </div>
        </div>
    </div>
</template>
