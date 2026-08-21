{{-- 
Component: Portal Notification Badge
File Path: resources/views/components/portal-notification-badge.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable badge for displaying unread notification counts.
Designed to attach to icons or menu items in the student portal.
Uses semantic theme tokens for consistent styling.

Status: ✅ Production Ready
Version: 1.0
--}}

@props([
    'count' => 0, // integer unread count
])

@if($count > 0)
    <span {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-full bg-[var(--theme-danger)] text-white text-[10px] font-bold leading-none px-1.5 py-0.5'
    ]) }}>
        {{ $count }}
    </span>
@endif
