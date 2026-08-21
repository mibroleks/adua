{{-- 
Component: Application Progress
File Path: resources/views/components/application-progress.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a progress tracker for application stages.
Supports both simple step arrays and detailed stage objects with status + date.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.1 (active marker background enhancement)
--}}

@props([
    'steps' => [],
    'current' => null,
    'stages' => [],
])

<div class="application-progress">

    @if(!empty($stages))

        <ol class="application-progress__track">

            @foreach($stages as $index => $stage)

                @php
                    $status = $stage['status'] ?? 'pending';
                @endphp

                <li class="application-progress__stage">

                    <div class="application-progress__content">

                        <div class="
                            application-progress__marker
                            application-progress__marker--{{ $status }}
                        ">
                            {{ $index + 1 }}
                        </div>

                        <span class="application-progress__label">
                            {{ $stage['label'] }}
                        </span>

                        @if(!empty($stage['date']))
                            <span class="application-progress__date">
                                {{ \Carbon\Carbon::parse($stage['date'])->format('d M Y') }}
                            </span>
                        @endif

                    </div>

                </li>

                @if($index < count($stages) - 1)
                    <div class="
                        application-progress__connector
                        {{ $status === 'completed'
                            ? 'application-progress__connector--completed'
                            : ''
                        }}
                    "></div>
                @endif

            @endforeach

        </ol>

    @else

        <ol class="application-progress__track">

            @foreach($steps as $index => $label)

                @php
                    $isCompleted = $current !== null && $index < $current;
                    $isActive = $current !== null && $index === $current;
                @endphp

                <li class="application-progress__stage">

                    <div class="application-progress__content">

                        <div class="
                            application-progress__marker
                            {{ $isCompleted
                                ? 'application-progress__marker--completed'
                                : ''
                            }}
                            {{ $isActive
                                ? 'application-progress__marker--active'
                                : ''
                            }}
                        ">
                            {{ $index + 1 }}
                        </div>

                        <span class="application-progress__label">
                            {{ $label }}
                        </span>

                    </div>

                </li>

                @if($index < count($steps) - 1)

                    <div class="
                        application-progress__connector
                        {{ $isCompleted
                            ? 'application-progress__connector--completed'
                            : ''
                        }}
                    "></div>

                @endif

            @endforeach

        </ol>

    @endif

</div>
