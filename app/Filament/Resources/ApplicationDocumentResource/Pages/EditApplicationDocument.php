<?php

/*
Component: EditApplicationDocument Page
File Path: app/Filament/Resources/ApplicationDocumentResource/Pages/EditApplicationDocument.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the edit page for application documents in Filament.
Allows officers to view metadata, verify or reject documents, and manage remarks.

Status: ✅ Production Ready
Version: 1.1
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Pages;

use App\Filament\Resources\ApplicationDocumentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class EditApplicationDocument extends EditRecord
{
    protected static string $resource = ApplicationDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('verify')
                ->label('Mark as Verified')
                ->color('success')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'VERIFIED',
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ]);
                }),
            Action::make('reject')
                ->label('Reject Document')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => 'REJECTED',
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                }),
        ];
    }
}
