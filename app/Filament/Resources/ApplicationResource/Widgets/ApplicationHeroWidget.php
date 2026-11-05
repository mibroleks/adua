<?php

/*
Component: Application Hero Widget
File Path: app/Filament/Widgets/ApplicationHeroWidget.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the premium application identity and status
summary at the top of the Application View page.

Responsibilities:
- Display application identity
- Display applicant and programme information
- Display current application status
- Provide a visual workflow-stage indicator

Design:
- Institution-theme aware
- Responsive
- Light and dark mode compatible

Status: Integration
Version: 1.0
*/

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Widgets\Widget;

class ApplicationHeroWidget extends Widget
{
    protected string $view = 'filament.widgets.application-hero-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * Current application record supplied by the ViewApplication page.
     */
    public ?Application $record = null;
}
