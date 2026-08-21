{{-- 
Component: Portal Progress
File Path: resources/views/components/portal-progress.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable progress component for the student portal.
Displays sequential application/admission stages with active/completed indicators.
Ensures consistent journey visualization across all views.

Status: ✅ Production Ready
Version: 1.0
--}}

@props([
    'stages' => [], // array of ['label' => 'Application Submitted', 'status' => 'completed|active|pending', 'date' => '2026-08-19']
])

<ul {{ $attributes->merge(['class' => 'portal-progress flex flex-col gap-4']) }}>
    @foreach($stages as $stage)
        @php
            $classes = match($stage['status']) {
                'completed' => 'border-[var(--theme-success)] text-[var(--theme-success)]',
                'active'    => 'border-[var(--theme-info)] text-[var(--theme-info)]',
                default     => 'border-[var(--theme-muted)] text-[var(--theme-muted)]',
            };
        @endphp

        <li class="flex items-center gap-3">
            <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $classes }}">
                @if($stage['status'] === 'completed')
                    ✓
                @elseif($stage['status'] === 'active')
                    ●
                @else
                    ○
                @endif
            </span>
            <div class="flex flex-col">
                <span class="font-medium">{{ $stage['label'] }}</span>
                @if(!empty($stage['date']))
                    <span class="text-xs theme-muted">{{ \Carbon\Carbon::parse($stage['date'])->format('d M Y') }}</span>
                @endif
            </div>
        </li>
    @endforeach
</ul>
