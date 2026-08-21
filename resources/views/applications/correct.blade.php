{{-- 
|-------------------------------------------------------------------------- 
| Application Correction Form 
|-------------------------------------------------------------------------- 
| File: resources/views/applications/correct.blade.php 
| Company: Ygrace Tech 
| Author: Ibrahim Olalekan 
|
| Purpose:
| Allows a student to correct and resubmit their application when
| admissions has requested a correction.
|
| Status: 🚦 Integration / Hardening
| Version: 1.2 (prefilled values + hidden programme_id)
--}}

@extends('layouts.portal')

@section('title', 'Correct Application')

@section('content')

<div class="admission-page admission-page--application">

    {{-- Decorative atmosphere --}}
    <div class="admission-atmosphere admission-atmosphere--left" aria-hidden="true"></div>
    <div class="admission-atmosphere admission-atmosphere--right" aria-hidden="true"></div>

    <main class="admission-shell">

        {{-- ================================================================
             PAGE HEADER
        ================================================================= --}}
        <header class="admission-page-header admission-page-header--application">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Admissions
            </div>
            <h1 class="admission-page-title">Correct your application</h1>
            <p class="admission-page-description">
                Update your application as requested by the admissions office. 
                Once saved, it will be resubmitted for review.
            </p>
        </header>

        {{-- ================================================================
             WORKSPACE
        ================================================================= --}}
        <div class="application-workspace">

            @php
                $hasProgramme = isset($programmeId) && filled($programmeId);
                $hasDocuments = ($globalDocs->isNotEmpty() || $programmeDocs->isNotEmpty());
            @endphp

            {{-- ============================================================
                 APPLICATION STEPPER
            ================================================================= --}}
            <div class="application-stepper" aria-label="Application progress">
                <div class="application-step application-step--active">
                    <span class="application-step__number">1</span>
                    <div>
                        <strong>Programme</strong>
                        <span>{{ $application->programme->name }}</span>
                    </div>
                </div>

                <span class="application-stepper__line" aria-hidden="true"></span>

                <div class="application-step application-step--active">
                    <span class="application-step__number">2</span>
                    <div>
                        <strong>Application</strong>
                        <span>Update your information</span>
                    </div>
                </div>

                <span class="application-stepper__line" aria-hidden="true"></span>

                <div class="application-step {{ $hasDocuments ? 'application-step--active' : '' }}">
                    <span class="application-step__number">3</span>
                    <div>
                        <strong>Documents</strong>
                        <span>Upload corrections</span>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 CORRECTION FORM
            ================================================================= --}}
            <form method="POST" action="{{ route('application.correct.update', $application) }}" enctype="multipart/form-data" class="application-form">
                @csrf

                {{-- Hidden programme_id to satisfy validation --}}
                <input type="hidden" name="programme_id" value="{{ $application->programme_id }}">

                {{-- General Information --}}
                @if(isset($globalFields) && $globalFields->count())
                    <section class="admission-panel">
                        <div class="admission-panel__header">
                            <div class="admission-panel__icon" aria-hidden="true"><span>01</span></div>
                            <div>
                                <span class="admission-panel__kicker">Your information</span>
                                <h2 class="admission-panel__title">General Information</h2>
                                <p class="admission-panel__description">
                                    Update your personal and general application information.
                                </p>
                            </div>
                        </div>
                        <div class="admission-form-grid">
                            @foreach($globalFields as $field)
                                @php
                                    $fieldType = $field->type ?? '';
                                    $isFullWidth = in_array($fieldType, ['textarea', 'address'], true);
                                    $existingValue = $application->fieldValues->firstWhere('form_field_id', $field->id)?->value;
                                @endphp
                                <div class="admission-field {{ $isFullWidth ? 'admission-field--full' : '' }}">
                                    <label for="{{ $field->key }}" class="admission-field__label">
                                        {{ $field->label }}
                                        @if($field->required)
                                            <span class="admission-required" aria-hidden="true">*</span>
                                        @endif
                                    </label>
                                    <x-form-field-input :field="$field" :value="$existingValue" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Programme-Specific Information --}}
                @if(isset($programmeFields) && $programmeFields->count())
                    <section class="admission-panel">
                        <div class="admission-panel__header">
                            <div class="admission-panel__icon" aria-hidden="true"><span>02</span></div>
                            <div>
                                <span class="admission-panel__kicker">Programme details</span>
                                <h2 class="admission-panel__title">Programme-Specific Information</h2>
                                <p class="admission-panel__description">
                                    Update the additional information required for your selected programme.
                                </p>
                            </div>
                        </div>

                        <div class="admission-form-grid">
                            @foreach($programmeFields as $field)
                                @php
                                    $fieldType = $field->type ?? '';
                                    $isFullWidth = in_array($fieldType, ['textarea', 'address'], true);
                                    $existingValue = $application->fieldValues->firstWhere('form_field_id', $field->id)?->value;
                                @endphp

                                <div class="admission-field {{ $isFullWidth ? 'admission-field--full' : '' }}">
                                    <label for="{{ $field->key }}" class="admission-field__label">
                                        {{ $field->label }}
                                        @if($field->required)
                                            <span class="admission-required" aria-hidden="true">*</span>
                                        @endif
                                    </label>
                                    <x-form-field-input :field="$field" :value="$existingValue" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Documents --}}
                @if($globalDocs->count() || $programmeDocs->count())
                    <section class="admission-panel admission-panel--documents">
                        <div class="admission-panel__header">
                            <div class="admission-panel__icon" aria-hidden="true"><span>03</span></div>
                            <div>
                                <span class="admission-panel__kicker">Supporting documents</span>
                                <h2 class="admission-panel__title">Required Documents</h2>
                                <p class="admission-panel__description">
                                    Upload corrected or missing documents as requested.
                                </p>
                            </div>
                        </div>

                        <div class="document-upload-list">
                            @foreach($globalDocs->merge($programmeDocs) as $doc)
                                @php
                                    $existingDoc = $application->documents->firstWhere('document_type_id', $doc->id);
                                @endphp
                                <div class="document-upload">
                                    <div class="document-upload__content">
                                        <div class="document-upload__icon" aria-hidden="true"><span>↥</span></div>
                                        <div class="document-upload__details">
                                            <label for="{{ $doc->key }}" class="document-upload__title">
                                                {{ $doc->name }}
                                                @if($doc->required)
                                                    <span class="admission-required" aria-hidden="true">*</span>
                                                @endif
                                            </label>
                                            <p class="document-upload__help">
                                                @if($doc->allowed_file_types)
                                                    Accepted: {{ implode(', ', $doc->allowed_file_types) }}
                                                @else
                                                    Upload a clear and readable document.
                                                @endif
                                                @if($doc->max_size)
                                                    <br>Max size: {{ $doc->max_size }} KB
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="document-upload__control">
                                        <input type="file"
                                            id="{{ $doc->key }}"
                                            name="documents[{{ $doc->key }}]"
                                            class="admission-file-input"
                                            data-filename-target="filename-{{ $doc->key }}">
                                        <label for="{{ $doc->key }}" class="admission-file-button">Choose file</label>
                                        <span id="filename-{{ $doc->key }}" class="admission-file-name">
                                            {{ $existingDoc?->original_name ?? 'No file selected' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Submit Correction --}}
                <section class="application-submit-panel">
                    <div class="application-submit-copy">
                        <span class="application-submit-copy__eyebrow">Ready to resubmit?</span>
                        <h2>Submit your corrected application</h2>
                        <p>
                            Your application will be updated and resubmitted to the admissions office for review.
                        </p>
                    </div>

                    {{-- Validation feedback --}}
                    @if(session('status'))
                        <x-alert variant="success" title="Application updated" :message="session('status')" />
                    @endif

                    @if($errors->any())
                        <x-alert variant="danger" title="There were some problems">
                            <ul class="mt-2 list-disc pl-5 text-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    {{-- Submit correction --}}
                    <button type="submit"
                        class="admission-button admission-button--primary admission-button--large">
                        Submit Correction
                    </button>
                </section>
            </form>
        </div>
    </main>
</div>

@endsection


