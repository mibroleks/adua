{{-- 
Component: Form Field Input (Preset-Driven)
File Path: resources/views/components/form-field-input.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable component to render dynamic application form fields.
Supports multiple input types (text, email, tel, textarea, date, number, select, radio, checkbox).
Used inside apply.blade.php for both global and programme-specific fields.

Status: ✅ Production Ready
Version: 2.3 (added email & tel support, fallback handler)
--}}

@props(['field'])

@php
    // Decode options if stored as JSON string
    $options = is_string($field->options) ? json_decode($field->options, true) : $field->options;
@endphp

@if($field->type === 'text')
    <input type="text" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}" placeholder="{{ $field->placeholder }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
@elseif($field->type === 'email')
    <input type="email" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}" placeholder="{{ $field->placeholder }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
@elseif($field->type === 'tel')
    <input type="tel" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}" placeholder="{{ $field->placeholder }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
@elseif($field->type === 'textarea')
    <textarea id="{{ $field->key }}" name="{{ $field->key }}"
        placeholder="{{ $field->placeholder }}"
        class="w-full min-h-32 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>{{ old($field->key) }}</textarea>
@elseif($field->type === 'date')
    <input type="date" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
@elseif($field->type === 'number')
    <input type="number" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
@elseif($field->type === 'select' && is_array($options))
    <select id="{{ $field->key }}" name="{{ $field->key }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text focus:border-[var(--theme-primary)] focus:ring-[var(--theme-focus)]"
        @if($field->required) required @endif>
        <option value="">Select an option</option>
        @foreach($options as $option)
            <option value="{{ $option }}" @selected(old($field->key) == $option)>{{ $option }}</option>
        @endforeach
    </select>
@elseif($field->type === 'radio' && is_array($options))
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($options as $option)
            <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 transition hover:border-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft)]">
                <input type="radio" name="{{ $field->key }}" value="{{ $option }}" @checked(old($field->key) == $option)>
                <span class="text-sm font-medium theme-heading">{{ $option }}</span>
            </label>
        @endforeach
    </div>
@elseif($field->type === 'checkbox' && is_array($options))
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($options as $option)
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 transition hover:border-[var(--theme-primary)]">
                <input type="checkbox" name="{{ $field->key }}[]" value="{{ $option }}" @checked(collect(old($field->key))->contains($option))>
                <span class="text-sm font-medium theme-heading">{{ $option }}</span>
            </label>
        @endforeach
    </div>
@else
    {{-- Fallback: render as text input if type is unknown --}}
    <input type="text" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key) }}" placeholder="{{ $field->placeholder }}"
        class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm theme-text"
        @if($field->required) required @endif>
@endif

@if($field->help_text)
    <p class="mt-2 text-xs leading-5 theme-muted">{{ $field->help_text }}</p>
@endif
