{{-- 
Component: Alert (Preset-Driven)
File Path: resources/views/components/alert.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable alert component for the student portal.
Displays contextual messages with dynamic colours from ThemeService.
Supports multiple variants (success, warning, danger, info).

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'variant' => 'info',   // options: success, warning, danger, info
    'title' => null,       // optional title
    'message' => null,     // optional message
])

@php
    $classes = match($variant) {
        'success' => 'border-[var(--theme-success)] bg-[var(--theme-success-soft)] text-[var(--theme-success)]',
        'warning' => 'border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',
        'danger'  => 'border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',
        default   => 'border-[var(--theme-info)] bg-[var(--theme-info-soft)] text-[var(--theme-info)]',
    };
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-md border p-4 {$classes}"
]) }}>
    @if($title)
        <h4 class="text-sm font-semibold theme-heading">
            {{ $title }}
        </h4>
    @endif

    @if($message)
        <p class="mt-1 text-xs theme-text">
            {{ $message }}
        </p>
    @endif

    {{-- Slot for custom content --}}
    {{ $slot }}
</div>
