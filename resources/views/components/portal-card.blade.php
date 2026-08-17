{{-- 
Component: Portal Card (Preset-Driven)
File Path: resources/views/components/portal-card.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable card component styled with semantic theme tokens.
Used for programme listings, dashboards, application steps, and general portal UI.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

<div {{ $attributes->merge([
    'class' => 'rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] shadow-sm transition hover:shadow-md'
]) }}>
    <div class="p-6">
        {{-- Card Header --}}
        @isset($title)
            <h3 class="text-lg font-bold theme-heading">
                {{ $title }}
            </h3>
        @endisset

        {{-- Card Subtitle --}}
        @isset($subtitle)
            <p class="mt-1 text-sm theme-muted">
                {{ $subtitle }}
            </p>
        @endisset

        {{-- Card Content --}}
        <div class="mt-4 text-sm theme-text">
            {{ $slot }}
        </div>

        {{-- Card Footer --}}
        @isset($footer)
            <div class="mt-6 border-t border-[var(--theme-border)] pt-4 text-xs theme-muted">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
