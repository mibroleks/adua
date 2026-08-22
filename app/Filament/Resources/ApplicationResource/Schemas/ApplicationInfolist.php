<?php

namespace App\Filament\Resources\ApplicationResource\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Application Information')
                    ->description('Core information about this application.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('application_number')->label('Application Number')->copyable()->weight('bold'),
                        TextEntry::make('application_status')->label('Application Status')->badge()->color(fn (?string $state): string => match ($state) {
                            'DRAFT' => 'gray',
                            'SUBMITTED' => 'info',
                            'UNDER_REVIEW' => 'warning',
                            'APPROVED' => 'success',
                            'REJECTED' => 'danger',
                            'CORRECTION_REQUIRED' => 'info',
                            default => 'gray',
                        }),
                        TextEntry::make('payment_status')->label('Payment Status')->badge()->color(fn (?string $state): string => match ($state) {
                            'PENDING' => 'warning',
                            'SUCCESS' => 'success',
                            'FAILED' => 'danger',
                            default => 'gray',
                        }),
                        TextEntry::make('submitted_at')->label('Submitted At')->dateTime('d M Y, H:i'),
                        TextEntry::make('created_at')->label('Application Created')->dateTime('d M Y, H:i'),
                        TextEntry::make('updated_at')->label('Last Updated')->dateTime('d M Y, H:i'),
                    ]),

                Section::make('Applicant Information')
                    ->description('Information belonging to the applicant account.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')->label('Full Name')->weight('bold'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                        TextEntry::make('user.phone')->label('Phone')->copyable()->placeholder('Not provided'),
                    ]),

                Section::make('Programme Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('programme.name')->label('Programme')->weight('bold'),
                        TextEntry::make('programme.code')->label('Programme Code')->placeholder('—'),
                        TextEntry::make('programme.degree_type')->label('Degree Type')->placeholder('—'),
                        // ✅ Fixed: use accessor instead of raw field
                        TextEntry::make('formatted_application_fee')->label('Application Fee')->money('NGN'),
                    ]),

                Section::make('Payment Information')
                    ->columns(3)
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->schema([
                                TextEntry::make('reference')->label('Payment Reference')->copyable()->placeholder('No payment reference'),
                                TextEntry::make('amount')->label('Amount')->formatStateUsing(fn ($state, $record) => '₦' . number_format((float) $state / 100, 2)),
                                TextEntry::make('status')->label('Payment Status')->badge()->color(fn (?string $state): string => match ($state) {
                                    'SUCCESS' => 'success',
                                    'PENDING' => 'warning',
                                    'FAILED' => 'danger',
                                    default => 'gray',
                                }),
                                TextEntry::make('gateway')->label('Payment Gateway')->placeholder('—'),
                                TextEntry::make('paid_at')->label('Paid At')->dateTime('d M Y, H:i')->placeholder('Not paid'),
                            ])
                            ->columns(3)
                            ->contained(true),
                    ]),

                Section::make('Application Details')
                    ->description('Information submitted through the dynamic application form.')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('fieldValues')
                            ->schema([
                                TextEntry::make('formField.label')->label('Field')->placeholder('—'),
                                TextEntry::make('value')->label('Response')->placeholder('Not provided'),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ]),

                Section::make('Uploaded Documents')
                    ->description('Documents submitted by the applicant.')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->schema([
                                TextEntry::make('documentType.name')->label('Document')->weight('bold'),
                                TextEntry::make('path')->label('File')->formatStateUsing(fn ($state) => $state ? basename($state) : 'No file'),
                                TextEntry::make('status')->label('Verification Status')->badge()->color(fn (?string $state): string => match ($state) {
                                    'VERIFIED' => 'success',
                                    'REJECTED' => 'danger',
                                    'PENDING' => 'warning',
                                    default => 'gray',
                                }),
                                TextEntry::make('uploaded_at')->label('Uploaded At')->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('rejection_reason')->label('Remarks')->placeholder('—')->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->contained(true),
                    ]),

                Section::make('Admission Decision')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('decision.decision')->label('Decision')->badge()->color(fn (?string $state): string => match ($state) {
                            'APPROVED' => 'success',
                            'REJECTED' => 'danger',
                            default => 'gray',
                        })->placeholder('No decision yet'),
                        TextEntry::make('decision.officer.name')->label('Decision By')->placeholder('—'),
                        TextEntry::make('decision.decided_at')->label('Decision Date')->dateTime('d M Y, H:i')->placeholder('—'),
                        TextEntry::make('decision.remarks')->label('Officer Remarks')->placeholder('No remarks provided')->columnSpanFull(),
                    ]),

                Section::make('Application Status History')
                    ->description('Audit trail of lifecycle changes.')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('statusHistories')
                            ->schema([
                                TextEntry::make('old_status')->label('Previous Status')->placeholder('—'),
                                TextEntry::make('new_status')->label('New Status')->badge(),
                                TextEntry::make('changed_at')->label('Changed At')->dateTime('d M Y, H:i'),
                                TextEntry::make('officer.name')->label('Changed By')->placeholder('System'),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ]),
            ]);
    }
}
