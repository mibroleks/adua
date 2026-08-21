{{--
Component: Alert
File Path: resources/views/components/alert.blade.php
--}}

@props([
    'variant' => 'info',
    'title' => null,
    'message' => null,
    'role' => 'alert',
])

@php
    $variant = in_array($variant, [
        'success',
        'warning',
        'danger',
        'info'
    ]) ? $variant : 'info';
@endphp

<div
    {{ $attributes->merge([
        'class' => "portal-alert portal-alert--{$variant}",
        'role' => $role,
    ]) }}
>
    @if($title)
        <h4 class="portal-alert__title">
            {{ $title }}
        </h4>
    @endif

    @if($message)
        <p class="portal-alert__message">
            {{ $message }}
        </p>
    @endif

    {{ $slot }}
</div>