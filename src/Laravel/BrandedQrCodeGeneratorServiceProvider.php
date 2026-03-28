<?php
declare(strict_types=1);

namespace URLCV\BrandedQrCodeGenerator\Laravel;

use Illuminate\Support\ServiceProvider;

class BrandedQrCodeGeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'branded-qr-code-generator');
    }
}

