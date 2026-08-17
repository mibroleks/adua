<?php

namespace App\Filament\Resources\ProgrammeResource\Pages;

use App\Filament\Resources\ProgrammeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgrammes extends ListRecords
{
    protected static string $resource = ProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
