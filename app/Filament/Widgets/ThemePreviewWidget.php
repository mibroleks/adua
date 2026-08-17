<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Services\ThemeService;

class ThemePreviewWidget extends Widget
{
    protected string $view = 'filament.widgets.theme-preview-widget';

    public function getViewData(): array
    {
        $theme = app(ThemeService::class);

        return [
            'preset' => $theme->preset(),
            'mode'   => $theme->mode(),
        ];
    }
}
