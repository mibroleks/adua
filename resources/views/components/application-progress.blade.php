{{-- 
Component: Application Progress (Preset-Driven)
File Path: resources/views/components/application-progress.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a progress tracker for the applicant journey.
Steps are dynamically styled with semantic theme tokens.
Ensures officers can configure steps without touching code.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'steps' => [],        // array of step labels
    'current' => null,    // current step key or index
])

<div class="w-full">
    <ol class="flex items-center justify-between">
        @foreach($steps as $index => $label)
            @php
                $isCompleted = $current !== null && $index < $current;
                $isActive = $current !== null && $index === $current;
            @endphp

            <li class="flex-1">
                <div class="flex flex-col items-center text-center">
                    <div class="
                        flex h-10 w-10 items-center justify-center rounded-full border-2
                        {{ $isCompleted ? 'bg-[var(--theme-success)] border-[var(--theme-success)] text-white' : '' }}
                        {{ $isActive ? 'border-[var(--theme-primary)] text-[var(--theme-primary)]' : '' }}
                        {{ !$isCompleted && !$isActive ? 'border-[var(--theme-border)] text-[var(--theme-muted)]' : '' }}
                    ">
                        {{ $index + 1 }}
                    </div>
                    <span class="mt-2 text-xs font-medium theme-heading">
                        {{ $label }}
                    </span>
                </div>
            </li>

            @if($index < count($steps) - 1)
                <div class="flex-1 border-t-2 
                    {{ $isCompleted ? 'border-[var(--theme-success)]' : 'border-[var(--theme-border)]' }}
                "></div>
            @endif
        @endforeach
    </ol>
</div>
