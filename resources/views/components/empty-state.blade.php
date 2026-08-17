{{-- 
Component: Empty State (Preset-Driven)
File Path: resources/views/components/empty-state.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable empty state component for the student portal.
Displays a friendly message, optional icon, and call-to-action.
Ensures officers can configure text without touching code.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'icon' => null,       // optional icon (Heroicon or custom SVG)
    'title' => 'Nothing here yet',
    'message' => 'There is currently no data to display.',
    'action' => null,     // optional slot for button or link
])

<div class="flex flex-col items-center justify-center rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] p-10 text-center shadow-sm">
    {{-- Icon --}}
    @if($icon)
        <div class="mb-4 text-[var(--theme-muted)]">
            {!! $icon !!}
        </div>
    @endif

    {{-- Title --}}
    <h3 class="text-lg font-bold theme-heading">
        {{ $title }}
    </h3>

    {{-- Message --}}
    <p class="mt-2 text-sm theme-muted">
        {{ $message }}
    </p>

    {{-- Action --}}
    @if($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
