{{-- 
Component: Applications List
File Path: resources/views/applications/index.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays all applications belonging to the authenticated student.
Each row shows programme, status, submission date, and link to full dossier.
Supports multiple applications across cycles or degrees.

Status: ✅ Production Ready
Version: 1.0
--}}

@extends('layouts.portal')

@section('title', 'My Applications')

@section('content')

<div class="admission-page admission-page--list">

    <main class="admission-shell admission-shell--wide">

        <header class="admission-page-header">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Applications Overview
            </div>
            <h1 class="admission-page-title">My Applications</h1>
            <p class="admission-page-description">
                View all your applications across programmes and admission cycles.
            </p>
        </header>

        @if($applications->isEmpty())
            <section class="admission-empty-state">
                <div class="admission-empty-state__icon" aria-hidden="true">+</div>
                <h2>No applications found</h2>
                <p>You have not started an application yet. Begin by selecting your programme.</p>
                <x-portal-button variant="primary" href="{{ route('application.create') }}">
                    Start Application
                </x-portal-button>
            </section>
        @else
            <table class="admission-table">
                <thead>
                    <tr>
                        <th>Application No.</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                        <tr>
                            <td>{{ $application->application_number }}</td>
                            <td>{{ $application->programme->name }}</td>
                            <td>{{ str_replace('_', ' ', $application->status) }}</td>
                            <td>{{ $application->submitted_at?->format('d M Y') ?? 'Not submitted' }}</td>
                            <td>
                                <x-portal-button variant="secondary" href="{{ route('applications.show', $application) }}">
                                    View Dossier
                                </x-portal-button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </main>
</div>

@endsection
