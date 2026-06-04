<div class="max-h-96 overflow-y-auto">
    <template x-if="query.trim().length > 0 && query.trim().length < 2">
        <p class="px-4 py-6 text-sm text-gray-500 text-center" x-text="minHint"></p>
    </template>

    <template x-if="query.trim().length >= 2 && !loading && results.length === 0">
        <p class="px-4 py-6 text-sm text-gray-500 text-center" x-text="noResults"></p>
    </template>

    <template x-for="(group, groupIndex) in results" :key="group.key">
        <div class="border-b border-gray-100 last:border-b-0">
            <div class="px-4 py-2 bg-gray-50 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <i class="fas text-emerald-600 text-xs" :class="group.icon"></i>
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 truncate" x-text="group.label"></span>
                </div>
                <a :href="group.view_all_url"
                   class="text-xs font-medium text-emerald-600 hover:text-emerald-700 whitespace-nowrap"
                   x-text="viewAll"></a>
            </div>
            <ul>
                <template x-for="(item, itemIndex) in group.items" :key="group.key + '-' + itemIndex">
                    <li>
                        <a :href="item.url"
                           @click="closeSearch()"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-emerald-50 transition-colors"
                           :class="isActive(groupIndex, itemIndex) ? 'bg-emerald-50 ring-1 ring-inset ring-emerald-200' : ''">
                            <template x-if="item.image">
                                <img :src="item.image" alt="" class="h-9 w-9 rounded-lg object-cover border border-gray-200 shrink-0">
                            </template>
                            <template x-if="!item.image">
                                <div class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                    <i class="fas text-emerald-600 text-sm" :class="group.icon"></i>
                                </div>
                            </template>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.title"></p>
                                <p x-show="item.subtitle" class="text-xs text-gray-500 truncate" x-text="item.subtitle"></p>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 shrink-0"></i>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>
