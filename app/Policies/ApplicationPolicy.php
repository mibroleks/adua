<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOfficer() || $user->isStudent();
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isOfficer()) {
            return true;
        }
        return $user->isStudent() && $user->id === $application->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    public function submit(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    public function pay(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && in_array($application->application_status, [
                Application::STATUS_DRAFT,
                Application::STATUS_SUBMITTED,
            ], true);
    }

    public function decide(User $user, Application $application): bool
    {
        return $user->isOfficer();
    }

    public function uploadDocument(User $user, Application $application): bool
    {
        return $user->isStudent() && $user->id === $application->user_id;
    }

    /**
     * Only allow replacement when the document is REJECTED.
     */
    public function replaceDocument(User $user, Application $application, ApplicationDocument $document): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $document->application_id === $application->id
            && $document->status === 'REJECTED';
    }

    public function viewDocument(User $user, Application $application, ApplicationDocument $document): bool
    {
        if ($user->isOfficer()) {
            return true;
        }
        return $user->isStudent()
            && $user->id === $application->user_id
            && $document->application_id === $application->id;
    }

    /**
     * Allow correction when status = CORRECTION_REQUIRED.
     */
    public function correct(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_CORRECTION_REQUIRED;
    }

    public function restore(User $user, Application $application): bool
    {
        return false;
    }

    public function forceDelete(User $user, Application $application): bool
    {
        return false;
    }
}
