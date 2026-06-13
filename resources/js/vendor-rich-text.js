import Quill from 'quill';
import 'quill/dist/quill.snow.css';

window.Quill = Quill;

/**
 * Online Store — Quill rich text fields (Pages & Legal).
 */

function initRichTextEditors(root = document) {
    if (typeof window.Quill === 'undefined') {
        return;
    }

    root.querySelectorAll('[data-rich-text]:not([data-rich-text-ready])').forEach((wrapper) => {
        const textarea = wrapper.querySelector('textarea');
        const editorEl = wrapper.querySelector('.rich-text-editor');

        if (!textarea || !editorEl) {
            return;
        }

        const quill = new window.Quill(editorEl, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'blockquote'],
                    ['clean'],
                ],
            },
            placeholder: editorEl.dataset.placeholder || '',
        });

        if (textarea.value) {
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        }

        const sync = () => {
            const html = quill.root.innerHTML;
            textarea.value = html === '<p><br></p>' ? '' : html;
        };

        quill.on('text-change', sync);

        const form = wrapper.closest('form');
        form?.addEventListener('submit', sync);

        wrapper.dataset.richTextReady = '1';
    });
}

function bootRichTextEditors() {
    initRichTextEditors(document);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootRichTextEditors);
} else {
    bootRichTextEditors();
}

document.addEventListener('livewire:navigated', bootRichTextEditors);
