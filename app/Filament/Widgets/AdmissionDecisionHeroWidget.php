<?php

/*
Component: Admission Decision Hero Widget
File Path: app/Filament/Widgets/AdmissionDecisionHeroWidget.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the premium identity and institutional outcome summary
at the top of the Admission Decision View page.

Responsibilities:
- Display the application reference and applicant identity.
- Display the application-processing decision separately.
- Display the actual institutional admission outcome.
- Identify the officers responsible for both decisions.
- Display important decision dates.
- Provide a visual institutional admission workflow indicator.

Design:
- Institution-theme aware.
- Responsive.
- Light and dark mode compatible.
- Consistent with the Application Hero experience.

Architecture:
- Presentation only; does not modify decision records.
- AdmissionService remains responsible for outcome changes.
- Application decisions and admission outcomes remain separate.
- Admission history remains independently auditable.

Status: Implementation in progress
Version: 1.1
*/

namespace App\Filament\Widgets;

use App\Models\AdmissionDecision;
use Filament\Widgets\Widget;

class AdmissionDecisionHeroWidget extends Widget
{
    /**
     * Disable Filament's default lazy loading for this widget.
     *
     * The hero must mount as a normal Livewire component so that
     * its component root and snapshot remain correctly aligned.
     */
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.admission-decision-hero-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * Current admission decision record supplied by the View page.
     */
    public ?AdmissionDecision $record = null;
}
