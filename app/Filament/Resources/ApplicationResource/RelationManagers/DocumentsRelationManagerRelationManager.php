<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use App\Filament\Resources\ApplicationDocumentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    // Relationship name must match Application model
    protected static string $relationship = 'documents';

    // Link to existing resource
    protected static ?string $relatedResource = ApplicationDocumentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Document')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Uploaded At')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Remarks')
                    ->wrap(),
            ]);
    }
}
