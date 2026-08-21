<?php

/*
Component: ApplicationDocumentsTable
File Path: app/Filament/Resources/ApplicationDocumentResource/Tables/ApplicationDocumentsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the table schema for listing application documents in Filament.
Shows applicant, programme, document type, status, upload details, and a secure preview/download link.
Supports filters and bulk actions for officer review, including preview, verification, and rejection.

Status: ✅ Filament v5 Compatible
Version: 1.9 (preview passes extension + mime, reject uses rejection_reason)
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Tables;

use App\Models\ApplicationDocument;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;     // ✅ global namespace in v5
use Filament\Actions\DeleteBulkAction;    // ✅ global namespace in v5
use Filament\Actions\Action;              // ✅ global namespace in v5
use Filament\Forms;

class ApplicationDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('application.user.name')
                    ->label('Applicant')
                    ->searchable(),

                TextColumn::make('application.programme.name')
                    ->label('Programme')
                    ->sortable(),

                TextColumn::make('documentType.name')
                    ->label('Document Type')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'PENDING',
                        'success' => 'VERIFIED',
                        'danger'  => 'REJECTED',
                    ])
                    ->sortable(),

                TextColumn::make('uploaded_at')
                    ->label('Uploaded At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                // Secure download link via authorized route
                TextColumn::make('download')
                    ->label('Download')
                    ->url(fn ($record) => route('application.documents.view', [$record->application_id, $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->path))
                    ->formatStateUsing(fn () => 'Open File'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING'  => 'Pending',
                        'VERIFIED' => 'Verified',
                        'REJECTED' => 'Rejected',
                    ]),
                SelectFilter::make('application.programme_id')
                    ->relationship('application.programme', 'name')
                    ->label('Programme'),
            ])
            ->recordActions([
                // Secure preview modal
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ApplicationDocument $record) =>
                        'Preview: ' . ($record->documentType?->name ?? $record->original_name)
                    )
                    ->modalWidth('7xl')
                    ->modalContent(fn (ApplicationDocument $record) => view('admin.documents.preview', [
                        'url' => route('application.documents.view', [$record->application_id, $record->id]),
                        'extension' => strtolower(pathinfo($record->original_name, PATHINFO_EXTENSION)), // ✅ pass extension
                        'mime' => $record->mime_type, // ✅ also pass mime
                    ])),

                // Verify action
                Action::make('verify')
                    ->label('Verify')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (ApplicationDocument $record) => app(\App\Services\AdmissionService::class)
                        ->verifyDocument($record, auth()->id())),

                // Reject action with reason
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(fn (ApplicationDocument $record, array $data) => app(\App\Services\AdmissionService::class)
                        ->rejectDocument($record, auth()->id(), $data['reason'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
