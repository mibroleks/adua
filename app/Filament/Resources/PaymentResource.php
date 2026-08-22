<?php

/*
Component: Payment Resource
File Path: app/Filament/Resources/PaymentResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing application payments.
Payments are immutable audit records created via PaymentService,
so officers can only list and view payments (no manual create/edit).
Supports filters by status, type, gateway, and date.
Future-proofed with reconciliation fields for finance integration.

Status: ✅ Production Ready
Version: 2.0 (added reconciliation fields)
*/

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Resources\PaymentResource\Pages\ViewPayment;
use App\Filament\Resources\PaymentResource\Schemas\PaymentInfolist;
use App\Filament\Resources\PaymentResource\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    // Navigation settings (must be public static in Filament v3)
    public static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static UnitEnum|string|null $navigationGroup = 'Finance';
    public static ?string $navigationLabel = 'Payments';

    // Title attribute for display
    protected static ?string $recordTitleAttribute = 'reference';

    // ✅ Infolist definition (includes reconciliation fields)
    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    // ✅ Table definition (includes reconciliation fields)
    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // ✅ Pages (List + View only)
    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view'  => ViewPayment::route('/{record}'),
        ];
    }
}
