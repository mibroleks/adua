<?php

/*
Component: Payment Form (Unused)
File Path: app/Filament/Resources/PaymentResource/Schemas/PaymentForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Payments are immutable audit records created via PaymentService.
This form schema is intentionally unused — officers cannot create or edit payments.
Kept only as a placeholder for consistency with other resources.

Status: 🚫 Not in use
Version: 1.0
*/

namespace App\Filament\Resources\PaymentResource\Schemas;

use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        // Payments are service-driven, not manually created/edited.
        return $schema->components([]);
    }
}
