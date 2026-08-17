@props([
    'id',
    'title' => null,
    'eyebrow' => null,
    'size' => 'default',
])

@php
    $sizeClass = match($size) {
        'large' => 'portal-modal--large',
        'fullscreen' => 'portal-modal--fullscreen',
        'media' => 'portal-modal--media',
        default => '',
    };
@endphp

<div
    id="{{ $id }}"
    class="portal-modal {{ $sizeClass }}"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        class="portal-modal__backdrop"
        data-modal-close="{{ $id }}"
    ></div>

    {{-- Dialog --}}
    <div
        class="portal-modal__dialog"
        role="document"
    >

        {{-- Header --}}
        @if($title || $eyebrow)

            <div class="portal-modal__header">

                <div>

                    @if($eyebrow)
                        <div class="portal-modal__eyebrow">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if($title)
                        <h2 class="portal-modal__title">
                            {{ $title }}
                        </h2>
                    @endif

                </div>

                <button
                    type="button"
                    class="portal-modal__close"
                    data-modal-close="{{ $id }}"
                    aria-label="Close"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>

            </div>

        @endif

        {{-- Body --}}
        <div class="portal-modal__body">
            {{ $slot }}
        </div>

        {{-- Optional Footer --}}
        @isset($footer)

            <div class="portal-modal__footer">
                {{ $footer }}
            </div>

        @endisset

    </div>
</div>