document.addEventListener('alpine:init', () => {
    window.Alpine.data('itemVariantForm', (initial) => ({
        hasVariants: Boolean(initial?.hasVariants),
        attributes: Array.isArray(initial?.attributes) ? initial.attributes : [],
        variants: Array.isArray(initial?.variants) ? initial.variants : [],
        itemManageStock: Boolean(initial?.itemManageStock ?? true),
        itemIsAvailable: Boolean(initial?.itemIsAvailable ?? true),
        itemIsActive: Boolean(initial?.itemIsActive ?? true),
        newAttributeName: '',
        presetAttributes: ['Size', 'Color', 'Capacity', 'Material'],

        init() {
            this.normalizeVariantAttributeKeys();
            this.syncVariantVisibility();
            this.$watch('itemManageStock', () => this.syncVariantVisibility());
            this.$watch('itemIsAvailable', () => this.syncVariantVisibility());
            this.$watch('itemIsActive', () => this.syncVariantVisibility());
        },

        syncVariantVisibility() {
            const manageStock = Boolean(this.itemManageStock);
            const isAvailable = Boolean(this.itemIsAvailable);
            const isActive = Boolean(this.itemIsActive);

            this.variants.forEach((variant) => {
                variant.manage_stock = manageStock;
                variant.is_available = isAvailable;
                variant.is_active = isActive;
            });
        },

        setHasVariants(enabled) {
            this.hasVariants = enabled;
            if (enabled && this.attributes.length > 0 && this.variants.length === 0) {
                this.addVariantRow();
            }
        },

        slugFromName(name) {
            return String(name || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '_')
                .replace(/[^\w]+/g, '')
                .replace(/^_+|_+$/g, '') || 'attribute';
        },

        addPreset(name) {
            this.addAttribute(name);
        },

        addAttribute(name) {
            const label = String(name || this.newAttributeName || '').trim();
            if (!label) {
                return;
            }

            const slug = this.slugFromName(label);
            const exists = this.attributes.some(
                (attr) => attr.slug === slug || String(attr.name).toLowerCase() === label.toLowerCase()
            );
            if (exists) {
                this.newAttributeName = '';
                return;
            }

            this.attributes.push({
                id: null,
                name: label,
                slug,
                sort_order: this.attributes.length,
            });
            this.newAttributeName = '';

            this.variants.forEach((variant) => {
                if (!variant.attributes || typeof variant.attributes !== 'object') {
                    variant.attributes = {};
                }
                if (variant.attributes[slug] === undefined) {
                    variant.attributes[slug] = '';
                }
            });

            if (this.variants.length === 0) {
                this.addVariantRow();
            }
        },

        removeAttribute(index) {
            const attr = this.attributes[index];
            if (!attr) {
                return;
            }

            this.attributes.splice(index, 1);
            this.attributes.forEach((row, i) => {
                row.sort_order = i;
            });

            this.variants.forEach((variant) => {
                if (variant.attributes && Object.prototype.hasOwnProperty.call(variant.attributes, attr.slug)) {
                    delete variant.attributes[attr.slug];
                }
            });
        },

        addVariantRow() {
            const attributes = {};
            this.attributes.forEach((attr) => {
                attributes[attr.slug] = '';
            });

            const templatePrice = this.variants.length > 0 ? this.variants[0].price : '';

            this.variants.push({
                id: null,
                variant_code: null,
                attributes,
                price: templatePrice,
                stock: 1,
                damaged_stock: 0,
                maintenance_stock: 0,
                manage_stock: Boolean(this.itemManageStock),
                is_available: Boolean(this.itemIsAvailable),
                is_active: Boolean(this.itemIsActive),
            });
        },

        removeVariantRow(index) {
            if (this.variants.length <= 1) {
                return;
            }
            this.variants.splice(index, 1);
        },

        normalizeVariantAttributeKeys() {
            this.variants.forEach((variant) => {
                if (!variant.attributes || typeof variant.attributes !== 'object') {
                    variant.attributes = {};
                }
                this.attributes.forEach((attr) => {
                    if (variant.attributes[attr.slug] === undefined) {
                        variant.attributes[attr.slug] = '';
                    }
                });
            });
        },
    }));
});
