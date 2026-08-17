<?php

namespace App\Filament\Resources\AdmissionDecisionResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\TextEntry;

class AdmissionDecisionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('decision')->label('Decision'),
            TextEntry::make('remarks')->label('Remarks'),
            TextEntry::make('decided_at')->dateTime()->label('Decided At'),
        ]);
    }
}
