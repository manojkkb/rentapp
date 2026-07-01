function normalizeWizardErrors(input) {
    if (input === null || input === undefined || input === '') {
        return [];
    }
    if (Array.isArray(input)) {
        return input.map(String).filter((m) => m.trim() !== '');
    }

    return [String(input)];
}

window.showWizardErrors = function showWizardErrors(messages) {
    const list = normalizeWizardErrors(messages);
    if (!list.length) {
        return;
    }
    window.dispatchEvent(new CustomEvent('wizard-show-error', { detail: { messages: list } }));
};

window.showWizardError = function showWizardError(message) {
    window.showWizardErrors([message]);
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('orderWizardErrorModal', () => ({
        open: false,
        messages: [],

        init() {
            this.$watch('open', (value) => {
                document.documentElement.classList.toggle('overflow-hidden', value);
            });
        },

        show(event) {
            const detail = event?.detail;
            const fromDetail = detail?.messages
                ?? (Array.isArray(detail) ? detail[0]?.messages : null);
            const list = normalizeWizardErrors(fromDetail ?? detail);
            if (!list.length) {
                return;
            }
            this.messages = list;
            this.open = true;
        },

        close() {
            this.open = false;
            this.messages = [];
        },
    }));
});

function extractLivewireValidationMessages(status, content) {
    if (status !== 422) {
        return [];
    }

    let payload = content;
    if (typeof content === 'string') {
        try {
            payload = JSON.parse(content);
        } catch {
            return [];
        }
    }

    const errors = payload?.errors ?? payload?.response?.errors ?? payload?.payload?.errors;
    if (!errors || typeof errors !== 'object') {
        return [];
    }

    return Object.values(errors).flat().filter(Boolean).map(String);
}

document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, content }) => {
            const messages = extractLivewireValidationMessages(status, content);
            if (messages.length) {
                window.showWizardErrors?.(messages);
            }
        });
    });

    Livewire.on('wizard-show-error', (data) => {
        const messages = data?.messages ?? data;
        window.showWizardErrors?.(messages);
    });
});
