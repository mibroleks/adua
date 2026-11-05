<?php

/*
Component: Applications Table
File Path: app/Filament/Resources/ApplicationResource/Tables/ApplicationsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the premium, read-oriented Filament table schema for
listing and reviewing admission applications.

The table supports:
- Application and applicant search.
- Recruitment partner attribution visibility.
- Direct / Organic application identification.
- Application lifecycle status monitoring.
- Independent payment status monitoring.
- Programme and application fee visibility.
- Submission date filtering.
- Admission decision visibility.
- Read-only access to the complete application record.

Referral Attribution:
- Displays the registered referral partner attached to the application.
- Displays Direct / Organic when no referral partner is recorded.
- Supports filtering by individual referral partners.
- Supports filtering direct / organic applications separately.
- Does not affect admission eligibility, fees, payments, or decisions.

Status: Implementation
Version: 3.4 (premium admissions register + referral attribution refinement)
Filament: 5.x
*/

namespace App\Filament\Resources\ApplicationResource\Tables;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\ReferralPartner;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Application Number
                TextColumn::make('application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Application number copied')
                    ->weight('bold')
                    ->color('primary')
                    ->description('Application reference'),

                // Applicant
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description('Registered applicant'),

                // Referral Source
                TextColumn::make('referralPartner.name')
                    ->label('Referral Source')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->default('Direct / Organic')
                    ->color(fn (?string $state): string =>
                        filled($state) && $state !== 'Direct / Organic'
                            ? 'success'
                            : 'gray'
                    )
                    ->icon(fn (?string $state): string =>
                        filled($state) && $state !== 'Direct / Organic'
                            ? 'heroicon-o-user-group'
                            : 'heroicon-o-globe-alt'
                    )
                    ->tooltip(fn (?string $state): string =>
                        filled($state) && $state !== 'Direct / Organic'
                            ? 'Recorded recruitment partner attribution'
                            : 'No referral partner was recorded for this application'
                    ),

                // Programme
                TextColumn::make('programme.name')
                    ->label('Programme')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(40),

                // Application Status
                TextColumn::make('application_status')
                    ->label('Application Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'DRAFT'               => 'Draft',
                        'SUBMITTED'           => 'Submitted',
                        'UNDER_REVIEW'        => 'Under Review',
                        'APPROVED'            => 'Approved',
                        'REJECTED'            => 'Rejected',
                        'CORRECTION_REQUIRED' => 'Correction Required',
                        default               => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'DRAFT'               => 'gray',
                        'SUBMITTED'           => 'info',
                        'UNDER_REVIEW'        => 'warning',
                        'APPROVED'            => 'success',
                        'REJECTED'            => 'danger',
                        'CORRECTION_REQUIRED' => 'info',
                        default               => 'gray',
                    })
                    ->icon(fn (?string $state): ?string => match ($state) {
                        'SUBMITTED' => 'heroicon-o-paper-airplane',
                        'UNDER_REVIEW' => 'heroicon-o-clock',
                        'APPROVED' => 'heroicon-o-check-circle',
                        'REJECTED' => 'heroicon-o-x-circle',
                        'CORRECTION_REQUIRED' => 'heroicon-o-exclamation-circle',
                        default => null,
                    }),

                // Payment Status
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'PENDING' => 'Pending',
                        'SUCCESS' => 'Paid',
                        'FAILED'  => 'Failed',
                        default   => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'SUCCESS' => 'success',
                        'FAILED'  => 'danger',
                        default   => 'gray',
                    })
                    ->icon(fn (?string $state): ?string => match ($state) {
                        'SUCCESS' => 'heroicon-o-check-circle',
                        'PENDING' => 'heroicon-o-clock',
                        'FAILED' => 'heroicon-o-x-circle',
                        default => null,
                    }),

                // Application Fee
                TextColumn::make('formatted_application_fee')
                    ->label('Application Fee')
                    ->money('NGN', true)
                    ->sortable()
                    ->alignEnd(),

                // Submitted At
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->description(fn (Application $record): ?string =>
                        $record->submitted_at
                            ? $record->submitted_at->format('h:i A')
                            : 'Awaiting submission'
                    )
                    ->placeholder('Not submitted'),

                // Latest Decision
                TextColumn::make('decision.decision')
                    ->label('Decision')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'APPROVED' => 'Approved',
                        'REJECTED' => 'Rejected',
                        default    => 'Pending',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default    => 'gray',
                    })
                    ->icon(fn (?string $state): ?string => match ($state) {
                        'APPROVED' => 'heroicon-o-check-circle',
                        'REJECTED' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-minus-circle',
                    })
                    ->placeholder('Pending'),
            ])

            ->filters([

                // Application Status Filter
                SelectFilter::make('application_status')
                    ->label('Application Status')
                    ->options([
                        'DRAFT'               => 'Draft',
                        'SUBMITTED'           => 'Submitted',
                        'UNDER_REVIEW'        => 'Under Review',
                        'APPROVED'            => 'Approved',
                        'REJECTED'            => 'Rejected',
                        'CORRECTION_REQUIRED' => 'Correction Required',
                    ])
                    ->native(false),

                // Payment Status Filter
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'PENDING' => 'Pending',
                        'SUCCESS' => 'Paid',
                        'FAILED'  => 'Failed',
                    ])
                    ->native(false),

                // Referral Partner Filter
                SelectFilter::make('referral_partner_id')
                    ->label('Referral Partner')
                    ->placeholder('All referral partners')
                    ->options(fn (): array => ReferralPartner::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                // Direct / Organic Applications Filter
                Filter::make('direct_applications')
                    ->label('Direct / Organic Applications')
                    ->toggle()
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNull('referral_partner_id')
                    ),

                // Submission Date Filter
                Filter::make('submitted_at')
                    ->label('Submission Date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(false),

                        DatePicker::make('until')
                            ->label('Until')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['from'] ?? null),
                            fn (Builder $query): Builder =>
                                $query->whereDate('submitted_at', '>=', $data['from'])
                        )
                        ->when(
                            filled($data['until'] ?? null),
                            fn (Builder $query): Builder =>
                                $query->whereDate('submitted_at', '<=', $data['until'])
                        )
                    ),
            ])

            ->recordActions([
                Action::make('view')
                    ->label('View Application')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Application $record): string =>
                        ApplicationResource::getUrl('view', [
                            'record' => $record,
                        ])
                    ),
            ])

            ->toolbarActions([]);
    }
}
