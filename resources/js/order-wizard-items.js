document.addEventListener('alpine:init', () => {
    window.Alpine.data('orderWizardItems', (raw) => {
        const p = raw || {};
        p.i18n = p.i18n || {};
        return {
        livewireWizard: !!p.livewireWizard,
        items: p.items ?? [],
                billingUnitsLabels: p.billingUnitsLabels,
                rentalPeriods: p.rentalPeriods,
                bookingDefaultUnitsByPriceType: p.bookingDefaultUnitsByPriceType,
                quickStoreUrl: p.quickStoreUrl,
                categoryStoreUrl: p.categoryStoreUrl,
                categories: p.categories,
                searchQuery: '',
                selectedCategory: '',
                showAddCategoryInline: false,
                newCategoryName: '',
                categoryInlineError: '',
                categoryCreateSaving: false,
                lineQty: {},
                lineUnits: {},
                lineMeta: {},
                showVariantModal: false,
                variantModalItem: null,
                variantModalPick: '',
                variantModalSelections: [],
                variantModalError: '',
                variantModalEditLineKey: null,
                variantModalMode: 'add',
                showAddItemModal: false,
                quickItemSaving: false,
                quickItemError: '',
                quickItem: { name: '', category_id: '', price: '', rental_period: 'per_day' },
                itemsStepError: '',
                showLineEditModal: false,
                lineEditKey: null,
                lineEditItem: null,
                lineEditName: '',
                lineEditQty: 1,
                lineEditBilling: '',
                lineEditPrice: '',
                lineEditRentalPeriod: 'per_day',
                lineEditUsesBilling: false,
                lineEditError: '',
                lineActionMenu: null,
                get hasSelectedItems() {
                    return Object.keys(this.lineQty).some((key) => (parseInt(String(this.lineQty[key]), 10) || 0) >= 1);
                },
                lineKey(itemId, variantId = null) {
                    return variantId ? `${itemId}_v${variantId}` : String(itemId);
                },
                findItem(itemId) {
                    return this.items.find((i) => String(i.id) === String(itemId)) || null;
                },
                findVariant(item, variantId) {
                    if (!item || !Array.isArray(item.variants)) return null;
                    return item.variants.find((v) => String(v.id) === String(variantId)) || null;
                },
                availableStockLabel(count) {
                    const n = parseInt(String(count), 10) || 0;
                    return `${n} ${p.i18n.available_stock || 'Available'}`;
                },
                cartQtyForItemVariant(itemId, variantId = null) {
                    let total = 0;
                    Object.keys(this.lineMeta || {}).forEach((key) => {
                        const meta = this.lineMeta[key];
                        if (String(meta?.item_id) !== String(itemId)) {
                            return;
                        }
                        const vid = meta?.item_variant_id ?? null;
                        if (variantId === null) {
                            if (vid) {
                                return;
                            }
                        } else if (String(vid) !== String(variantId)) {
                            return;
                        }
                        total += this.getQtyForKey(key);
                    });
                    return total;
                },
                itemAvailableStock(item) {
                    if (!item?.manage_stock) {
                        return 0;
                    }
                    const base = parseInt(String(item.stock), 10) || 0;
                    if (item.has_variants) {
                        let inCart = 0;
                        this.lineKeysForItem(item.id).forEach((key) => {
                            inCart += this.getQtyForKey(key);
                        });
                        return Math.max(0, base - inCart);
                    }
                    return Math.max(0, base - this.cartQtyForItemVariant(item.id, null));
                },
                variantAvailableStock(item, variant) {
                    if (!variant?.manage_stock) {
                        return parseInt(String(variant.stock), 10) || 0;
                    }
                    const base = parseInt(String(variant.stock), 10) || 0;
                    if (!item) {
                        return base;
                    }
                    return Math.max(0, base - this.cartQtyForItemVariant(item.id, variant.id));
                },
                itemStockLabel(item) {
                    if (!item?.manage_stock) return '';
                    return this.availableStockLabel(this.itemAvailableStock(item));
                },
                variantLineStockLabel(item, lineKey) {
                    const meta = this.lineMeta[lineKey];
                    const variant = this.findVariant(item, meta?.item_variant_id);
                    if (!variant?.manage_stock) return '';
                    return this.availableStockLabel(this.variantAvailableStock(item, variant));
                },
                lineKeysForItem(itemId) {
                    return Object.keys(this.lineMeta).filter((key) => String(this.lineMeta[key]?.item_id) === String(itemId));
                },
                isSimpleSelected(itemId) {
                    const key = this.lineKey(itemId);
                    return (parseInt(String(this.lineQty[key]), 10) || 0) >= 1;
                },
                hasVariantLines(itemId) {
                    return this.lineKeysForItem(itemId).some((key) => (parseInt(String(this.lineQty[key]), 10) || 0) >= 1);
                },
                getQtyForKey(lineKey) {
                    return parseInt(String(this.lineQty[lineKey]), 10) || 0;
                },
                variantOptionLabel(variant) {
                    let text = variant.label || variant.variant_code || '';
                    text += ' — ₹' + parseFloat(variant.price).toFixed(2);
                    if (variant.manage_stock) {
                        const avail = this.variantAvailableStock(this.variantModalItem, variant);
                        text += ' (' + this.availableStockLabel(avail) + ')';
                    }
                    return text;
                },
                variantSelectable(variant) {
                    if (!variant.is_available) return false;
                    if (variant.manage_stock && this.variantAvailableStock(this.variantModalItem, variant) < 1) return false;
                    return true;
                },
                itemPriceLabel(item) {
                    if (!item.has_variants) {
                        return parseFloat(item.price).toFixed(2);
                    }
                    if (item.price_min != null && item.price_max != null && item.price_min !== item.price_max) {
                        return parseFloat(item.price_min).toFixed(2) + ' – ' + parseFloat(item.price_max).toFixed(2);
                    }
                    return parseFloat(item.price_min ?? item.price ?? 0).toFixed(2);
                },
                collectLinesPayload() {
                    const out = {};
                    Object.keys(this.lineQty || {}).forEach((lineKey) => {
                        const qty = parseInt(String(this.lineQty[lineKey]), 10) || 0;
                        if (qty < 1) return;
                        const meta = this.lineMeta[lineKey] || {};
                        const item = this.findItem(meta.item_id);
                        const row = {
                            item_id: meta.item_id,
                            quantity: qty,
                            rental_period: meta.rental_period || item?.rental_period || 'per_day',
                            price: meta.price ?? item?.price ?? 0,
                        };
                        if (meta.item_variant_id) {
                            row.item_variant_id = meta.item_variant_id;
                        }
                        if (meta.uses_billing_units || item?.uses_billing_units) {
                            row.billing_units = this.lineUnits[lineKey];
                        }
                        out[lineKey] = row;
                    });
                    return out;
                },
                submitItemsStep(ev) {
                    if (!this.hasSelectedItems) {
                        this.itemsStepError = p.i18n.select_at_least_one_item;
                        if (typeof showToast === 'function') {
                            showToast(this.itemsStepError, 'error');
                        }
                        return;
                    }
                    this.itemsStepError = '';
                    if (p.livewireWizard) {
                        ev?.preventDefault?.();
                        this.$wire.saveItemsStep(this.collectLinesPayload());
                        return;
                    }
                    ev.target.submit();
                },
                init() {
                    const qty = {};
                    const units = {};
                    const meta = {};
                    Object.entries(p.initialLineMeta || {}).forEach(([lineKey, row]) => {
                        if (!row || typeof row !== 'object') return;
                        const key = String(row.line_key || lineKey);
                        meta[key] = { ...row, line_key: key };
                        qty[key] = parseInt(String(row.quantity ?? 0), 10) || 0;
                        const iu = row.billing_units;
                        units[key] = iu !== undefined && iu !== null && iu !== '' ? parseFloat(String(iu)) : null;
                    });
                    p.items.forEach((item) => {
                        const key = this.lineKey(item.id);
                        if (meta[key] === undefined && !item.has_variants) {
                            meta[key] = {
                                line_key: key,
                                item_id: item.id,
                                item_variant_id: null,
                                uses_billing_units: item.uses_billing_units,
                                rental_period: item.rental_period,
                            };
                            qty[key] = qty[key] ?? 0;
                        }
                        if (item.has_variants && qty[key] === undefined) {
                            qty[key] = 0;
                        }
                    });
                    this.lineMeta = meta;
                    this.lineQty = qty;
                    this.lineUnits = units;
                },
                selectedVariantLineKeys(itemId) {
                    return this.lineKeysForItem(itemId).filter((key) => this.getQtyForKey(key) >= 1);
                },
                variantQtyInOrder(item, variantId) {
                    const key = this.lineKey(item.id, variantId);
                    return this.getQtyForKey(key);
                },
                itemHasVariants(item) {
                    return !!item?.has_variants;
                },
                lineEditDisplayName(item, lineKey) {
                    const meta = this.lineMeta[lineKey];
                    if (meta?.variant_label) {
                        return item.name + ' — ' + meta.variant_label;
                    }
                    return item.name || '';
                },
                openLineEditModal(item, lineKey) {
                    this.closeLineActionMenu();
                    const key = String(lineKey);
                    const meta = this.lineMeta[key] || {};
                    this.lineEditError = '';
                    this.lineEditKey = key;
                    this.lineEditItem = item;
                    this.lineEditName = this.lineEditDisplayName(item, key);
                    this.lineEditQty = this.getQtyForKey(key) || 1;
                    this.lineEditRentalPeriod = this.lineRentalPeriodForKey(key, item);
                    this.lineEditUsesBilling = this.lineUsesBillingForKey(key, item);
                    const price = parseFloat(meta?.price ?? this.linePriceForKey(key, item));
                    this.lineEditPrice = Number.isFinite(price) ? String(price) : '';
                    if (this.lineEditUsesBilling) {
                        const bu = this.getLineBillingUnitsForKey(key, item);
                        this.lineEditBilling = Number.isFinite(bu) ? String(bu) : String(this.defaultBillingUnitsForKey(key, item));
                    } else {
                        this.lineEditBilling = '';
                    }
                    this.showLineEditModal = true;
                    document.documentElement.classList.add('overflow-hidden');
                },
                closeLineEditModal() {
                    this.showLineEditModal = false;
                    this.lineEditKey = null;
                    this.lineEditItem = null;
                    this.lineEditName = '';
                    this.lineEditQty = 1;
                    this.lineEditBilling = '';
                    this.lineEditPrice = '';
                    this.lineEditRentalPeriod = 'per_day';
                    this.lineEditUsesBilling = false;
                    this.lineEditError = '';
                    if (!this.showVariantModal && !this.showAddItemModal) {
                        document.documentElement.classList.remove('overflow-hidden');
                    }
                },
                rentalPeriodUsesBilling(period) {
                    return String(period || '') !== 'fixed';
                },
                lineRentalPeriodForKey(lineKey, item) {
                    const meta = this.lineMeta[lineKey];
                    const pt = meta?.rental_period || item?.rental_period || 'per_day';
                    if (pt === 'fixed' || this.rentalPeriodUsesBilling(pt)) {
                        return pt;
                    }
                    return 'per_day';
                },
                lineUsesBillingForKey(lineKey, item) {
                    return this.rentalPeriodUsesBilling(this.lineRentalPeriodForKey(lineKey, item));
                },
                onLineEditRentalChange() {
                    this.lineEditUsesBilling = this.rentalPeriodUsesBilling(this.lineEditRentalPeriod);
                    if (!this.lineEditUsesBilling) {
                        this.lineEditBilling = '';
                        return;
                    }
                    if (!this.lineEditBilling || this.lineEditBilling === '') {
                        this.lineEditBilling = String(this.defaultBillingUnitsForType(this.lineEditRentalPeriod));
                    }
                },
                lineEditBillingLabel() {
                    return this.billingUnitsLabels[this.lineEditRentalPeriod] || p.i18n.billing_units;
                },
                lineEditRentalPeriodLabel() {
                    return this.rentalPeriods[this.lineEditRentalPeriod] || this.lineEditRentalPeriod || '';
                },
                lineEditPreviewTotal() {
                    const price = parseFloat(String(this.lineEditPrice));
                    const qty = parseInt(String(this.lineEditQty), 10);
                    if (!Number.isFinite(price) || !Number.isFinite(qty) || qty < 1) {
                        return 0;
                    }
                    let total = price * qty;
                    if (this.rentalPeriodUsesBilling(this.lineEditRentalPeriod)) {
                        const bu = parseFloat(String(this.lineEditBilling));
                        if (Number.isFinite(bu) && bu >= 0.01) {
                            total *= bu;
                        }
                    }
                    return Math.round(total);
                },
                isItemInOrder(item) {
                    if (this.itemHasVariants(item)) {
                        return this.hasVariantLines(item.id);
                    }
                    return this.isSimpleSelected(item.id);
                },
                openLineActionMenu(event, config) {
                    const id = String(config.id || '');
                    if (this.lineActionMenu?.id === id) {
                        this.lineActionMenu = null;
                        return;
                    }
                    const btn = event.currentTarget;
                    const rect = btn.getBoundingClientRect();
                    const width = config.width || 168;
                    const itemCount = config.itemCount || 2;
                    let left = rect.right - width;
                    left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
                    let top = rect.bottom + 6;
                    const menuHeight = itemCount * 44 + 8;
                    if (top + menuHeight > window.innerHeight - 8) {
                        top = Math.max(8, rect.top - menuHeight - 6);
                    }
                    this.lineActionMenu = {
                        id,
                        type: config.type,
                        itemId: config.itemId,
                        lineKey: config.lineKey ?? null,
                        top,
                        left,
                        width,
                    };
                },
                closeLineActionMenu() {
                    this.lineActionMenu = null;
                },
                isLineActionMenuOpen(id) {
                    return this.lineActionMenu?.id === String(id);
                },
                lineActionMenuItem() {
                    if (!this.lineActionMenu?.itemId) {
                        return null;
                    }
                    return this.findItem(this.lineActionMenu.itemId);
                },
                runLineActionMenu(action) {
                    const menu = this.lineActionMenu;
                    if (!menu) {
                        return;
                    }
                    const item = this.findItem(menu.itemId);
                    if (!item) {
                        this.closeLineActionMenu();
                        return;
                    }
                    const lineKey = menu.lineKey;
                    this.closeLineActionMenu();
                    if (action === 'edit') {
                        this.openLineEditModal(item, lineKey || this.lineKey(item.id));
                    } else if (action === 'change') {
                        this.openVariantModal(item, lineKey);
                    } else if (action === 'remove') {
                        this.removeVariantLine(lineKey || this.lineKey(item.id));
                    }
                },
                saveLineEditModal() {
                    const item = this.lineEditItem;
                    const key = this.lineEditKey;
                    if (!item || !key) {
                        return;
                    }
                    const qty = parseInt(String(this.lineEditQty), 10);
                    if (!Number.isFinite(qty) || qty < 1) {
                        this.lineEditError = p.i18n.enter_quantity;
                        return;
                    }
                    const price = parseFloat(String(this.lineEditPrice));
                    if (!Number.isFinite(price) || price < 0) {
                        this.lineEditError = p.i18n.quick_item_price_invalid;
                        return;
                    }
                    const rentalPeriod = String(this.lineEditRentalPeriod || item.rental_period || 'per_day');
                    const usesBilling = this.rentalPeriodUsesBilling(rentalPeriod);
                    let billing = null;
                    if (usesBilling) {
                        billing = parseFloat(String(this.lineEditBilling));
                        if (!Number.isFinite(billing) || billing < 0.01) {
                            this.lineEditError = p.i18n.billing_units_required;
                            return;
                        }
                    }
                    this.itemsStepError = '';
                    const meta = { ...(this.lineMeta[key] || {}), line_key: key };
                    meta.rental_period = rentalPeriod;
                    meta.uses_billing_units = usesBilling;
                    meta.price = price;
                    this.lineMeta = { ...this.lineMeta, [key]: meta };
                    this.lineQty = { ...this.lineQty, [key]: qty };
                    if (usesBilling) {
                        this.lineUnits = { ...this.lineUnits, [key]: billing };
                    } else {
                        const nextUnits = { ...this.lineUnits };
                        delete nextUnits[key];
                        this.lineUnits = nextUnits;
                    }
                    this.closeLineEditModal();
                },
                variantInCartLabel(item, variantId) {
                    if (!item) {
                        return '';
                    }
                    const qty = this.variantQtyInOrder(item, variantId);
                    if (qty < 1) {
                        return '';
                    }
                    return p.i18n.variant_in_cart.replace(':qty', String(qty));
                },
                openVariantModal(item, editLineKey = null, mode = null) {
                    this.closeLineActionMenu();
                    this.variantModalError = '';
                    this.variantModalItem = item;
                    this.variantModalEditLineKey = editLineKey;
                    if (editLineKey) {
                        this.variantModalMode = 'change';
                        this.variantModalSelections = [];
                        this.variantModalPick = this.lineMeta[editLineKey]?.item_variant_id
                            ? String(this.lineMeta[editLineKey].item_variant_id)
                            : '';
                    } else if (mode === 'modify' || (mode !== 'add' && this.hasVariantLines(item.id))) {
                        this.variantModalMode = 'modify';
                        this.variantModalPick = '';
                        this.variantModalSelections = this.selectedVariantLineKeys(item.id)
                            .map((key) => String(this.lineMeta[key]?.item_variant_id || ''))
                            .filter((id) => id !== '');
                    } else {
                        this.variantModalMode = 'add';
                        this.variantModalSelections = [];
                        this.variantModalPick = '';
                    }
                    this.showVariantModal = true;
                    document.documentElement.classList.add('overflow-hidden');
                },
                closeVariantModal() {
                    this.showVariantModal = false;
                    this.variantModalItem = null;
                    this.variantModalPick = '';
                    this.variantModalSelections = [];
                    this.variantModalEditLineKey = null;
                    this.variantModalMode = 'add';
                    this.variantModalError = '';
                    document.documentElement.classList.remove('overflow-hidden');
                },
                isVariantModalChecked(variantId) {
                    return this.variantModalSelections.includes(String(variantId));
                },
                isVariantModalRowSelected(variant) {
                    const id = String(variant.id);
                    if (this.variantModalMode === 'change') {
                        return String(this.variantModalPick) === id;
                    }
                    return this.isVariantModalChecked(id);
                },
                handleVariantRowClick(variant) {
                    if (!this.variantSelectable(variant)) {
                        return;
                    }
                    const id = String(variant.id);
                    if (this.variantModalMode === 'change') {
                        this.variantModalPick = id;
                    } else {
                        this.toggleVariantModalSelection(id);
                    }
                    this.variantModalError = '';
                },
                toggleVariantModalSelection(variantId) {
                    const id = String(variantId);
                    if (this.isVariantModalChecked(id)) {
                        this.variantModalSelections = this.variantModalSelections.filter((v) => v !== id);
                    } else {
                        this.variantModalSelections = [...this.variantModalSelections, id];
                    }
                },
                variantModalSelectionCount() {
                    return this.variantModalSelections.length;
                },
                addVariantToOrder(item, variantId, incrementIfExists = true) {
                    const variant = this.findVariant(item, variantId);
                    if (!variant || !this.variantSelectable(variant)) {
                        return false;
                    }
                    const key = this.lineKey(item.id, variantId);
                    if (incrementIfExists && this.getQtyForKey(key) >= 1) {
                        this.incrementQtyForKey(key, item);
                        return true;
                    }
                    if (!incrementIfExists && this.getQtyForKey(key) >= 1) {
                        return true;
                    }
                    this.lineMeta = {
                        ...this.lineMeta,
                        [key]: {
                            line_key: key,
                            item_id: item.id,
                            item_variant_id: variantId,
                            variant_label: variant.label,
                            uses_billing_units: item.uses_billing_units,
                            rental_period: item.rental_period,
                            price: variant.price,
                        },
                    };
                    this.lineQty = { ...this.lineQty, [key]: 1 };
                    if (item.uses_billing_units) {
                        this.ensureBillingUnits(key, item);
                    }
                    return true;
                },
                removeVariantLine(lineKey) {
                    if (String(this.lineEditKey) === String(lineKey)) {
                        this.closeLineEditModal();
                    }
                    const nextQty = { ...this.lineQty };
                    const nextUnits = { ...this.lineUnits };
                    const nextMeta = { ...this.lineMeta };
                    delete nextQty[lineKey];
                    delete nextUnits[lineKey];
                    delete nextMeta[lineKey];
                    this.lineQty = nextQty;
                    this.lineUnits = nextUnits;
                    this.lineMeta = nextMeta;
                },
                confirmVariantModal(keepOpen = false) {
                    const item = this.variantModalItem;
                    if (!item) {
                        return;
                    }
                    this.itemsStepError = '';
                    const editKey = this.variantModalEditLineKey;

                    if (editKey) {
                        const variantId = parseInt(String(this.variantModalPick || ''), 10);
                        if (!variantId) {
                            this.variantModalError = p.i18n.select_variant;
                            return;
                        }
                        const variant = this.findVariant(item, variantId);
                        if (!variant || !this.variantSelectable(variant)) {
                            this.variantModalError = p.i18n.variant_invalid;
                            return;
                        }
                        const newKey = this.lineKey(item.id, variantId);
                        if (editKey !== newKey) {
                            const prevQty = this.getQtyForKey(editKey);
                            const prevUnits = this.lineUnits[editKey];
                            this.removeVariantLine(editKey);
                            this.lineMeta = {
                                ...this.lineMeta,
                                [newKey]: {
                                    line_key: newKey,
                                    item_id: item.id,
                                    item_variant_id: variantId,
                                    variant_label: variant.label,
                                    uses_billing_units: item.uses_billing_units,
                                    rental_period: item.rental_period,
                                    price: variant.price,
                                },
                            };
                            this.lineQty = { ...this.lineQty, [newKey]: Math.max(1, prevQty) };
                            if (item.uses_billing_units) {
                                this.lineUnits = {
                                    ...this.lineUnits,
                                    [newKey]: prevUnits ?? this.defaultBillingUnitsFor(item),
                                };
                            }
                        } else {
                            this.lineMeta = {
                                ...this.lineMeta,
                                [newKey]: {
                                    ...this.lineMeta[newKey],
                                    variant_label: variant.label,
                                    price: variant.price,
                                },
                            };
                        }
                        this.closeVariantModal();
                        return;
                    }

                    const selectedIds = this.variantModalSelections.length
                        ? [...this.variantModalSelections]
                        : (this.variantModalPick ? [String(this.variantModalPick)] : []);

                    if (!selectedIds.length) {
                        this.variantModalError = p.i18n.select_variant;
                        return;
                    }

                    if (this.variantModalMode === 'modify') {
                        const selectedIdSet = new Set(selectedIds.map(String));
                        this.selectedVariantLineKeys(item.id).forEach((lineKey) => {
                            const variantId = String(this.lineMeta[lineKey]?.item_variant_id || '');
                            if (variantId && !selectedIdSet.has(variantId)) {
                                this.removeVariantLine(lineKey);
                            }
                        });
                    }

                    let added = 0;
                    const incrementIfExists = this.variantModalMode === 'add';
                    selectedIds.forEach((idStr) => {
                        const variantId = parseInt(String(idStr), 10);
                        if (variantId && this.addVariantToOrder(item, variantId, incrementIfExists)) {
                            added++;
                        }
                    });

                    if (added === 0 && this.variantModalMode !== 'modify') {
                        this.variantModalError = p.i18n.variant_invalid;
                        return;
                    }

                    if (keepOpen) {
                        this.variantModalError = '';
                        if (this.variantModalMode === 'modify') {
                            this.variantModalSelections = this.selectedVariantLineKeys(item.id)
                                .map((key) => String(this.lineMeta[key]?.item_variant_id || ''))
                                .filter((id) => id !== '');
                        } else {
                            this.variantModalSelections = [];
                        }
                        this.variantModalPick = '';
                        return;
                    }
                    this.closeVariantModal();
                },
                    matchesFilter(item) {
                        const q = (this.searchQuery || '').toLowerCase().trim();
                        const cat = this.selectedCategory || '';
                        const matchesSearch = !q ||
                            item.name.toLowerCase().includes(q) ||
                            (item.category && item.category.name.toLowerCase().includes(q));
                        const matchesCategory = !cat || String(item.category_id ?? '') === String(cat);
                        return matchesSearch && matchesCategory;
                    },
                    get filteredItems() {
                        return this.items.filter((item) => this.matchesFilter(item));
                    },
                    openAddCategoryInline() {
                        this.showAddCategoryInline = true;
                        this.categoryInlineError = '';
                        this.$nextTick(() => document.getElementById('orderWizardNewCategoryName')?.focus());
                    },
                    closeAddCategoryInline() {
                        this.showAddCategoryInline = false;
                        this.categoryInlineError = '';
                    },
                    async saveQuickCategory() {
                        const name = (this.newCategoryName || '').trim();
                        if (!name) {
                            this.categoryInlineError = p.i18n.category_name_required;
                            return;
                        }
                        if (this.categoryCreateSaving) return;
                        this.categoryInlineError = '';
                        this.categoryCreateSaving = true;
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        try {
                            const res = await fetch(this.categoryStoreUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ name, is_active: 1 }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.success || !data.category) {
                                const errs = data.errors ? Object.values(data.errors).flat() : [];
                                this.categoryInlineError = data.message || errs[0] || p.i18n.category_create_failed;
                                return;
                            }
                            const cat = { id: data.category.id, name: data.category.name };
                            const rest = this.categories.filter((c) => String(c.id) !== String(cat.id));
                            this.categories = [cat, ...rest];
                            this.quickItem.category_id = String(cat.id);
                            this.showAddCategoryInline = false;
                            this.newCategoryName = '';
                        } catch (e) {
                            this.categoryInlineError = p.i18n.category_create_failed;
                        } finally {
                            this.categoryCreateSaving = false;
                        }
                    },
                    billingUnitsLabelForLine(item) {
                        const t = item.rental_period;
                        return this.billingUnitsLabels[t] || '';
                    },
                    billingUnitShortForKey(lineKey, item) {
                        const labels = {
                            per_minute: p.i18n.unit_minute,
                            per_hour: p.i18n.unit_hour,
                            per_day: p.i18n.unit_day,
                            per_week: p.i18n.unit_week,
                            per_month: p.i18n.unit_month,
                            per_year: p.i18n.unit_year,
                        };
                        return labels[this.lineRentalPeriodForKey(lineKey, item)] || '';
                    },
                    billingUnitShort(item) {
                        return this.billingUnitShortForKey(this.lineKey(item.id), item);
                    },
                    formatRupeeInt(amount) {
                        const n = parseInt(String(Math.round(Number(amount) || 0)), 10) || 0;
                        return n.toLocaleString('en-IN');
                    },
                    formatBillingUnitsDisplay(val) {
                        const n = parseFloat(String(val));
                        if (!Number.isFinite(n)) {
                            return '0';
                        }
                        return n.toFixed(2).replace(/\.?0+$/, '') || '0';
                    },
                    lineTotalForKey(lineKey, item) {
                        const meta = this.lineMeta[lineKey];
                        const price = parseFloat(meta?.price ?? item.price) || 0;
                        const qty = this.getQtyForKey(lineKey);
                        let total = price * qty;
                        if (this.lineUsesBillingForKey(lineKey, item)) {
                            total *= this.getLineBillingUnitsForKey(lineKey, item);
                        }
                        return Math.round(total);
                    },
                    linePriceForKey(lineKey, item) {
                        const meta = this.lineMeta[lineKey];
                        return parseFloat(meta?.price ?? item.price) || 0;
                    },
                    getQty(itemId) {
                        return this.getQtyForKey(this.lineKey(itemId));
                    },
                    defaultBillingUnitsForType(period) {
                        const map = this.bookingDefaultUnitsByPriceType || {};
                        const raw = map[period];
                        const v = raw !== undefined && raw !== null ? parseFloat(String(raw)) : NaN;
                        return Number.isFinite(v) ? v : 1;
                    },
                    defaultBillingUnitsFor(item) {
                        return this.defaultBillingUnitsForType(item.rental_period);
                    },
                    defaultBillingUnitsForKey(lineKey, item) {
                        return this.defaultBillingUnitsForType(this.lineRentalPeriodForKey(lineKey, item));
                    },
                    getLineBillingUnitsForKey(lineKey, item) {
                        const v = this.lineUnits[lineKey];
                        if (v !== undefined && v !== null && v !== '' && !Number.isNaN(parseFloat(String(v)))) {
                            return parseFloat(String(v));
                        }
                        return this.defaultBillingUnitsForKey(lineKey, item);
                    },
                    getLineBillingUnits(item) {
                        return this.getLineBillingUnitsForKey(this.lineKey(item.id), item);
                    },
                    ensureBillingUnits(lineKey, item) {
                        if (!this.lineUsesBillingForKey(lineKey, item)) {
                            return;
                        }
                        const cur = this.lineUnits[lineKey];
                        if (cur === undefined || cur === null || cur === '' || !Number.isFinite(parseFloat(String(cur)))) {
                            this.lineUnits = { ...this.lineUnits, [lineKey]: this.defaultBillingUnitsForKey(lineKey, item) };
                        }
                    },
                    addLine(item) {
                        this.itemsStepError = '';
                        const key = this.lineKey(item.id);
                        this.lineMeta = {
                            ...this.lineMeta,
                            [key]: {
                                line_key: key,
                                item_id: item.id,
                                item_variant_id: null,
                                uses_billing_units: item.uses_billing_units,
                                rental_period: item.rental_period,
                                price: item.price,
                            },
                        };
                        this.lineQty = { ...this.lineQty, [key]: 1 };
                        if (item.uses_billing_units) {
                            this.ensureBillingUnits(key, item);
                        }
                    },
                    incrementQtyForKey(lineKey, item) {
                        const next = this.getQtyForKey(lineKey) + 1;
                        if (next >= 1) this.itemsStepError = '';
                        this.lineQty = { ...this.lineQty, [lineKey]: next };
                        if (this.lineUsesBillingForKey(lineKey, item)) {
                            this.ensureBillingUnits(lineKey, item);
                        }
                    },
                    decrementQtyForKey(lineKey, item) {
                        const current = this.getQtyForKey(lineKey);
                        if (current > 1) {
                            this.lineQty = { ...this.lineQty, [lineKey]: current - 1 };
                        } else if (current === 1) {
                            const nextQty = { ...this.lineQty };
                            const nextUnits = { ...this.lineUnits };
                            const nextMeta = { ...this.lineMeta };
                            delete nextQty[lineKey];
                            delete nextUnits[lineKey];
                            delete nextMeta[lineKey];
                            this.lineQty = nextQty;
                            this.lineUnits = nextUnits;
                            this.lineMeta = nextMeta;
                        }
                    },
                    incrementQty(item) {
                        this.incrementQtyForKey(this.lineKey(item.id), item);
                    },
                    decrementQty(item) {
                        this.decrementQtyForKey(this.lineKey(item.id), item);
                    },
                    incrementBillingUnits(item) {
                        const key = this.lineKey(item.id);
                        const cur = this.getLineBillingUnitsForKey(key, item);
                        const v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 + 100) / 100;
                        this.lineUnits = { ...this.lineUnits, [key]: v };
                    },
                    decrementBillingUnits(item) {
                        const key = this.lineKey(item.id);
                        const cur = this.getLineBillingUnitsForKey(key, item);
                        if (!Number.isFinite(cur) || cur <= 0.011) return;
                        let v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 - 100) / 100;
                        if (!Number.isFinite(v) || v < 0.01) v = 0.01;
                        this.lineUnits = { ...this.lineUnits, [key]: v };
                    },
                    incrementBillingUnitsForKey(lineKey, item) {
                        const cur = this.getLineBillingUnitsForKey(lineKey, item);
                        const v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 + 100) / 100;
                        this.lineUnits = { ...this.lineUnits, [lineKey]: v };
                    },
                    decrementBillingUnitsForKey(lineKey, item) {
                        const cur = this.getLineBillingUnitsForKey(lineKey, item);
                        if (!Number.isFinite(cur) || cur <= 0.011) return;
                        let v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 - 100) / 100;
                        if (!Number.isFinite(v) || v < 0.01) v = 0.01;
                        this.lineUnits = { ...this.lineUnits, [lineKey]: v };
                    },
                    onBillingUnitsBlur(item, ev) {
                        const key = this.lineKey(item.id);
                        let v = parseFloat(ev.target.value);
                        if (!Number.isFinite(v) || v < 0.01) {
                            v = this.defaultBillingUnitsFor(item);
                            ev.target.value = String(v);
                        }
                        this.lineUnits = { ...this.lineUnits, [key]: v };
                    },
                    onBillingUnitsInput(item, ev) {
                        const key = this.lineKey(item.id);
                        const v = parseFloat(ev.target.value) || this.defaultBillingUnitsFor(item);
                        this.lineUnits = { ...this.lineUnits, [key]: v };
                    },
                    onBillingUnitsInputForKey(lineKey, item, ev) {
                        const v = parseFloat(ev.target.value) || this.defaultBillingUnitsFor(item);
                        this.lineUnits = { ...this.lineUnits, [lineKey]: v };
                    },
                    onBillingUnitsBlurForKey(lineKey, item, ev) {
                        let v = parseFloat(ev.target.value);
                        if (!Number.isFinite(v) || v < 0.01) {
                            v = this.defaultBillingUnitsFor(item);
                            ev.target.value = String(v);
                        }
                        this.lineUnits = { ...this.lineUnits, [lineKey]: v };
                    },
                    openAddItemModal() {
                        this.quickItemError = '';
                        this.showAddCategoryInline = false;
                        this.newCategoryName = '';
                        this.categoryInlineError = '';
                        this.quickItem = { name: '', category_id: '', price: '', rental_period: 'per_day' };
                        this.showAddItemModal = true;
                        document.documentElement.classList.add('overflow-hidden');
                        this.$nextTick(() => document.getElementById('orderWizardQuickItemName')?.focus());
                    },
                    closeAddItemModal() {
                        if (this.quickItemSaving) return;
                        this.showAddItemModal = false;
                        document.documentElement.classList.remove('overflow-hidden');
                    },
                    async saveQuickItem() {
                        if (this.quickItemSaving) return;
                        this.quickItemError = '';
                        const name = (this.quickItem.name || '').trim();
                        const categoryId = this.quickItem.category_id;
                        const price = parseFloat(String(this.quickItem.price));
                        if (!name || !categoryId) {
                            this.quickItemError = p.i18n.quick_item_required;
                            return;
                        }
                        if (!Number.isFinite(price) || price < 0) {
                            this.quickItemError = p.i18n.quick_item_price_invalid;
                            return;
                        }
                        this.quickItemSaving = true;
                        try {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const res = await fetch(this.quickStoreUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    name,
                                    category_id: categoryId,
                                    price,
                                    rental_period: this.quickItem.rental_period || 'per_day',
                                }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.success) {
                                const errs = data.errors ? Object.values(data.errors).flat() : [];
                                this.quickItemError = data.message || errs[0] || p.i18n.item_create_failed;
                                return;
                            }
                            const item = data.item;
                            if (!this.items.some((i) => i.id === item.id)) {
                                this.items = [...this.items, item];
                                if (!item.has_variants) {
                                    const key = this.lineKey(item.id);
                                    this.lineMeta = { ...this.lineMeta, [key]: { line_key: key, item_id: item.id, item_variant_id: null } };
                                    this.lineQty = { ...this.lineQty, [key]: 0 };
                                    this.lineUnits = { ...this.lineUnits, [key]: null };
                                }
                            }
                            if (item.has_variants) {
                                this.openVariantModal(item);
                            } else {
                                this.addLine(item);
                            }
                            this.searchQuery = '';
                            this.selectedCategory = '';
                            this.showAddItemModal = false;
                            document.documentElement.classList.remove('overflow-hidden');
                        } catch (e) {
                            this.quickItemError = p.i18n.item_create_failed;
                        } finally {
                            this.quickItemSaving = false;
                        }
                    },
        };
    });

    /** Legacy alias kept for create-items full-page load (inline script there). */
});
