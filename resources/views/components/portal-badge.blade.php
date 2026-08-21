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
    $type = in_array($type, [
        'default',
        'success',
        'warning',
        'danger',
        'info'
    ]) ? $type : 'default';
@endphp

<span {{ $attributes->merge([
    'class' => "portal-badge portal-badge--{$type}"
]) }}>
    {{ $label ?? $slot }}
</span>
