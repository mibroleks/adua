{{-- 
Component: Application Form
File Path: resources/views/apply.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the student application form.
Allows programme selection, captures dynamic application fields,
and uploads required documents.

Status: ✅ Production Ready
Version: 4.4 (Step 3 fix + alert feedback)
--}}

@extends('layouts.app')

@section('title', 'Application Form')

@section('content')

<div class="admission-page admission-page--application">

    <div class="admission-atmosphere admission-atmosphere--left" aria-hidden="true"></div>
    <div class="admission-atmosphere admission-atmosphere--right" aria-hidden="true"></div>

    <main class="admission-shell">

        <header class="admission-page-header admission-page-header--application">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Admissions
            </div>
            <h1 class="admission-page-title">Start your application</h1>
            <p class="admission-page-description">
                Complete your application carefully. Your information will be reviewed
                by the admissions office after submission.
            </p>
        </header>

        <div class="application-workspace">

            {{-- Prepare document collections before stepper --}}
            @php
                $hasProgramme = isset($programmeId) && $programmeId;

                $globalDocs = collect();
                $programmeDocs = collect();

                if ($hasProgramme) {
                    $globalDocs = \App\Models\ApplicationDocumentType::whereNull('programme_id')
                        ->where('active', true)->get();

                    $programmeDocs = \App\Models\ApplicationDocumentType::where('programme_id', $programmeId)
                        ->where('active', true)->get();
                }

                $hasDocuments = $globalDocs->isNotEmpty() || $programmeDocs->isNotEmpty();
            @endphp

            {{-- Progress indicator --}}
            <div class="application-stepper" aria-label="Application progress">
                <div class="application-step application-step--active">
                    <span class="application-step__number">1</span>
                    <div><strong>Programme</strong><span>Choose your programme</span></div>
                </div>
                <span class="application-stepper__line" aria-hidden="true"></span>
                <div class="application-step {{ $hasProgramme ? 'application-step--active' : '' }}">
                    <span class="application-step__number">2</span>
                    <div><strong>Application</strong><span>Provide your information</span></div>
                </div>
                <span class="application-stepper__line" aria-hidden="true"></span>
                <div class="application-step {{ $hasDocuments ? 'application-step--active' : '' }}">
                    <span class="application-step__number">3</span>
                    <div><strong>Documents</strong><span>Upload requirements</span></div>
                </div>
            </div>

            {{-- Programme selection --}}
            <section class="admission-panel admission-panel--programme">
                <div class="admission-panel__header">
                    <div>
                        <span class="admission-panel__kicker">Step 1</span>
                        <h2 class="admission-panel__title">Choose your programme</h2>
                        <p class="admission-panel__description">
                            Select the programme you want to apply for. The application
                            fields will appear once a programme is selected.
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
                                @foreach(\App\Models\Programme::where('active', true)->where('application_enabled', true)->get() as $programme)
                                    <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->name }} @if($programme->degree_type) — {{ $programme->degree_type }} @endif
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

            {{-- Application form --}}
            @if($hasProgramme)
                <form method="POST" action="{{ route('application.store') }}" enctype="multipart/form-data" class="application-form">
                    @csrf
                    <input type="hidden" name="programme_id" value="{{ $programmeId }}">

                    {{-- General information --}}
                    @if(isset($globalFields) && $globalFields->count())
                        <section class="admission-panel">
                            <div class="admission-panel__header">
                                <div class="admission-panel__icon" aria-hidden="true"><span>01</span></div>
                                <div>
                                    <span class="admission-panel__kicker">Your information</span>
                                    <h2 class="admission-panel__title">General Information</h2>
                                    <p class="admission-panel__description">Provide your personal and general application information.</p>
                                </div>
                            </div>
                            <div class="admission-form-grid">
                                @foreach($globalFields as $field)
                                    <div class="admission-field @if(in_array($field->type ?? '', ['textarea','address'])) admission-field--full @endif">
                                        <label for="{{ $field->key }}" class="admission-field__label">
                                            {{ $field->label }} @if($field->required)<span class="admission-required">*</span>@endif
                                        </label>
                                        <x-form-field-input :field="$field" />
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Programme-specific information --}}
                    @if(isset($programmeFields) && $programmeFields->count())
                        <section class="admission-panel">
                            <div class="admission-panel__header">
                                <div class="admission-panel__icon" aria-hidden="true"><span>02</span></div>
                                <div>
                                    <span class="admission-panel__kicker">Programme details</span>
                                    <h2 class="admission-panel__title">Programme-Specific Information</h2>
                                    <p class="admission-panel__description">Complete the additional information required for your selected programme.</p>
                                </div>
                            </div>
                            <div class="admission-form-grid">
                                @foreach($programmeFields as $field)
                                    <div class="admission-field @if(in_array($field->type ?? '', ['textarea','address'])) admission-field--full @endif">
                                        <label for="{{ $field->key }}" class="admission-field__label">
                                            {{ $field->label }} @if($field->required)<span class="admission-required">*</span>@endif
                                        </label>
                                        <x-form-field-input :field="$field" />
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
                                                {{ $doc->name }} @if($doc->required)<span class="admission-required">*</span>@endif
                                            </label>
                                            <p class="document-upload__help">
                                                {{ $doc->allowed_file_types ? 'Accepted: '.implode(', ', $doc->allowed_file_types) : 'Upload a clear and readable document.' }}
                                                @if($doc->max_size)
                                                    <br>Max size: {{ $doc->max_size }} KB
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="document-upload__control">
                                        <input
                                            type="file"
                                            id="{{ $doc->key }}"
                                            name="documents[{{ $doc->key }}]"
                                            class="admission-file-input"
                                            data-filename-target="filename-{{ $doc->key }}"
                                            @if($doc->required) required @endif
                                        >
                                        <label for="{{ $doc->key }}" class="admission-file-button">
                                            Choose file
                                        </label>
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

                        {{-- Submit area --}}
                        <section class="application-submit-panel">
                            <div class="application-submit-copy">
                                <span class="application-submit-copy__eyebrow">Ready to continue?</span>
                                <h2>Save your application</h2>
                                <p>
                                    Your application will be saved as a draft. You can review
                                    and submit it from your application status page.
                                </p>
                            </div>

                            {{-- Feedback alerts --}}
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

                            <button
                                type="submit"
                                class="admission-button admission-button--primary admission-button--large"
                            >
                                <span>Save Draft</span>
                                <span aria-hidden="true">→</span>
                            </button>
                        </section>
                        </form>
                        @else
                            {{-- Empty programme state --}}
                            <section class="admission-empty-state">
                                <div class="admission-empty-state__icon" aria-hidden="true">01</div>
                                <h2>Select a programme to continue</h2>
                                <p>
                                    Once you choose a programme above, your application form
                                    and required documents will appear here.
                                </p>
                            </section>
                        @endif
                        </div>
                        </main>
                        </div>

                        @endsection

           