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

<div class="portal-empty-state">

    {{-- Icon --}}
    @if($icon)
        <div class="portal-empty-state__icon">
            {!! $icon !!}
        </div>
    @endif

    {{-- Title --}}
    <h3 class="portal-empty-state__title">
        {{ $title }}
    </h3>

    {{-- Message --}}
    <p class="portal-empty-state__message">
        {{ $message }}
    </p>

    {{-- Action --}}
    @if($action)
        <div class="portal-empty-state__action">
            {{ $action }}
        </div>
    @endif

</div>
