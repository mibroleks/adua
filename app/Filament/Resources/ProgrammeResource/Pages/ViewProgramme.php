<?php

namespace App\Filament\Resources\ProgrammeResource\Pages;

use App\Filament\Resources\ProgrammeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramme extends ViewRecord
{
    protected static string $resource = ProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
