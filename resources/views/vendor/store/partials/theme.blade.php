@php
    $palette = $resolvedTheme['palette'] ?? [];
    $savedCustom = old('theme_colors', $store->theme_colors ?? []);
    if (is_array($savedCustom)) {
        foreach ($savedCustom as $k => $v) {
            if (is_string($v) && $v !== '') {
                $palette[$k] = $v;
            }
        }
    }
    $initialPalette = [];
    foreach ($paletteKeys as $key) {
        $initialPalette[$key] = $palette[$key] ?? '#059669';
    }
@endphp
<form action="{{ route('vendor.store.theme.update') }}" method="POST" enctype="multipart/form-data"
      x-data="{
          submitting: false,
          accent: @js(old('theme_accent_color', $store->theme_accent_color ?? '#059669')),
          template: @js(old('theme_template', $store->theme_template ?? 'classic')),
          mode: @js(old('theme_mode', $store->theme_mode ?? 'light')),
          font: @js(old('theme_font', $store->theme_font ?? 'inter')),
          fonts: @js($googleFonts),
          presets: @js($palettePresetsByTemplate),
          templateAccents: @js($templateDefaultAccents),
          palette: @js($initialPalette),
          openGroup: 'basic',
          fontStylesheet() {
              const hit = this.fonts.find(f => f.key === this.font);
              return hit ? hit.url : (this.fonts[0]?.url || '');
          },
          fontCss() {
              const hit = this.fonts.find(f => f.key === this.font);
              return hit ? hit.css : 'Inter, sans-serif';
          },
          preset() {
              const modes = this.presets[this.template] || this.presets.classic;
              return { ...(modes[this.mode] || modes.light) };
          },
          tintAccent(p) {
              p.accent = this.accent;
              p.primary = this.accent;
              p.btn_primary_bg = this.accent;
              p.input_focus_border = this.accent;
              if (this.mode === 'gradient') {
                  p.header_bg = this.accent;
              }
              return p;
          },
          applyPreset() {
              if (this.mode === 'custom') return;
              this.accent = this.templateAccents[this.template] || this.accent;
              this.palette = this.tintAccent(this.preset());
          },
          previewHeader() {
              return this.mode === 'gradient'
                  ? 'linear-gradient(135deg, ' + this.accent + ', ' + this.palette.body_bg + ')'
                  : this.palette.header_bg;
          }
      }"
      x-init="applyPreset()"
      @submit="submitting = true"
      class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_theme_help') }}</p>

    <div>
        <label class="mb-3 block text-sm font-semibold text-gray-800">{{ __('vendor.store_theme_template') }}</label>
        <div class="grid grid-cols-1 gap-3 min-[480px]:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach($themeTemplates as $template)
                <label class="cursor-pointer rounded-xl border-2 p-3 transition hover:border-emerald-300 sm:p-4"
                       :class="template === @js($template) ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'">
                    <input type="radio" name="theme_template" value="{{ $template }}" class="sr-only"
                           x-model="template" @change="applyPreset()">
                    <div class="mb-2 h-14 overflow-hidden rounded-lg ring-1 ring-black/5 sm:h-16"
                         style="background: {{ $templatePreviewGradients[$template] ?? $templatePreviewGradients['classic'] }}"></div>
                    <p class="text-sm font-semibold capitalize text-gray-900">{{ __('vendor.store_theme_'.$template) }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('vendor.store_theme_'.$template.'_desc') }}</p>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="mb-3 block text-sm font-semibold text-gray-800">{{ __('vendor.store_theme_mode') }}</label>
        <div class="grid grid-cols-1 gap-3 min-[400px]:grid-cols-2 lg:grid-cols-4">
            @foreach($themeModes as $themeMode)
                <label class="cursor-pointer rounded-xl border-2 p-3 transition hover:border-emerald-300"
                       :class="mode === @js($themeMode) ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'">
                    <input type="radio" name="theme_mode" value="{{ $themeMode }}" class="sr-only"
                           x-model="mode" @change="applyPreset()">
                    <p class="text-sm font-semibold text-gray-900">{{ __('vendor.store_theme_mode_'.$themeMode) }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('vendor.store_theme_mode_'.$themeMode.'_desc') }}</p>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="mb-3 block text-sm font-semibold text-gray-800">{{ __('vendor.store_theme_font') }}</label>
        <p class="mb-3 text-xs text-gray-500">{{ __('vendor.store_theme_font_help') }}</p>
        <div class="mb-3 hidden gap-2 sm:flex">
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.store_font_category_sans') }}</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.store_font_category_serif') }}</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.store_font_category_display') }}</span>
        </div>
        <div class="grid grid-cols-1 gap-2 min-[480px]:grid-cols-2 lg:grid-cols-3">
            @foreach($googleFonts as $fontOption)
                <label class="cursor-pointer rounded-xl border-2 p-3 transition hover:border-emerald-300"
                       :class="font === @js($fontOption['key']) ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'"
                       :style="'font-family: ' + (@js($fontOption['css']))">
                    <input type="radio" name="theme_font" value="{{ $fontOption['key'] }}" class="sr-only"
                           x-model="font">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-semibold text-gray-900">{{ $fontOption['label'] }}</span>
                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-500">
                            {{ __('vendor.store_font_category_'.$fontOption['category']) }}
                        </span>
                    </div>
                    <p class="mt-2 text-lg font-semibold leading-tight text-gray-800">{{ __('vendor.store_font_preview') }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $vendor->name }}</p>
                </label>
            @endforeach
        </div>
    </div>

    <div class="max-w-sm">
        <label for="theme_accent_color" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_accent_color') }}</label>
        <div class="flex items-center gap-2">
            <input type="color" id="theme_accent_color" name="theme_accent_color" x-model="accent"
                   @input="applyPreset()"
                   class="h-10 w-14 cursor-pointer rounded border border-gray-300 p-1">
            <input type="text" x-model="accent" readonly class="h-10 flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700">
        </div>
        <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_accent_color_help') }}</p>
    </div>

    <div x-show="mode === 'custom'" x-cloak class="space-y-3">
        <p class="text-sm font-semibold text-gray-800">{{ __('vendor.store_custom_palette') }}</p>
        <p class="text-xs text-gray-500">{{ __('vendor.store_custom_palette_help') }}</p>

        @foreach($paletteGroups as $groupKey => $keys)
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <button type="button"
                        @click="openGroup = openGroup === @js($groupKey) ? '' : @js($groupKey)"
                        class="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left text-sm font-semibold text-gray-800 hover:bg-gray-100">
                    {{ __('vendor.store_palette_group_'.$groupKey) }}
                    <i class="fas fa-chevron-down text-xs transition" :class="openGroup === @js($groupKey) ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroup === @js($groupKey)" class="grid gap-3 border-t border-gray-200 p-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($keys as $key)
                        <div>
                            <label for="theme_color_{{ $key }}" class="mb-1 block text-xs font-semibold text-gray-700">
                                {{ $paletteLabels[$key] ?? $key }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="theme_color_{{ $key }}"
                                       name="theme_colors[{{ $key }}]" x-model="palette.{{ $key }}"
                                       class="h-9 w-12 cursor-pointer rounded border border-gray-300 p-0.5">
                                <input type="text" x-model="palette.{{ $key }}"
                                       class="h-9 flex-1 rounded-lg border border-gray-300 px-2 text-xs text-gray-700">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="reset_theme_colors" value="1" class="rounded border-gray-300 text-emerald-600">
        {{ __('vendor.store_reset_theme_colors') }}
    </label>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.store_preview') }}</p>
        <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm" :style="'background:' + palette.body_bg + '; font-family:' + fontCss()">
            <div class="px-4 py-3 text-sm font-bold" :style="'background:' + previewHeader() + '; color:' + palette.nav_text">
                {{ $vendor->name }}
                <span class="ml-3 text-xs font-medium opacity-80" :style="'color:' + palette.nav_hover">{{ __('vendor.store_nav_shop') }}</span>
            </div>
            <div class="p-4" :style="'background:' + palette.card_bg + '; color:' + palette.text">
                <p class="text-sm font-semibold" :style="'color:' + palette.heading">{{ __('vendor.store_public_catalog') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold"
                            :style="'background:' + palette.btn_primary_bg + '; color:' + palette.btn_primary_text">
                        {{ __('vendor.store_add_to_cart') }}
                    </button>
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold"
                            :style="'background:' + palette.btn_secondary_bg + '; color:' + palette.btn_secondary_text">
                        {{ __('vendor.store_preview_secondary_btn') }}
                    </button>
                </div>
                <a href="#" class="mt-2 block text-sm font-medium" :style="'color:' + palette.link">{{ __('vendor.store_nav_shop') }}</a>
                <input type="text" readonly placeholder="Email" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm"
                       :style="'background:' + palette.input_bg + '; border-color:' + palette.input_border + '; color:' + palette.text">
                <div class="mt-2 flex flex-wrap gap-2 text-xs font-medium">
                    <span class="rounded px-2 py-0.5" :style="'background:' + palette.success + '22; color:' + palette.success">Success</span>
                    <span class="rounded px-2 py-0.5" :style="'background:' + palette.warning + '22; color:' + palette.warning">Warning</span>
                    <span class="rounded px-2 py-0.5" :style="'background:' + palette.error + '22; color:' + palette.error">Error</span>
                </div>
            </div>
            <div class="border-t px-4 py-2 text-xs" :style="'background:' + palette.footer_bg + '; color:' + palette.text">{{ __('vendor.store_public_powered_by') }} Rentkia</div>
        </div>
    </div>

    @include('vendor.store.partials.save-button')
</form>
