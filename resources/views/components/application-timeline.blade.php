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

<div class="portal-timeline">

    @foreach($events as $event)

        @php
            $status = $event['status'] ?? 'upcoming';
        @endphp

        <div class="portal-timeline__event">

            {{-- Timeline marker --}}
            <div class="portal-timeline__marker portal-timeline__marker--{{ $status }}">
                {{ strtoupper(substr($status, 0, 1)) }}
            </div>

            {{-- Timeline content --}}
            <div class="portal-timeline__content">

                <p class="portal-timeline__label">
                    {{ $event['label'] }}
                </p>

                @if(!empty($event['date']))
                    <p class="portal-timeline__date">
                        {{ $event['date']->toDayDateTimeString() }}
                    </p>
                @endif

            </div>

        </div>

    @endforeach

</div>
