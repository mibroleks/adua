<?php

namespace App\Providers;

use App\Models\Application;
use App\Policies\ApplicationPolicy;
use App\Models\Payment;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Payment::class => PaymentPolicy::class, // ✅ Explicitly register PaymentPolicy
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Explicit Gate registration for clarity
        Gate::policy(Payment::class, PaymentPolicy::class);
    }
}
