{{--
    Searchable category field with modal create.
    Requires: $categories, $selectedCategoryId, $inputClass ($fc), $labelClass ($fl), $hintClass ($fh)
--}}
@php
    $pickerConfig = [
        'categories' => $categories
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->values()
            ->all(),
        'categoryStoreUrl' => route('vendor.categories.store'),
        'categoryCsrf' => csrf_token(),
        'selectedId' => (string) old('category_id', $selectedCategoryId ?? ''),
        'msgNameRequired' => __('vendor.order_wizard_category_name_required'),
        'msgCreateFailed' => __('vendor.order_wizard_category_create_failed'),
    ];
@endphp

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemCategoryPicker', (config) => ({
            categories: config.categories || [],
            categoryStoreUrl: config.categoryStoreUrl,
            categoryCsrf: config.categoryCsrf,
            selectedId: config.selectedId || '',
            msgNameRequired: config.msgNameRequired || '',
            msgCreateFailed: config.msgCreateFailed || '',
            query: '',
            dropdownOpen: false,
            showAddCategoryModal: false,
            newCategoryName: '',
            categoryModalError: '',
            categoryCreateSaving: false,
            init() {
                if (this.selectedId) {
                    const c = this.categories.find((x) => String(x.id) === String(this.selectedId));
                    if (c) {
                        this.query = c.name;
                    }
                }
            },
            get filteredCategories() {
                const q = (this.query || '').trim().toLowerCase();
                if (!q) {
                    return this.categories;
                }
                return this.categories.filter((c) => c.name.toLowerCase().includes(q));
            },
            openDropdown() {
                this.dropdownOpen = true;
            },
            closeDropdown() {
                this.dropdownOpen = false;
            },
            onQueryInput() {
                this.dropdownOpen = true;
                const sel = this.categories.find((c) => String(c.id) === String(this.selectedId));
                if (sel && (this.query || '').trim() !== sel.name) {
                    this.selectedId = '';
                }
            },
            selectCategory(cat) {
                this.selectedId = String(cat.id);
                this.query = cat.name;
                this.closeDropdown();
            },
            clearCategory() {
                this.selectedId = '';
                this.query = '';
                this.closeDropdown();
            },
            openAddCategoryModal() {
                this.showAddCategoryModal = true;
                this.categoryModalError = '';
                const q = (this.query || '').trim();
                if (q && !this.categories.some((c) => c.name.toLowerCase() === q.toLowerCase())) {
                    this.newCategoryName = q;
                }
                this.$nextTick(() => this.$refs.newCategoryNameInput?.focus());
            },
            closeAddCategoryModal() {
                this.showAddCategoryModal = false;
                this.categoryModalError = '';
            },
            async saveNewCategory() {
                const name = (this.newCategoryName || '').trim();
                if (!name) {
                    this.categoryModalError = this.msgNameRequired;
                    return;
                }
                if (this.categoryCreateSaving) {
                    return;
                }
                this.categoryModalError = '';
                this.categoryCreateSaving = true;
                try {
                    const res = await fetch(this.categoryStoreUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.categoryCsrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ name, is_active: 1 }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success || !data.category) {
                        const errs = data.errors ? Object.values(data.errors).flat() : [];
                        this.categoryModalError = data.message || errs[0] || this.msgCreateFailed;
                        return;
                    }
                    const cat = { id: data.category.id, name: data.category.name };
                    const rest = this.categories.filter((c) => String(c.id) !== String(cat.id));
                    this.categories = [cat, ...rest].sort((a, b) => a.name.localeCompare(b.name));
                    this.selectCategory(cat);
                    this.closeAddCategoryModal();
                    this.newCategoryName = '';
                } catch (e) {
                    this.categoryModalError = this.msgCreateFailed;
                } finally {
                    this.categoryCreateSaving = false;
                }
            },
        }));
    });
</script>
@endonce

<div class="sm:col-span-2"
     x-data="itemCategoryPicker(@js($pickerConfig))"
     @click.outside="closeDropdown()">
    <label for="item_category_search" class="{{ $labelClass }}">{{ __('vendor.category') }} <span class="text-red-500">*</span></label>
    <p class="{{ $hintClass }}">{{ __('vendor.field_hint_category') }}</p>

    <input type="hidden" name="category_id" :value="selectedId" required>

    <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true">
            <i class="fas fa-search text-xs"></i>
        </span>
        <input type="text"
               id="item_category_search"
               x-model="query"
               @focus="openDropdown()"
               @input="onQueryInput()"
               @keydown.escape.prevent="closeDropdown()"
               autocomplete="off"
               role="combobox"
               :aria-expanded="dropdownOpen ? 'true' : 'false'"
               aria-controls="item_category_listbox"
               aria-autocomplete="list"
               class="{{ $inputClass }} pl-10 pr-10 @error('category_id') border-red-500 @enderror"
               placeholder="{{ __('vendor.item_category_search_placeholder') }}">
        <button type="button"
                x-show="selectedId || (query && query.length)"
                x-cloak
                @click="clearCategory()"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                aria-label="{{ __('vendor.clear') }}">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>

        <ul id="item_category_listbox"
            x-show="dropdownOpen && filteredCategories.length"
            x-cloak
            role="listbox"
            class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
            <template x-for="cat in filteredCategories" :key="cat.id">
                <li role="option"
                    :aria-selected="String(selectedId) === String(cat.id)"
                    @mousedown.prevent="selectCategory(cat)"
                    class="cursor-pointer px-3 py-2.5 text-sm text-gray-800 hover:bg-emerald-50"
                    :class="{ 'bg-emerald-50 font-medium text-emerald-900': String(selectedId) === String(cat.id) }"
                    x-text="cat.name"></li>
            </template>
        </ul>

        <p x-show="dropdownOpen && query.trim() && !filteredCategories.length"
           x-cloak
           class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-600 shadow-lg">
            {{ __('vendor.order_wizard_no_categories_match') }}
        </p>
    </div>

    <button type="button"
            @click="openAddCategoryModal()"
            class="mt-2 inline-flex min-h-[36px] items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800">
        <i class="fas fa-plus-circle text-sm" aria-hidden="true"></i>
        {{ __('vendor.order_wizard_add_category_inline') }}
    </button>

    @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    @if($categories->isEmpty())
        <p class="mt-1 text-xs text-orange-600">{{ __('vendor.no_categories_found') }}</p>
    @endif

    {{-- Add category modal --}}
    <template x-teleport="body">
        <div x-show="showAddCategoryModal"
             x-cloak
             class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="item-new-category-title"
             @keydown.escape.window="closeAddCategoryModal()">
            <div class="fixed inset-0 bg-black/50"
                 @click="closeAddCategoryModal()"
                 aria-hidden="true"
                 x-show="showAddCategoryModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
                 x-show="showAddCategoryModal"
                 @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/40 px-4 py-4 sm:px-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                                <i class="fas fa-folder-plus text-sm" aria-hidden="true"></i>
                            </span>
                            <div>
                                <h3 id="item-new-category-title" class="text-base font-bold text-gray-900 sm:text-lg">
                                    {{ __('vendor.order_wizard_new_category_heading') }}
                                </h3>
                                <p class="text-xs text-gray-500">{{ __('vendor.order_wizard_add_category_modal_hint') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="closeAddCategoryModal()"
                                class="rounded-xl p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                aria-label="{{ __('vendor.cancel') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <label for="item_new_category_name" class="mb-1 block text-sm font-medium text-gray-800">
                        {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="item_new_category_name"
                           x-ref="newCategoryNameInput"
                           x-model="newCategoryName"
                           maxlength="255"
                           @keydown.enter.prevent="saveNewCategory()"
                           class="{{ $inputClass }}"
                           placeholder="{{ __('vendor.category_name_placeholder') ?? __('vendor.category_name') }}">
                    <p x-show="categoryModalError"
                       x-text="categoryModalError"
                       x-cloak
                       class="mt-2 text-xs font-medium text-red-600"
                       role="alert"></p>

                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button"
                                @click="closeAddCategoryModal()"
                                class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                            {{ __('vendor.cancel') }}
                        </button>
                        <button type="button"
                                @click="saveNewCategory()"
                                :disabled="categoryCreateSaving"
                                class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60 sm:min-h-[40px]">
                            <span x-show="!categoryCreateSaving">
                                <i class="fas fa-check text-xs" aria-hidden="true"></i>
                                {{ __('vendor.order_wizard_save_and_select_category') }}
                            </span>
                            <span x-show="categoryCreateSaving" x-cloak>
                                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                                {{ __('vendor.save') }}…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
