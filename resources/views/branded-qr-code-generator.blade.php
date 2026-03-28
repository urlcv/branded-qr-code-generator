<div x-data="brandedQrGenerator()" x-init="init()" x-cloak class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label for="bq-url" class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                <input
                    id="bq-url"
                    type="url"
                    x-model="url"
                    @input="validateUrl($event.target.value); generateDebounced()"
                    placeholder="https://example.com"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
                <div class="mt-2 text-xs" x-show="url.length > 0 && !urlValid" x-transition>
                    <span class="text-red-600 font-medium" x-text="urlError"></span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="copyUrl()"
                    :disabled="!urlValid"
                >
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Use standard Tailwind utilities only (no arbitrary values). Production vite builds often scan ./resources/views but not vendor packages, so JIT would omit classes only referenced from the Composer package. --}}
    <div class="flex flex-col lg:flex-row lg:items-start gap-6">
        <div class="w-full min-w-0 space-y-4 lg:flex-1 lg:sticky lg:top-6 order-1">
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-semibold text-gray-800">Preview</div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!urlValid"
                        @click="downloadPng()"
                    >
                        Download PNG
                    </button>
                </div>

                <div class="flex flex-col items-center gap-3">
                    <div class="text-sm font-semibold text-gray-900 text-center" x-show="labelPosition === 'above' && label.trim().length > 0" x-text="label" x-cloak></div>

                    <div
                        class="rounded-2xl border border-gray-200 shadow-sm"
                        :style="`padding:${margin}px; background:${bg};`"
                    >
                        <div id="qr-canvas-target" class="min-h-64 min-w-64 flex items-center justify-center">
                            <div class="text-xs text-gray-500 text-center px-4" x-show="!urlValid" x-cloak>
                                Enter a valid URL to generate a QR code.
                            </div>
                        </div>
                    </div>

                    <div class="text-sm font-semibold text-gray-900 text-center" x-show="labelPosition === 'below' && label.trim().length > 0" x-text="label" x-cloak></div>
                </div>
            </div>
        </div>

        <div class="w-full min-w-0 space-y-5 lg:w-96 lg:flex-shrink-0 order-2">
            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-5">
                <div>
                    <div class="text-sm font-semibold text-gray-800 mb-3">Theme</div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(preset, key) in presets" :key="key">
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-full text-sm border transition"
                                :class="activePreset === key ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                                @click="applyPreset(key)"
                                x-text="key === 'light' ? 'Light' : key === 'dark' ? 'Dark' : key === 'brand' ? 'Brand Blue' : 'Monochrome'"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foreground</label>
                        <div class="flex items-center gap-3 min-w-0">
                            <input type="color" x-model="fg" @input="activePreset = 'custom'; generate()" class="h-10 w-12 shrink-0 rounded border border-gray-300 bg-white" />
                            <input type="text" x-model="fg" @input="activePreset = 'custom'; generate()" class="min-w-0 flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background</label>
                        <div class="flex items-center gap-3 min-w-0">
                            <input type="color" x-model="bg" @input="activePreset = 'custom'; generate()" class="h-10 w-12 shrink-0 rounded border border-gray-300 bg-white" />
                            <input type="text" x-model="bg" @input="activePreset = 'custom'; generate()" class="min-w-0 flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Size</label>
                        <span class="text-xs text-gray-500 tabular-nums" x-text="size + 'px'"></span>
                    </div>
                    <input
                        type="range"
                        min="128"
                        max="512"
                        step="16"
                        x-model.number="size"
                        @input="generate()"
                        class="w-full"
                    />
                    <div class="mt-1 text-xs text-gray-500">Tip: 256px+ is best for print and posters.</div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Quiet zone</label>
                        <span class="text-xs text-gray-500 tabular-nums" x-text="margin + 'px'"></span>
                    </div>
                    <input
                        type="range"
                        min="4"
                        max="32"
                        step="1"
                        x-model.number="margin"
                        @input="enforceMinMargin(); generate()"
                        class="w-full"
                    />
                    <div class="mt-1 text-xs text-gray-500">Minimum enforced to protect scanability.</div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Label (optional)</label>
                        <input
                            type="text"
                            x-model="label"
                            @input="generate()"
                            placeholder="e.g. Scan me"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Label position</label>
                        <select
                            x-model="labelPosition"
                            @change="generate()"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                            <option value="below">Below</option>
                            <option value="above">Above</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-semibold text-gray-800">Scanability</div>
                    <span
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold"
                        :class="scanScore.level === 'excellent' ? 'bg-green-100 text-green-800' : (scanScore.level === 'good' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')"
                    >
                        <svg x-show="scanScore.level === 'risky'" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.516 11.59c.75 1.335-.214 2.99-1.742 2.99H3.483c-1.528 0-2.492-1.655-1.742-2.99l6.516-11.59zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span x-text="scanScore.label"></span>
                    </span>
                </div>
                <div class="text-sm text-gray-600" x-text="scanScore.reason"></div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 bg-white text-gray-800 text-sm font-medium hover:bg-gray-50"
                    @click="resetDefaults()"
                >
                    Reset to defaults
                </button>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm">
        <span class="font-semibold">Pro tip:</span>
        High contrast and adequate quiet zone spacing are the two most important factors for reliable scanning.
        The tool enforces a minimum quiet zone of 4 modules.
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
        labelPosition: 'below', // 'above' | 'below' | 'none'
        activePreset: 'light',
        copied: false,
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
            if (v.length === 0) {
                this.urlValid = false;
                this.urlError = '';
                this._clearQrTarget();
                return;
            }

            const ok = /^https?:\/\//i.test(v) && v.includes('.');
            this.urlValid = ok;
            this.urlError = ok ? '' : 'Enter a valid URL starting with http(s):// and containing a dot.';

            if (!ok) {
                this._clearQrTarget();
            }
        },

        enforceMinMargin() {
            if (!Number.isFinite(this.margin)) this.margin = 4;
            if (this.margin < 4) this.margin = 4;
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
                    if (this._genAttempts < 20) {
                        this._genAttempts++;
                        setTimeout(() => this.generate(), 50);
                    }
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
            let reason = ratio < 3
                ? 'Low colour contrast — may not scan reliably.'
                : (ratio < 4.5 ? 'Moderate colour contrast — usually OK, but test with your phone camera.' : 'High contrast — best for reliable scanning.');

            if (this.size < 160) {
                level = level === 'excellent' ? 'good' : 'risky';
                reason = 'QR size is small — increase size for more reliable scanning.';
            }

            const label = level === 'excellent' ? 'Excellent' : (level === 'good' ? 'Good' : 'Risky');
            return { level, label, reason };
        },

        applyPreset(name) {
            const p = this.presets[name];
            if (!p) return;
            this.fg = p.fg;
            this.bg = p.bg;
            this.activePreset = name;
            this.generate();
        },

        resetDefaults() {
            this.url = '';
            this.urlValid = false;
            this.urlError = '';
            this.fg = '#000000';
            this.bg = '#ffffff';
            this.size = 256;
            this.margin = 4;
            this.label = '';
            this.labelPosition = 'below';
            this.activePreset = 'light';
            this.copied = false;
            this._clearQrTarget();
            this.applyPreset('light');
        },

        async copyUrl() {
            if (!this.urlValid) return;
            try {
                await navigator.clipboard.writeText(this.url.trim());
                this.copied = true;
                setTimeout(() => (this.copied = false), 2000);
            } catch (e) {
                // Ignore; clipboard may be blocked by browser permissions.
            }
        },

        downloadPng() {
            if (!this.urlValid) return;

            setTimeout(() => {
                const target = document.getElementById('qr-canvas-target');
                const qrCanvas = target ? target.querySelector('canvas') : null;
                if (!qrCanvas) return;

                const labelText = (this.label || '').trim();
                const includeLabel = this.labelPosition !== 'none' && labelText.length > 0;

                const padX = 24;
                const labelHeight = includeLabel ? 44 : 0;
                const outW = qrCanvas.width + (this.margin * 2);
                const outH = qrCanvas.height + (this.margin * 2) + labelHeight;

                const out = document.createElement('canvas');
                out.width = outW + (includeLabel ? padX * 2 : 0);
                out.height = outH;

                const ctx = out.getContext('2d');
                if (!ctx) return;

                ctx.fillStyle = this.bg;
                ctx.fillRect(0, 0, out.width, out.height);

                let qrX = includeLabel ? padX : 0;
                let qrY = 0;

                if (includeLabel && this.labelPosition === 'above') {
                    ctx.fillStyle = '#111827';
                    ctx.font = '600 16px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, out.width / 2, labelHeight / 2);
                    qrY = labelHeight;
                }

                // Quiet zone is represented by the background fill; draw QR inside that area.
                ctx.drawImage(qrCanvas, qrX + this.margin, qrY + this.margin);

                if (includeLabel && this.labelPosition === 'below') {
                    ctx.fillStyle = '#111827';
                    ctx.font = '600 16px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, out.width / 2, qrY + (qrCanvas.height + (this.margin * 2)) + (labelHeight / 2));
                }

                const a = document.createElement('a');
                a.download = 'qr-code.png';
                a.href = out.toDataURL('image/png');
                a.click();
            }, 100);
        },

        _hexToRgb(hex) {
            const h = (hex || '').replace('#', '').trim();
            if (h.length === 3) {
                const r = parseInt(h[0] + h[0], 16);
                const g = parseInt(h[1] + h[1], 16);
                const b = parseInt(h[2] + h[2], 16);
                return { r, g, b };
            }
            if (h.length !== 6) return { r: 0, g: 0, b: 0 };
            return {
                r: parseInt(h.slice(0, 2), 16),
                g: parseInt(h.slice(2, 4), 16),
                b: parseInt(h.slice(4, 6), 16),
            };
        },

        _relLuminance({ r, g, b }) {
            const srgb = [r, g, b].map(v => {
                const c = v / 255;
                return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
        },

        _contrastRatio(fg, bg) {
            const L1 = this._relLuminance(this._hexToRgb(fg));
            const L2 = this._relLuminance(this._hexToRgb(bg));
            const lighter = Math.max(L1, L2);
            const darker  = Math.min(L1, L2);
            return (lighter + 0.05) / (darker + 0.05);
        },
    };
}
</script>
@endpush

