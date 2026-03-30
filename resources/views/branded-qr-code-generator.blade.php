<style>
    #qr-canvas-target canvas,
    #qr-canvas-target img { display: block; }
    .bqr-modal-backdrop { background: rgba(0,0,0,0.45); }
</style>

<div x-data="brandedQrGenerator()" x-init="init()" x-cloak class="space-y-5">

    {{-- ── URL INPUT ── --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
        <label for="bqr-url" class="block text-sm font-semibold text-gray-700 mb-2">Enter a URL to generate your QR code</label>
        <div class="flex gap-2">
            <input
                id="bqr-url"
                type="url"
                x-model="url"
                @input="validateUrl($event.target.value); generateDebounced()"
                placeholder="https://example.com"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <button
                type="button"
                @click="copyUrl()"
                :disabled="!urlValid"
                class="shrink-0 inline-flex items-center justify-center h-10 px-4 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed"
            >
                <span x-show="!copied">Copy</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>
        <p class="mt-2 text-xs text-red-600 font-medium" x-show="url.length > 0 && !urlValid" x-text="urlError" x-cloak></p>
    </div>

    {{-- ── QR PREVIEW — full width, centred ── --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">

        {{-- Empty state --}}
        <div x-show="!urlValid" class="flex flex-col items-center justify-center py-16 text-center">
            <div style="width:120px; height:120px; border-radius:12px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;color:#d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 17.25h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-700">Paste a URL above to generate your QR code</p>
            <p class="text-xs text-gray-400 mt-1">Customise colours, size, and labels once it's generated</p>
        </div>

        {{-- Generated QR --}}
        <div x-show="urlValid" x-cloak class="flex flex-col items-center gap-4">

            {{-- Label above --}}
            <p class="text-sm font-semibold text-gray-800" x-show="labelPosition === 'above' && label.trim().length > 0" x-text="label" x-cloak></p>

            {{-- QR canvas wrapper — bg colour + quiet zone via padding --}}
            <div
                class="rounded-xl shadow-md"
                :style="`padding:${margin * 4}px; background:${bg}; display:inline-block;`"
            >
                <div id="qr-canvas-target"></div>
            </div>

            {{-- Label below --}}
            <p class="text-sm font-semibold text-gray-800" x-show="labelPosition === 'below' && label.trim().length > 0" x-text="label" x-cloak></p>

            {{-- Scanability badge --}}
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                    :class="{
                        'bg-green-100 text-green-800': scanScore.level === 'excellent',
                        'bg-amber-100 text-amber-800': scanScore.level === 'good',
                        'bg-red-100 text-red-800': scanScore.level === 'risky'
                    }"
                >
                    <span x-show="scanScore.level === 'risky'" x-cloak>⚠</span>
                    <span x-text="'Scanability: ' + scanScore.label"></span>
                </span>
                <span class="text-xs text-gray-500" x-text="scanScore.reason"></span>
            </div>

            {{-- Action bar --}}
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                <button
                    type="button"
                    @click="customiseOpen = true"
                    class="inline-flex items-center gap-2 h-10 px-5 rounded-lg border border-gray-300 bg-white text-gray-800 text-sm font-medium hover:bg-gray-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                    Customise
                </button>
                <button
                    type="button"
                    @click="downloadPng()"
                    class="inline-flex items-center gap-2 h-10 px-5 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download PNG
                </button>
                <button
                    type="button"
                    @click="resetDefaults()"
                    class="inline-flex items-center h-10 px-4 rounded-lg border border-gray-200 bg-white text-gray-500 text-sm hover:bg-gray-50"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- ── CUSTOMISE MODAL ── --}}
    <div
        x-show="customiseOpen"
        x-cloak
        class="bqr-modal-backdrop"
        style="position:fixed; inset:0; z-index:50; display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="customiseOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl w-full"
            style="max-width:480px; max-height:90vh; overflow-y:auto;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Customise QR code</h3>
                <button @click="customiseOpen = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="px-6 py-5 space-y-6">

                {{-- Presets --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3">Theme</p>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="(preset, key) in presets" :key="key">
                            <button
                                type="button"
                                @click="applyPreset(key)"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl border text-sm font-medium transition"
                                :class="activePreset === key ? 'border-gray-900 bg-gray-50 text-gray-900' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'"
                            >
                                <span class="w-5 h-5 rounded-full border border-gray-200 shrink-0" :style="`background:${preset.fg};`"></span>
                                <span x-text="key === 'light' ? 'Light' : key === 'dark' ? 'Dark' : key === 'brand' ? 'Brand Blue' : 'Monochrome'"></span>
                                <span x-show="activePreset === key" class="ml-auto text-gray-900">✓</span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Colours --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foreground</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="fg" @input="activePreset = 'custom'; generate()" class="h-9 w-10 shrink-0 rounded border border-gray-300 cursor-pointer" />
                            <input type="text" x-model="fg" @change="activePreset = 'custom'; generate()" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="bg" @input="activePreset = 'custom'; generate()" class="h-9 w-10 shrink-0 rounded border border-gray-300 cursor-pointer" />
                            <input type="text" x-model="bg" @change="activePreset = 'custom'; generate()" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>
                    </div>
                </div>

                {{-- Size --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Size</label>
                        <span class="text-xs text-gray-500 tabular-nums" x-text="size + 'px'"></span>
                    </div>
                    <input type="range" min="128" max="512" step="16" x-model.number="size" @input="generate()" class="w-full" />
                    <p class="mt-1 text-xs text-gray-400">256px+ recommended for print.</p>
                </div>

                {{-- Quiet zone --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Quiet zone</label>
                        <span class="text-xs text-gray-500 tabular-nums" x-text="margin + 'px'"></span>
                    </div>
                    <input type="range" min="4" max="20" step="1" x-model.number="margin" @input="enforceMinMargin(); generate()" class="w-full" />
                    <p class="mt-1 text-xs text-gray-400">Minimum 4px enforced to protect scanability.</p>
                </div>

                {{-- Label --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Label <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" x-model="label" @input="generate()" placeholder="e.g. Scan me" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                    </div>
                    <div x-show="label.trim().length > 0" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Label position</label>
                        <div class="flex gap-2">
                            <template x-for="pos in ['above', 'below', 'none']" :key="pos">
                                <button
                                    type="button"
                                    @click="labelPosition = pos; generate()"
                                    class="flex-1 py-2 rounded-lg border text-sm font-medium transition capitalize"
                                    :class="labelPosition === pos ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                    x-text="pos"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal footer --}}
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <button @click="resetDefaults()" class="text-sm text-gray-400 hover:text-gray-600">Reset to defaults</button>
                <button @click="customiseOpen = false" class="inline-flex items-center h-9 px-5 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800">Done</button>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm">
        <span class="font-semibold">Pro tip:</span>
        High contrast and adequate quiet zone spacing are the two most important factors for reliable scanning.
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
<script>
function brandedQrGenerator() {
    return {
        url: '',
        urlValid: false,
        urlError: '',
        fg: '#000000',
        bg: '#ffffff',
        size: 256,
        margin: 4,
        label: '',
        labelPosition: 'below',
        activePreset: 'light',
        copied: false,
        customiseOpen: false,
        qrInstance: null,
        _debounce: null,
        _genAttempts: 0,

        presets: {
            light: { fg: '#000000', bg: '#ffffff' },
            dark:  { fg: '#ffffff', bg: '#1a1a1a' },
            brand: { fg: '#1d4ed8', bg: '#eff6ff' },
            mono:  { fg: '#374151', bg: '#f9fafb' },
        },

        init() {
            this.applyPreset('light');
        },

        validateUrl(val) {
            const v = (val || '').trim();
            if (!v.length) { this.urlValid = false; this.urlError = ''; this._clearQrTarget(); return; }
            const ok = /^https?:\/\//i.test(v) && v.includes('.');
            this.urlValid = ok;
            this.urlError = ok ? '' : 'Enter a valid URL starting with http(s)://';
            if (!ok) this._clearQrTarget();
        },

        enforceMinMargin() {
            if (!Number.isFinite(this.margin) || this.margin < 4) this.margin = 4;
        },

        _clearQrTarget() {
            const el = document.getElementById('qr-canvas-target');
            if (el) el.innerHTML = '';
            this.qrInstance = null;
        },

        generateDebounced() {
            clearTimeout(this._debounce);
            this._debounce = setTimeout(() => this.generate(), 300);
        },

        generate() {
            this.enforceMinMargin();
            if (!this.urlValid) return;
            this.$nextTick(() => {
                const target = document.getElementById('qr-canvas-target');
                if (!target) return;
                if (typeof QRCode === 'undefined') {
                    if (this._genAttempts < 20) { this._genAttempts++; setTimeout(() => this.generate(), 50); }
                    return;
                }
                this._genAttempts = 0;
                target.innerHTML = '';
                this.qrInstance = new QRCode(target, {
                    text: this.url.trim(),
                    width: this.size,
                    height: this.size,
                    colorDark: this.fg,
                    colorLight: this.bg,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            });
        },

        get scanScore() {
            const ratio = this._contrastRatio(this.fg, this.bg);
            let level = ratio > 4.5 ? 'excellent' : (ratio >= 3 ? 'good' : 'risky');
            let reason = ratio < 3 ? 'Low contrast — may not scan reliably.'
                : (ratio < 4.5 ? 'Moderate contrast — test with your phone.' : 'High contrast — best for reliable scanning.');
            if (this.size < 160) { level = level === 'excellent' ? 'good' : 'risky'; reason = 'QR size is small — increase for better scanning.'; }
            return { level, label: level.charAt(0).toUpperCase() + level.slice(1), reason };
        },

        applyPreset(name) {
            const p = this.presets[name];
            if (!p) return;
            this.fg = p.fg; this.bg = p.bg; this.activePreset = name;
            this.generate();
        },

        resetDefaults() {
            this.url = ''; this.urlValid = false; this.urlError = '';
            this.fg = '#000000'; this.bg = '#ffffff'; this.size = 256; this.margin = 4;
            this.label = ''; this.labelPosition = 'below'; this.activePreset = 'light'; this.copied = false;
            this._clearQrTarget();
        },

        async copyUrl() {
            if (!this.urlValid) return;
            try { await navigator.clipboard.writeText(this.url.trim()); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch(e) {}
        },

        downloadPng() {
            if (!this.urlValid) return;
            setTimeout(() => {
                const target = document.getElementById('qr-canvas-target');
                const qrCanvas = target ? target.querySelector('canvas') : null;
                if (!qrCanvas) return;
                const labelText = (this.label || '').trim();
                const includeLabel = this.labelPosition !== 'none' && labelText.length > 0;
                const pad = this.margin * 4;
                const labelH = includeLabel ? 44 : 0;
                const out = document.createElement('canvas');
                out.width = qrCanvas.width + pad * 2;
                out.height = qrCanvas.height + pad * 2 + labelH;
                const ctx = out.getContext('2d');
                ctx.fillStyle = this.bg;
                ctx.fillRect(0, 0, out.width, out.height);
                let qrY = pad;
                if (includeLabel && this.labelPosition === 'above') {
                    ctx.fillStyle = '#111827';
                    ctx.font = '600 16px system-ui, sans-serif';
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, out.width / 2, labelH / 2);
                    qrY = pad + labelH;
                }
                ctx.drawImage(qrCanvas, pad, qrY);
                if (includeLabel && this.labelPosition === 'below') {
                    ctx.fillStyle = '#111827';
                    ctx.font = '600 16px system-ui, sans-serif';
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, out.width / 2, qrY + qrCanvas.height + pad + labelH / 2);
                }
                const a = document.createElement('a');
                a.download = 'qr-code.png';
                a.href = out.toDataURL('image/png');
                a.click();
            }, 100);
        },

        _hexToRgb(hex) {
            const h = (hex || '').replace('#', '').trim();
            if (h.length === 3) return { r: parseInt(h[0]+h[0],16), g: parseInt(h[1]+h[1],16), b: parseInt(h[2]+h[2],16) };
            if (h.length !== 6) return { r:0, g:0, b:0 };
            return { r: parseInt(h.slice(0,2),16), g: parseInt(h.slice(2,4),16), b: parseInt(h.slice(4,6),16) };
        },

        _relLuminance({ r, g, b }) {
            return [r,g,b].reduce((sum, v, i) => {
                const c = v / 255;
                const lin = c <= 0.03928 ? c / 12.92 : Math.pow((c+0.055)/1.055, 2.4);
                return sum + lin * [0.2126, 0.7152, 0.0722][i];
            }, 0);
        },

        _contrastRatio(fg, bg) {
            const L1 = this._relLuminance(this._hexToRgb(fg));
            const L2 = this._relLuminance(this._hexToRgb(bg));
            return (Math.max(L1,L2) + 0.05) / (Math.min(L1,L2) + 0.05);
        },
    };
}
</script>
@endpush
