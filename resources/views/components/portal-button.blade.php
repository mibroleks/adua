{{-- 
Component: Portal Button
File:
resources/views/components/portal-button.blade.php
--}}

@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'size' => 'default',
])

@php
    $variantClass = match($variant) {
        'secondary' => 'portal-btn--secondary',
        'ghost'     => 'portal-btn--ghost',
        'danger'    => 'portal-btn--danger',
        'success'   => 'portal-btn--success',
        default     => 'portal-btn--primary',
    };

    $sizeClass = match($size) {
        'small' => 'portal-btn--small',
        'large' => 'portal-btn--large',
        default => '',
    };

    $classes = trim(
        "portal-btn {$variantClass} {$sizeClass}"
    );
@endphp

@if($href)

    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>

@else

    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>

@endif