{{-- 
Component: Form Field Input (Preset-Driven)
File Path: resources/views/components/form-field-input.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable component to render dynamic application form fields.
Supports multiple input types (text, email, tel, textarea, date, number, select, radio, checkbox).
Now supports prefilled values via :value prop.
--}}

@props(['field', 'value' => null])

@php
    // Decode options if stored as JSON string
    $options = is_string($field->options) ? json_decode($field->options, true) : $field->options;
@endphp

@if($field->type === 'text')
    <input type="text" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}" placeholder="{{ $field->placeholder }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@elseif($field->type === 'email')
    <input type="email" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}" placeholder="{{ $field->placeholder }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@elseif($field->type === 'tel')
    <input type="tel" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}" placeholder="{{ $field->placeholder }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@elseif($field->type === 'textarea')
    <textarea id="{{ $field->key }}" name="{{ $field->key }}"
        placeholder="{{ $field->placeholder }}"
        class="portal-form-field portal-form-field--textarea"
        @if($field->required) required @endif>{{ old($field->key, $value) }}</textarea>
@elseif($field->type === 'date')
    <input type="date" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@elseif($field->type === 'number')
    <input type="number" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@elseif($field->type === 'select' && is_array($options))
    <select id="{{ $field->key }}" name="{{ $field->key }}"
        class="portal-form-field"
        @if($field->required) required @endif>
        <option value="">Select an option</option>
        @foreach($options as $option)
            <option value="{{ $option }}" 
                @selected(old($field->key, $value) == $option)>
                {{ $option }}
            </option>
        @endforeach
    </select>
@elseif($field->type === 'radio' && is_array($options))
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($options as $option)
            <label class="portal-form-option">
                <input type="radio" name="{{ $field->key }}" value="{{ $option }}" 
                    @checked(old($field->key, $value) == $option)>
                <span class="portal-form-option__label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
@elseif($field->type === 'checkbox' && is_array($options))
    @php
        $selectedValues = collect(old($field->key, $value ? json_decode($value, true) : []));
    @endphp
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($options as $option)
            <label class="portal-form-option">
                <input type="checkbox" name="{{ $field->key }}[]" value="{{ $option }}" 
                    @checked($selectedValues->contains($option))>
                <span class="portal-form-option__label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
@else
    {{-- Fallback: render as text input if type is unknown --}}
    <input type="text" id="{{ $field->key }}" name="{{ $field->key }}"
        value="{{ old($field->key, $value) }}" placeholder="{{ $field->placeholder }}"
        class="portal-form-field"
        @if($field->required) required @endif>
@endif

@if($field->help_text)
    <p class="portal-form-help">{{ $field->help_text }}</p>
@endif
