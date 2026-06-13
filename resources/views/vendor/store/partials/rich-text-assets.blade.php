@once
    @push('store-styles')
        <style>
            .rich-text-editor-wrap .ql-toolbar.ql-snow {
                border: none;
                border-bottom: 1px solid #e5e7eb;
                background: #f9fafb;
                border-radius: 0.5rem 0.5rem 0 0;
                padding: 0.5rem;
            }
            .rich-text-editor-wrap .ql-container.ql-snow {
                border: none;
                font-family: inherit;
                font-size: 0.875rem;
            }
            .rich-text-editor-wrap .ql-editor {
                min-height: var(--rich-text-min-height, 12rem);
                line-height: 1.6;
            }
            .rich-text-editor-wrap .ql-editor.ql-blank::before {
                color: #9ca3af;
                font-style: normal;
            }
            .rich-text-editor-wrap .ql-snow .ql-stroke { stroke: #6b7280; }
            .rich-text-editor-wrap .ql-snow .ql-fill { fill: #6b7280; }
            .rich-text-editor-wrap .ql-snow .ql-picker { color: #374151; }
            .rich-text-editor-wrap .ql-snow .ql-picker-options { border-radius: 0.5rem; }
            .rich-text-editor-wrap .ql-snow button:hover .ql-stroke,
            .rich-text-editor-wrap .ql-snow button.ql-active .ql-stroke { stroke: #059669; }
            .rich-text-editor-wrap .ql-snow button:hover .ql-fill,
            .rich-text-editor-wrap .ql-snow button.ql-active .ql-fill { fill: #059669; }
            @media (max-width: 640px) {
                .rich-text-editor-wrap .ql-toolbar.ql-snow {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.125rem;
                }
                .rich-text-editor-wrap .ql-toolbar.ql-snow .ql-formats {
                    margin-right: 0.25rem;
                }
                .rich-text-editor-wrap .ql-toolbar button,
                .rich-text-editor-wrap .ql-toolbar .ql-picker-label {
                    width: 2rem;
                    height: 2rem;
                    padding: 0.25rem;
                }
                .rich-text-editor-wrap .ql-editor {
                    min-height: 10rem;
                }
            }
        </style>
    @endpush
@endonce
