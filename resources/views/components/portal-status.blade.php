{{-- 
Component: Portal Status
File Path: resources/views/components/portal-status.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable status component for the student portal.
Displays application/admission statuses with semantic theme tokens.
Ensures consistent human-readable labels across all views.

Status: ✅ Production Ready
Version: 1.0
--}}

@props([
    'status' => 'PENDING', // canonical values: DRAFT, SUBMITTED, UNDER_REVIEW, APPROVED, REJECTED, PAID, UNPAID
    'label' => null,
])

@php
    $map = [
        'DRAFT'        => ['label' => 'Draft', 'class' => 'bg-[var(--theme-muted)] text-[var(--theme-inverse)]'],
        'SUBMITTED'    => ['label' => 'Submitted', 'class' => 'bg-[var(--theme-info)] text-white'],
        'UNDER_REVIEW' => ['label' => 'Under Review', 'class' => 'bg-[var(--theme-warning)] text-white'],
        'APPROVED'     => ['label' => 'Approved', 'class' => 'bg-[var(--theme-success)] text-white'],
        'REJECTED'     => ['label' => 'Rejected', 'class' => 'bg-[var(--theme-danger)] text-white'],
        'PAID'         => ['label' => 'Paid', 'class' => 'bg-[var(--theme-success)] text-white'],
        'UNPAID'       => ['label' => 'Unpaid', 'class' => 'bg-[var(--theme-danger)] text-white'],
    ];

    $config = $map[$status] ?? ['label' => ucfirst(strtolower($status)), 'class' => 'bg-[var(--theme-muted)] text-[var(--theme-inverse)]'];
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {$config['class']}"
]) }}>
    {{ $label ?? $config['label'] }}
</span>
