<?php

/*
Component: Application Infolist
File Path: app/Filament/Resources/ApplicationResource/Schemas/ApplicationInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the structured application dossier displayed on the
Officer Filament application view page.

Responsibilities:
- Application identity and lifecycle information
- Applicant and programme information
- Payment records
- Dynamic application form responses
- Uploaded document information
- Admission decision
- Complete application status history

Design:
Premium institutional application dossier.
Full-width sections with responsive content grids.

Status: 🚧 Premium UI Redesign
Version: 2.2
*/

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

                /*
                |--------------------------------------------------------------------------
                | Application Information
                |--------------------------------------------------------------------------
                */

                Section::make('Application Information')
                    ->description('Application reference, lifecycle status and important dates.')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('primary')
                    ->columns(3)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--identity',
                    ])
                    ->schema([

                        TextEntry::make('application_number')
                            ->label('Application Number')
                            ->weight('bold')
                            ->copyable()
                            ->placeholder('Not assigned'),

                        TextEntry::make('application_status')
                            ->label('Application Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'DRAFT' => 'gray',
                                'SUBMITTED' => 'info',
                                'UNDER_REVIEW' => 'warning',
                                'APPROVED' => 'success',
                                'REJECTED' => 'danger',
                                'CORRECTION_REQUIRED' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'DRAFT' => 'Draft',
                                'SUBMITTED' => 'Submitted',
                                'UNDER_REVIEW' => 'Under Review',
                                'APPROVED' => 'Approved',
                                'REJECTED' => 'Rejected',
                                'CORRECTION_REQUIRED' => 'Correction Required',
                                default => $state ?? 'Unknown',
                            }),

                        TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'PENDING' => 'warning',
                                'SUCCESS' => 'success',
                                'FAILED' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'PENDING' => 'Pending',
                                'SUCCESS' => 'Successful',
                                'FAILED' => 'Failed',
                                default => $state ?? 'Unknown',
                            }),

                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not submitted'),

                        TextEntry::make('created_at')
                            ->label('Application Created')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('—'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Applicant Information
                |--------------------------------------------------------------------------
                */

                Section::make('Applicant Information')
                    ->description('Personal and contact information associated with the applicant account.')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->columns(2)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--applicant',
                    ])
                    ->schema([

                        TextEntry::make('user.name')
                            ->label('Full Name')
                            ->weight('bold')
                            ->placeholder('Applicant name unavailable'),

                        TextEntry::make('user.email')
                            ->label('Email Address')
                            ->copyable()
                            ->placeholder('Not provided'),

                        TextEntry::make('user.phone')
                            ->label('Phone Number')
                            ->copyable()
                            ->placeholder('Not provided'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Programme Information
                |--------------------------------------------------------------------------
                */

                Section::make('Programme Information')
                    ->description('Programme selected by the applicant and the applicable application fee.')
                    ->icon('heroicon-o-academic-cap')
                    ->iconColor('primary')
                    ->columns(2)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--programme',
                    ])
                    ->schema([

                        TextEntry::make('programme.name')
                            ->label('Programme')
                            ->weight('bold')
                            ->columnSpanFull()
                            ->placeholder('Programme unavailable'),

                        TextEntry::make('programme.code')
                            ->label('Programme Code')
                            ->placeholder('—'),

                        TextEntry::make('programme.degree_type')
                            ->label('Degree Type')
                            ->placeholder('—'),

                        TextEntry::make('formatted_application_fee')
                            ->label('Application Fee')
                            ->weight('bold')
                            ->placeholder('—'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Payment Information
                |--------------------------------------------------------------------------
                |
                | Preserve the complete payments collection. An application
                | may have multiple payment records.
                |
                */

                Section::make('Payment Information')
                    ->description('Payment transactions associated with this application.')
                    ->icon('heroicon-o-credit-card')
                    ->iconColor('success')
                    ->columns(1)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--payments',
                    ])
                    ->schema([

                        RepeatableEntry::make('payments')
                            ->label('Payment Transactions')
                            ->schema([

                                TextEntry::make('reference')
                                    ->label('Payment Reference')
                                    ->copyable()
                                    ->columnSpanFull()
                                    ->placeholder('No payment reference'),

                                TextEntry::make('amount')
                                    ->label('Amount')
                                    ->weight('bold')
                                    ->formatStateUsing(
                                        fn ($state): string => '₦' . number_format((float) $state / 100, 2)
                                    )
                                    ->placeholder('₦0.00'),

                                TextEntry::make('status')
                                    ->label('Payment Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'SUCCESS' => 'success',
                                        'PENDING' => 'warning',
                                        'FAILED' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'SUCCESS' => 'Successful',
                                        'PENDING' => 'Pending',
                                        'FAILED' => 'Failed',
                                        default => $state ?? 'Unknown',
                                    }),

                                TextEntry::make('gateway')
                                    ->label('Payment Gateway')
                                    ->placeholder('—'),

                                TextEntry::make('paid_at')
                                    ->label('Paid At')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('Not paid'),

                            ])
                            ->columns(2)
                            ->contained(true),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Dynamic Application Details
                |--------------------------------------------------------------------------
                */

                Section::make('Application Details')
                    ->description('Responses submitted through the institution application form.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->iconColor('primary')
                    ->collapsible()
                    ->columns(1)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--details',
                    ])
                    ->schema([

                        RepeatableEntry::make('fieldValues')
                            ->label('Submitted Responses')
                            ->schema([

                                TextEntry::make('formField.label')
                                    ->label('Application Field')
                                    ->weight('medium')
                                    ->placeholder('Unnamed field'),

                                TextEntry::make('value')
                                    ->label('Applicant Response')
                                    ->placeholder('Not provided')
                                    ->wrap(),

                            ])
                            ->columns(2)
                            ->contained(false),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Uploaded Documents
                |--------------------------------------------------------------------------
                |
                | Display document metadata only. Do not expose raw storage
                | paths as public URLs from this infolist.
                |
                */

                Section::make('Uploaded Documents')
                    ->description('Documents submitted by the applicant and their verification status.')
                    ->icon('heroicon-o-folder-open')
                    ->iconColor('primary')
                    ->collapsible()
                    ->columns(1)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--documents',
                    ])
                    ->schema([

                        RepeatableEntry::make('documents')
                            ->label('Submitted Documents')
                            ->schema([

                                TextEntry::make('documentType.name')
                                    ->label('Document Type')
                                    ->weight('bold'),

                                TextEntry::make('path')
                                    ->label('Stored File')
                                    ->formatStateUsing(
                                        fn ($state): string => $state
                                            ? basename($state)
                                            : 'No file uploaded'
                                    )
                                    ->placeholder('No file uploaded')
                                    ->wrap(),

                                TextEntry::make('status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'VERIFIED' => 'success',
                                        'REJECTED' => 'danger',
                                        'PENDING' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'VERIFIED' => 'Verified',
                                        'REJECTED' => 'Rejected',
                                        'PENDING' => 'Pending Verification',
                                        default => $state ?? 'Unknown',
                                    }),

                                TextEntry::make('uploaded_at')
                                    ->label('Uploaded At')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('—'),

                                TextEntry::make('rejection_reason')
                                    ->label('Remarks')
                                    ->placeholder('No remarks')
                                    ->columnSpanFull()
                                    ->wrap(),

                            ])
                            ->columns(2)
                            ->contained(true),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Admission Decision
                |--------------------------------------------------------------------------
                */

                Section::make('Admission Decision')
                    ->description('Official admission decision and the officer responsible.')
                    ->icon('heroicon-o-check-badge')
                    ->iconColor('success')
                    ->columns(2)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--decision',
                    ])
                    ->schema([

                        TextEntry::make('decision.decision')
                            ->label('Decision')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'APPROVED' => 'success',
                                'REJECTED' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'APPROVED' => 'Approved',
                                'REJECTED' => 'Rejected',
                                default => $state ?? 'No decision yet',
                            })
                            ->placeholder('No decision yet'),

                        TextEntry::make('decision.officer.name')
                            ->label('Decision By')
                            ->placeholder('Not assigned'),

                        TextEntry::make('decision.decided_at')
                            ->label('Decision Date')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('No decision date'),

                        TextEntry::make('decision.remarks')
                            ->label('Officer Remarks')
                            ->placeholder('No remarks provided')
                            ->columnSpanFull()
                            ->wrap(),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Application Status History
                |--------------------------------------------------------------------------
                |
                | Preserve every history record. Do not deduplicate or hide
                | audit events at the presentation layer.
                |
                */

                Section::make('Application Status History')
                    ->description('Complete audit trail of recorded application lifecycle transitions.')
                    ->icon('heroicon-o-clock')
                    ->iconColor('gray')
                    ->collapsible()
                    ->columns(1)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'application-dossier__section application-dossier__section--history',
                    ])
                    ->schema([

                        RepeatableEntry::make('statusHistories')
                            ->label('Lifecycle Events')
                            ->schema([

                                TextEntry::make('old_status')
                                    ->label('Previous Status')
                                    ->placeholder('Initial status')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'DRAFT' => 'Draft',
                                        'SUBMITTED' => 'Submitted',
                                        'UNDER_REVIEW' => 'Under Review',
                                        'APPROVED' => 'Approved',
                                        'REJECTED' => 'Rejected',
                                        'CORRECTION_REQUIRED' => 'Correction Required',
                                        default => $state ?? 'Initial status',
                                    }),

                                TextEntry::make('new_status')
                                    ->label('New Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'SUBMITTED' => 'info',
                                        'UNDER_REVIEW' => 'warning',
                                        'APPROVED' => 'success',
                                        'REJECTED' => 'danger',
                                        'CORRECTION_REQUIRED' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'DRAFT' => 'Draft',
                                        'SUBMITTED' => 'Submitted',
                                        'UNDER_REVIEW' => 'Under Review',
                                        'APPROVED' => 'Approved',
                                        'REJECTED' => 'Rejected',
                                        'CORRECTION_REQUIRED' => 'Correction Required',
                                        default => $state ?? 'Unknown',
                                    }),

                                TextEntry::make('changed_at')
                                    ->label('Date and Time')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('—'),

                                TextEntry::make('officer.name')
                                    ->label('Changed By')
                                    ->placeholder('System'),

                            ])
                            ->columns(2)
                            ->contained(false),
                    ]),
            ]);
    }
}
