<?php

namespace App\Providers;

use App\Listeners\LogMailSent;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Document;
use App\Models\Insurer;
use App\Models\Invoice;
use App\Models\Policy;
use App\Models\Quote;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Observers\StatusTrackingObserver;
use App\Policies\ClaimPolicy;
use App\Policies\ClientPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\InsurerPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PolicyPolicy;
use App\Policies\QuotePolicy;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerObservers();
        $this->registerEventListeners();
        $this->defineGates();
    }

    /**
     * Register authorization policies.
     */
    private function registerPolicies(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Insurer::class, InsurerPolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
        Gate::policy(Policy::class, PolicyPolicy::class);
        Gate::policy(Claim::class, ClaimPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
    }

    /**
     * Register event listeners.
     */
    private function registerEventListeners(): void
    {
        Event::listen(MessageSent::class, LogMailSent::class);
    }

    /**
     * Register model observers.
     */
    private function registerObservers(): void
    {
        // Audit trail observer
        Client::observe(AuditableObserver::class);
        Insurer::observe(AuditableObserver::class);
        Quote::observe(AuditableObserver::class);
        Policy::observe(AuditableObserver::class);
        Claim::observe(AuditableObserver::class);
        Invoice::observe(AuditableObserver::class);

        // Status history tracking observer
        Quote::observe(StatusTrackingObserver::class);
        Policy::observe(StatusTrackingObserver::class);
        Claim::observe(StatusTrackingObserver::class);
    }

    /**
     * Define authorization gates for role-based access.
     */
    private function defineGates(): void
    {
        // Dashboard access - admin and super_user only
        Gate::define('access-dashboard', function (User $user) {
            return $user->isAdmin() || $user->isSuperUser();
        });

        // User management - admin and super_user
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin() || $user->isSuperUser();
        });

        // Invitation management - admin and super_user
        Gate::define('manage-invitations', function (User $user) {
            return $user->isAdmin() || $user->isSuperUser();
        });

        // Super admin only actions
        Gate::define('super-admin', function (User $user) {
            return $user->isSuperUser();
        });
    }
}
