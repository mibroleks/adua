<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any applications.
     */
    public function viewAny(User $user): bool
    {
        // Officers can list all applications.
        // Students can list their own applications.
        return $user->isOfficer() || $user->isStudent();
    }

    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->isOfficer()) {
            return true;
        }
        return $user->isStudent() && $user->id === $application->user_id;
    }

    /**
     * Determine whether the user can create applications.
     */
    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    /**
     * Determine whether the user can update the application.
     */
    public function update(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can submit the application.
     */
    public function submit(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && $application->application_status === Application::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can pay for the application.
     */
    public function pay(User $user, Application $application): bool
    {
        return $user->isStudent()
            && $user->id === $application->user_id
            && in_array($application->application_status, [
                Application::STATUS_DRAFT,
                Application::STATUS_SUBMITTED,
            ], true);
    }

    /**
     * Determine whether the user can restore the application.
     */
    public function restore(User $user, Application $application): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the application.
     */
    public function forceDelete(User $user, Application $application): bool
    {
        return false;
    }
}
