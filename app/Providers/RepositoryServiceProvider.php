<?php

namespace App\Providers;

use App\Repositories\Address\AddressRepo;
use App\Repositories\Care\ClaimRepo;
use App\Repositories\Care\InsuranceRepo;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\Client\ClientRepo;
use App\Repositories\Contact\ContactSubmissionRepo;
use App\Repositories\Infra\AccessRequestRepo;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\Infra\InvitationRepo;
use App\Repositories\Infra\ReportRepo;
use App\Repositories\Infra\WebhookEventRepo;
use App\Repositories\Operations\BatchRepo;
use App\Repositories\Operations\PickupRepo;
use App\Repositories\Operations\ScanFormRepo;
use App\Repositories\Shipping\ApprovalRepo;
use App\Repositories\Shipping\ParcelRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Team\TeamRepo;
use App\Repositories\Tracker\TrackerEventRepo;
use App\Repositories\Tracker\TrackerRepo;
use App\Repositories\User\RoleRepo;
use App\Repositories\User\RoleRightRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\ServiceProvider;

/**
 * Every repo in the app binds here as a singleton. Repos are stateless and share
 * a blank Eloquent model instance, so singleton binding avoids per-request
 * construction and makes swapping fakes in tests trivial.
 *
 * Repos are the ONLY place that touches Eloquent models directly — controllers
 * and actions ask the container for the repo they need.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    private const SINGLETONS = [
        // Identity
        UserRepo::class,
        RoleRepo::class,
        RoleRightRepo::class,
        TeamRepo::class,

        // Shipping
        AddressRepo::class,
        ParcelRepo::class,
        ShipmentRepo::class,
        ShipmentEventRepo::class,
        ApprovalRepo::class,
        TrackerRepo::class,
        TrackerEventRepo::class,

        // Operations
        BatchRepo::class,
        ScanFormRepo::class,
        PickupRepo::class,

        // Customer care
        ReturnRequestRepo::class,
        ClaimRepo::class,
        InsuranceRepo::class,

        // Business
        ClientRepo::class,

        // Infra / public surface
        ContactSubmissionRepo::class,
        AuditLogRepo::class,
        InvitationRepo::class,
        WebhookEventRepo::class,
        AccessRequestRepo::class,
        ReportRepo::class,
    ];

    public function register(): void
    {
        foreach (self::SINGLETONS as $class) {
            $this->app->singleton($class);
        }
    }
}
