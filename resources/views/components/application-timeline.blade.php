{{-- 
Component: Application Timeline (Preset-Driven)
File Path: resources/views/components/application-timeline.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a timeline of application milestones.
Steps are dynamically styled with semantic theme tokens.
Ensures officers can configure milestones without touching code.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'events' => [], // array of ['label' => 'Submitted', 'date' => Carbon instance, 'status' => 'completed|active|upcoming']
])

<div class="space-y-6">
    @foreach($events as $event)
        @php
            $status = $event['status'] ?? 'upcoming';
            $classes = match($status) {
                'completed' => 'border-[var(--theme-success)] bg-[var(--theme-success-soft)] text-[var(--theme-success)]',
                'active'    => 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',
                default     => 'border-[var(--theme-border)] bg-[var(--theme-surface)] text-[var(--theme-muted)]',
            };
        @endphp

        <div class="flex items-start gap-4">
            {{-- Timeline marker --}}
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 {{ $classes }}">
                <span class="text-xs font-bold">
                    {{ strtoupper(substr($status, 0, 1)) }}
                </span>
            </div>

            {{-- Timeline content --}}
            <div class="flex-1">
                <p class="text-sm font-semibold theme-heading">
                    {{ $event['label'] }}
                </p>
                @if(!empty($event['date']))
                    <p class="text-xs theme-muted">
                        {{ $event['date']->toDayDateTimeString() }}
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>
