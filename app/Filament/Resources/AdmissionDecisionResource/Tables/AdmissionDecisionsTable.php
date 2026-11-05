<?php

/*
Component: Admission Decisions Table
File Path: app/Filament/Resources/AdmissionDecisionResource/Tables/AdmissionDecisionsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the read-oriented Filament table schema for the
institutional admission decision register.

The table supports:
- Applicant full name visibility.
- Application-processing decision visibility.
- Institutional admission outcome visibility.
- Processing and admission officer identification.
- Application referral attribution visibility.
- Direct / Organic application identification.
- Referral partner filtering.
- Admission outcome filtering.
- Read-only access to individual admission decision records.

Referral Attribution:
- Reads referral attribution from the original Application.
- Displays the registered referral partner where available.
- Displays Direct / Organic when no referral partner is recorded.
- Supports filtering by individual referral partners.
- Supports filtering direct / organic applications separately.
- Does not duplicate or modify application referral attribution.
- Does not affect admission eligibility or institutional decisions.

Architecture:
- Application approval and institutional admission remain separate.
- Admission outcomes are recorded through AdmissionService.
- Historical decision records remain preserved.
- Admission letters remain a separate process.
- The register remains read-oriented.

Status: Premium admission register + referral attribution
Version: 2.1 (Filament 5)
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Tables;

use App\Models\ReferralPartner;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdmissionDecisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Applicant Information
                |--------------------------------------------------------------------------
                |
                | Applicant identity is retrieved through:
                | AdmissionDecision -> Application -> User -> Name.
                |
                */

                TextColumn::make('application.user.name')
                    ->label('Applicant Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A')
                    ->weight('semibold'),

                /*
                |--------------------------------------------------------------------------
                | Application Reference
                |--------------------------------------------------------------------------
                */

                TextColumn::make('application.application_number')
                    ->label('Application Number')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A')
                    ->weight('semibold')
                    ->copyable(),

                /*
                |--------------------------------------------------------------------------
                | Referral Attribution
                |--------------------------------------------------------------------------
                |
                | Referral attribution belongs to the original Application.
                | It is deliberately not duplicated on AdmissionDecision.
                |
                */

                TextColumn::make('application.referralPartner.name')
                    ->label('Referral Source')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->default('Direct / Organic')
                    ->color(
                        fn (?string $state): string =>
                            filled($state) && $state !== 'Direct / Organic'
                                ? 'success'
                                : 'gray'
                    )
                    ->icon(
                        fn (?string $state): string =>
                            filled($state) && $state !== 'Direct / Organic'
                                ? 'heroicon-o-user-group'
                                : 'heroicon-o-globe-alt'
                    )
                    ->tooltip(
                        fn (?string $state): string =>
                            filled($state) && $state !== 'Direct / Organic'
                                ? 'Recorded recruitment partner attribution on the original application'
                                : 'No referral partner was recorded for the original application'
                    ),

                /*
                |--------------------------------------------------------------------------
                | Application Processing Decision
                |--------------------------------------------------------------------------
                */

                TextColumn::make('decision')
                    ->label('Application Decision')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'APPROVED' => 'Approved',
                            'REJECTED' => 'Rejected',
                            default => $state ?? 'Not recorded',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'APPROVED' => 'success',
                            'REJECTED' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make('officer.name')
                    ->label('Processing Officer')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('decided_at')
                    ->label('Application Decision Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('N/A'),

                /*
                |--------------------------------------------------------------------------
                | Institutional Admission Outcome
                |--------------------------------------------------------------------------
                */

                TextColumn::make('admission_status')
                    ->label('Admission Outcome')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'PENDING' => 'Pending',
                            'ADMITTED' => 'Admitted',
                            'NOT_ADMITTED' => 'Not Admitted',
                            'WAITLISTED' => 'Waitlisted',
                            'DEFERRED' => 'Deferred',
                            default => 'Not yet decided',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'ADMITTED' => 'success',
                            'NOT_ADMITTED' => 'danger',
                            'WAITLISTED' => 'warning',
                            'DEFERRED' => 'info',
                            'PENDING' => 'gray',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make('admissionOfficer.name')
                    ->label('Admission Officer')
                    ->searchable()
                    ->placeholder('Not assigned'),

                TextColumn::make('admission_decided_at')
                    ->label('Admission Decision Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('Not decided'),

                /*
                |--------------------------------------------------------------------------
                | Decision Remarks
                |--------------------------------------------------------------------------
                */

                TextColumn::make('admission_remarks')
                    ->label('Admission Remarks')
                    ->limit(50)
                    ->tooltip(
                        fn (?string $state): ?string => $state
                    )
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('remarks')
                    ->label('Application Remarks')
                    ->limit(50)
                    ->tooltip(
                        fn (?string $state): ?string => $state
                    )
                    ->placeholder('—')
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Record Audit
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->label('Record Created')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

                /*
                |--------------------------------------------------------------------------
                | Application Decision Filter
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('decision')
                    ->label('Application Decision')
                    ->options([
                        'APPROVED' => 'Approved',
                        'REJECTED' => 'Rejected',
                    ])
                    ->native(false),

                /*
                |--------------------------------------------------------------------------
                | Institutional Admission Outcome Filter
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('admission_status')
                    ->label('Admission Outcome')
                    ->options([
                        'PENDING' => 'Pending',
                        'ADMITTED' => 'Admitted',
                        'NOT_ADMITTED' => 'Not Admitted',
                        'WAITLISTED' => 'Waitlisted',
                        'DEFERRED' => 'Deferred',
                    ])
                    ->native(false),

                /*
                |--------------------------------------------------------------------------
                | Referral Partner Filter
                |--------------------------------------------------------------------------
                |
                | Filters admission decisions through their original
                | Application referral_partner_id.
                |
                */

                SelectFilter::make('referral_partner_id')
                    ->label('Referral Partner')
                    ->placeholder('All referral partners')
                    ->options(
                        fn (): array => ReferralPartner::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                filled($data['value'] ?? null),
                                fn (Builder $query): Builder => $query
                                    ->whereHas(
                                        'application',
                                        fn (Builder $applicationQuery): Builder =>
                                            $applicationQuery->where(
                                                'referral_partner_id',
                                                $data['value']
                                            )
                                    )
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Direct / Organic Applications Filter
                |--------------------------------------------------------------------------
                |
                | Selects admission decisions whose original Application
                | has no recorded referral partner.
                |
                */

                Filter::make('direct_applications')
                    ->label('Direct / Organic Applications')
                    ->toggle()
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->whereHas(
                                'application',
                                fn (Builder $applicationQuery): Builder =>
                                    $applicationQuery->whereNull(
                                        'referral_partner_id'
                                    )
                            )
                    ),

            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            |
            | Individual admission decision records remain accessible
            | through their dedicated read-only View page.
            |
            */

            ->recordActions([
                ViewAction::make(),
            ])

            /*
            |--------------------------------------------------------------------------
            | Toolbar Actions
            |--------------------------------------------------------------------------
            |
            | No generic create, edit or delete actions are exposed.
            |
            */

            ->toolbarActions([]);
    }
}
