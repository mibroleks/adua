<?php

namespace App\Filament\Resources\AdmissionDecisionResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;

class AdmissionDecisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('decision')
                ->options([
                    'APPROVED' => 'Approved',
                    'REJECTED' => 'Rejected',
                ])
                ->required()
                ->label('Decision'),

            Textarea::make('remarks')
                ->label('Remarks')
                ->maxLength(1000),
        ]);
    }
}
