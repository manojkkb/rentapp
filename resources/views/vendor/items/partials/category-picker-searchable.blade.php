{{--
    Searchable category field with inline create.
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
                showAddCategoryInline: false,
                newCategoryName: '',
                categoryInlineError: '',
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
                openAddCategoryInline() {
                    this.showAddCategoryInline = true;
                    this.categoryInlineError = '';
                    const q = (this.query || '').trim();
                    if (q && !this.categories.some((c) => c.name.toLowerCase() === q.toLowerCase())) {
                        this.newCategoryName = q;
                    }
                    this.$nextTick(() => this.$refs.newCategoryNameInput?.focus());
                },
                closeAddCategoryInline() {
                    this.showAddCategoryInline = false;
                    this.categoryInlineError = '';
                },
                async saveInlineCategory() {
                    const name = (this.newCategoryName || '').trim();
                    if (!name) {
                        this.categoryInlineError = this.msgNameRequired;
                        return;
                    }
                    if (this.categoryCreateSaving) {
                        return;
                    }
                    this.categoryInlineError = '';
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
                            this.categoryInlineError = data.message || errs[0] || this.msgCreateFailed;
                            return;
                        }
                        const cat = { id: data.category.id, name: data.category.name };
                        const rest = this.categories.filter((c) => String(c.id) !== String(cat.id));
                        this.categories = [cat, ...rest].sort((a, b) => a.name.localeCompare(b.name));
                        this.selectCategory(cat);
                        this.showAddCategoryInline = false;
                        this.newCategoryName = '';
                    } catch (e) {
                        this.categoryInlineError = this.msgCreateFailed;
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
        <span class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true">
            <i class="fas fa-search text-[10px]"></i>
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
               class="{{ $inputClass }} pl-7 pr-8 @error('category_id') border-red-500 @enderror"
               placeholder="{{ __('vendor.item_category_search_placeholder') }}">
        <button type="button"
                x-show="selectedId || (query && query.length)"
                x-cloak
                @click="clearCategory()"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                aria-label="{{ __('vendor.clear') }}">
            <i class="fas fa-times text-[10px]" aria-hidden="true"></i>
        </button>

        <ul id="item_category_listbox"
            x-show="dropdownOpen && filteredCategories.length"
            x-cloak
            role="listbox"
            class="absolute z-20 mt-0.5 max-h-48 w-full overflow-y-auto rounded-md border border-gray-200 bg-white py-0.5 shadow-lg">
            <template x-for="cat in filteredCategories" :key="cat.id">
                <li role="option"
                    :aria-selected="String(selectedId) === String(cat.id)"
                    @mousedown.prevent="selectCategory(cat)"
                    class="cursor-pointer px-2.5 py-1.5 text-sm text-gray-800 hover:bg-emerald-50"
                    :class="{ 'bg-emerald-50 font-medium text-emerald-900': String(selectedId) === String(cat.id) }"
                    x-text="cat.name"></li>
            </template>
        </ul>

        <p x-show="dropdownOpen && query.trim() && !filteredCategories.length"
           x-cloak
           class="absolute z-20 mt-0.5 w-full rounded-md border border-gray-200 bg-white px-2.5 py-2 text-xs text-gray-600 shadow-lg">
            {{ __('vendor.order_wizard_no_categories_match') }}
        </p>
    </div>

    <button type="button"
            @click="openAddCategoryInline()"
            class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800">
        <i class="fas fa-plus-circle text-[10px]" aria-hidden="true"></i>
        {{ __('vendor.order_wizard_add_category_inline') }}
    </button>

    <div x-show="showAddCategoryInline"
         x-cloak
         class="mt-2 rounded-md border border-emerald-200 bg-emerald-50/80 p-2.5">
        <div class="mb-1.5 flex items-center justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wide text-emerald-900">{{ __('vendor.order_wizard_new_category_heading') }}</span>
            <button type="button"
                    @click="closeAddCategoryInline()"
                    class="rounded p-0.5 text-gray-500 hover:bg-white/80"
                    aria-label="{{ __('vendor.cancel') }}">
                <i class="fas fa-times text-xs" aria-hidden="true"></i>
            </button>
        </div>
        <label for="item_new_category_name" class="mb-0.5 block text-[10px] font-medium text-gray-600">
            {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text"
               id="item_new_category_name"
               x-ref="newCategoryNameInput"
               x-model="newCategoryName"
               maxlength="255"
               class="{{ $inputClass }} mb-1.5">
        <p x-show="categoryInlineError"
           x-text="categoryInlineError"
           x-cloak
           class="mb-1.5 text-[11px] text-red-600"
           role="alert"></p>
        <button type="button"
                @click="saveInlineCategory()"
                :disabled="categoryCreateSaving"
                class="w-full rounded-md bg-emerald-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
            <span x-show="!categoryCreateSaving">{{ __('vendor.order_wizard_save_and_select_category') }}</span>
            <span x-show="categoryCreateSaving" x-cloak>{{ __('vendor.save') }}…</span>
        </button>
    </div>

    @error('category_id')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
    @if($categories->isEmpty())
        <p class="mt-0.5 text-[10px] text-orange-600">{{ __('vendor.no_categories_found') }}</p>
    @endif
</div>
