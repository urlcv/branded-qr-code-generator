<?php
declare(strict_types=1);

namespace URLCV\BrandedQrCodeGenerator\Laravel;

use App\Tools\Contracts\ToolInterface;

class BrandedQrCodeGeneratorTool implements ToolInterface
{
    public function slug(): string { return 'branded-qr-code-generator'; }

    public function name(): string { return 'Branded QR Code Generator'; }

    public function summary(): string
    {
        return 'Generate customisable QR codes with live scanability checks and one-click PNG download.';
    }

    public function descriptionMd(): ?string
    {
        return <<<'MD'
## Branded QR Code Generator

Create scannable QR codes with custom colours and branding. Preview updates live as you adjust settings.

### Features
- Instant QR generation from any URL
- Foreground and background colour pickers
- 4 preset themes: Light, Dark, Brand Blue, Monochrome
- Optional text label (above or below)
- Live scanability score: Excellent / Good / Risky
- One-click PNG download with label baked in
- Quiet zone enforced at a safe minimum to protect scanability

Runs entirely in your browser — no upload, no account needed.
MD;
    }

    public function categories(): array { return ['productivity', 'marketing']; }

    public function tags(): array { return ['qr-code', 'qr', 'generator', 'branded', 'marketing', 'links']; }

    public function inputSchema(): array { return []; }

    public function run(array $input): array { return []; }

    public function mode(): string { return 'frontend'; }

    public function isAsync(): bool { return false; }

    public function isPublic(): bool { return true; }

    public function frontendView(): ?string { return 'branded-qr-code-generator::branded-qr-code-generator'; }

    public function rateLimitPerMinute(): int { return 60; }

    public function cacheTtlSeconds(): int { return 0; }

    public function sortWeight(): int { return 135; }
}

