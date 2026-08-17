{{-- 
Component: Portal Badge (Preset-Driven)
File Path: resources/views/components/portal-badge.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable badge component for the student portal.
Displays small status/label indicators with dynamic colours from ThemeService.
Ensures officers can change branding without touching code.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'type' => 'default', // options: default, success, warning, danger, info
    'label' => null,
])

@php
    $classes = match($type) {
        'success' => 'bg-[var(--theme-success)] text-white',
        'warning' => 'bg-[var(--theme-warning)] text-white',
        'danger'  => 'bg-[var(--theme-danger)] text-white',
        'info'    => 'bg-[var(--theme-info)] text-white',
        default   => 'bg-[var(--theme-muted)] text-[var(--theme-inverse)]',
    };
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {$classes}"
]) }}>
    {{ $label ?? $slot }}
</span>
