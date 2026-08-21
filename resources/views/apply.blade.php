{{-- 
Component: Application Form
File Path: resources/views/apply.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the authenticated applicant application form.

Allows:
- Programme selection
- Dynamic general application fields
- Programme-specific fields
- Required document uploads
- Draft saving
- Validation feedback

Important:
Saving this form creates/updates a DRAFT.
It does NOT submit the application.
Final lifecycle: DRAFT → SUBMITTED → UNDER_REVIEW → APPROVED / REJECTED

Status: ✅ Production Ready
Version: 6.0 (portal integration + safe draft workflow)
--}}

@extends('layouts.portal')

@section('title', 'Application Form')

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
            <h1 class="admission-page-title">Start your application</h1>
            <p class="admission-page-description">
                Complete your application carefully. Your information will
                be reviewed by the admissions office after submission.
            </p>
        </header>

        {{-- ================================================================
             WORKSPACE
        ================================================================= --}}
        <div class="application-workspace">

            @php
                $hasProgramme = isset($programmeId) && filled($programmeId);

                // Preserve the existing document-loading behaviour.
                $globalDocs = collect();
                $programmeDocs = collect();

                if ($hasProgramme) {
                    $globalDocs = \App\Models\ApplicationDocumentType::query()
                        ->whereNull('programme_id')
                        ->where('active', true)
                        ->get();

                    $programmeDocs = \App\Models\ApplicationDocumentType::query()
                        ->where('programme_id', $programmeId)
                        ->where('active', true)
                        ->get();
                }

                $hasDocuments = $globalDocs->isNotEmpty() || $programmeDocs->isNotEmpty();
            @endphp

            {{-- ============================================================
                 APPLICATION STEPPER
            ================================================================= --}}
            <div class="application-stepper" aria-label="Application progress">
                {{-- Step 1 --}}
                <div class="application-step application-step--active">
                    <span class="application-step__number">1</span>
                    <div>
                        <strong>Programme</strong>
                        <span>Choose your programme</span>
                    </div>
                </div>

                <span class="application-stepper__line" aria-hidden="true"></span>

                {{-- Step 2 --}}
                <div class="application-step {{ $hasProgramme ? 'application-step--active' : '' }}">
                    <span class="application-step__number">2</span>
                    <div>
                        <strong>Application</strong>
                        <span>Provide your information</span>
                    </div>
                </div>

                <span class="application-stepper__line" aria-hidden="true"></span>

                {{-- Step 3 --}}
                <div class="application-step {{ $hasDocuments ? 'application-step--active' : '' }}">
                    <span class="application-step__number">3</span>
                    <div>
                        <strong>Documents</strong>
                        <span>Upload requirements</span>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 PROGRAMME SELECTION
            ================================================================= --}}
            <section class="admission-panel admission-panel--programme">
                <div class="admission-panel__header">
                    <div>
                        <span class="admission-panel__kicker">Step 1</span>
                        <h2 class="admission-panel__title">Choose your programme</h2>
                        <p class="admission-panel__description">
                            Select the programme you want to apply for.
                            The application fields will appear once a programme is selected.
                        </p>
                    </div>
                </div>

                <form method="GET" action="{{ route('application.create') }}" class="application-programme-form">
                    <div class="admission-field">
                        <label for="programme_id" class="admission-field__label">
                            Programme <span class="admission-required" aria-hidden="true">*</span>
                        </label>
                        <div class="admission-select-wrap">
                            <select id="programme_id" name="programme_id"
                                class="admission-input admission-input--select"
                                onchange="this.form.submit()" aria-describedby="programme-help">
                                <option value="">Choose a programme</option>
                                @foreach(
                                    \App\Models\Programme::query()
                                        ->where('active', true)
                                        ->where('application_enabled', true)
                                        ->orderBy('name')
                                        ->get()
                                    as $programme
                                )
                                    <option value="{{ $programme->id }}"
                                        {{ (string) request('programme_id') === (string) $programme->id ? 'selected' : '' }}>
                                        {{ $programme->name }}
                                        @if($programme->degree_type)
                                            — {{ $programme->degree_type }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="programme-help" class="admission-field__help">
                            Choose the programme that matches your intended course of study.
                        </p>
                    </div>
                </form>
            </section>

            {{-- ============================================================
                 APPLICATION FORM
            ================================================================= --}}
            @if($hasProgramme)
                <form method="POST" action="{{ route('application.store') }}" enctype="multipart/form-data" class="application-form">
                    @csrf
                    <input type="hidden" name="programme_id" value="{{ $programmeId }}">

                    {{-- General Information --}}
                    @if(isset($globalFields) && $globalFields->count())
                        <section class="admission-panel">
                            <div class="admission-panel__header">
                                <div class="admission-panel__icon" aria-hidden="true"><span>01</span></div>
                                <div>
                                    <span class="admission-panel__kicker">Your information</span>
                                    <h2 class="admission-panel__title">General Information</h2>
                                    <p class="admission-panel__description">
                                        Provide your personal and general application information.
                                    </p>
                                </div>
                            </div>
                            <div class="admission-form-grid">
                                @foreach($globalFields as $field)
                                    @php
                                        $fieldType = $field->type ?? '';
                                        $isFullWidth = in_array($fieldType, ['textarea', 'address'], true);
                                    @endphp
                                    <div class="admission-field {{ $isFullWidth ? 'admission-field--full' : '' }}">
                                        <label for="{{ $field->key }}" class="admission-field__label">
                                            {{ $field->label }}
                                            @if($field->required)
                                                <span class="admission-required" aria-hidden="true">*</span>
                                            @endif
                                        </label>
                                        <x-form-field-input :field="$field" />
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
                                        Complete the additional information required for your selected programme.
                                    </p>
                                </div>
                            </div>

                            <div class="admission-form-grid">
                                @foreach($programmeFields as $field)
                                    @php
                                        $fieldType = $field->type ?? '';
                                        $isFullWidth = in_array($fieldType, ['textarea', 'address'], true);
                                    @endphp

                                    <div class="admission-field {{ $isFullWidth ? 'admission-field--full' : '' }}">
                                        <label for="{{ $field->key }}" class="admission-field__label">
                                            {{ $field->label }}
                                            @if($field->required)
                                                <span class="admission-required" aria-hidden="true">*</span>
                                            @endif
                                        </label>
                                        <x-form-field-input :field="$field" />
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- ====================================================
                         DOCUMENTS
                    ================================================================= --}}
                    @if($globalDocs->count() || $programmeDocs->count())
                        <section class="admission-panel admission-panel--documents">
                            <div class="admission-panel__header">
                                <div class="admission-panel__icon" aria-hidden="true"><span>03</span></div>
                                <div>
                                    <span class="admission-panel__kicker">Supporting documents</span>
                                    <h2 class="admission-panel__title">Required Documents</h2>
                                    <p class="admission-panel__description">
                                        Upload clear and readable copies of the documents required for your application.
                                    </p>
                                </div>
                            </div>

                            <div class="document-upload-list">
                                @foreach($globalDocs->merge($programmeDocs) as $doc)
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
                                                data-filename-target="filename-{{ $doc->key }}"
                                                @if($doc->required) required @endif>
                                            <label for="{{ $doc->key }}" class="admission-file-button">Choose file</label>
                                            <span id="filename-{{ $doc->key }}" class="admission-file-name">No file selected</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="admission-notice">
                                <span class="admission-notice__icon" aria-hidden="true">i</span>
                                <p>
                                    Make sure every uploaded document is clear, complete and belongs to you.
                                    You can review your application before final submission.
                                </p>
                            </div>
                        </section>
                    @endif

                    {{-- ====================================================
                         SUBMIT / SAVE DRAFT
                    ================================================================= --}}
                    <section class="application-submit-panel">
                        <div class="application-submit-copy">
                            <span class="application-submit-copy__eyebrow">Ready to continue?</span>
                            <h2>Save your application</h2>
                            <p>
                                Your application will be saved as a draft along with uploaded documents.
                                You can review and submit it from your application status page.
                            </p>
                        </div>

                        {{-- Validation feedback --}}
                        @if(session('status'))
                            <x-alert variant="success" title="Application saved" :message="session('status')" />
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

                        {{-- Save draft --}}
                        <button type="submit"
                            class="admission-button admission-button--primary admission-button--large">
                            Save Draft
                        </button>
                    </section>
                </form>
            @endif
        </div>
    </main>
</div>
@endsection

