<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Batch;
use App\Models\Claim;
use App\Models\Insurance;
use App\Models\Pickup;
use App\Models\ReturnRequest;
use App\Models\ScanForm;
use App\Models\Shipment;
use App\Models\Team;
use App\Models\Tracker;
use Laravel\Cashier\Cashier;
use App\Policies\AddressPolicy;
use App\Policies\BatchPolicy;
use App\Policies\ClaimPolicy;
use App\Policies\InsurancePolicy;
use App\Policies\PickupPolicy;
use App\Policies\ReturnRequestPolicy;
use App\Policies\ScanFormPolicy;
use App\Policies\ShipmentPolicy;
use App\Policies\TrackerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Team::class);

        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(ScanForm::class, ScanFormPolicy::class);
        Gate::policy(Pickup::class, PickupPolicy::class);
        Gate::policy(ReturnRequest::class, ReturnRequestPolicy::class);
        Gate::policy(Claim::class, ClaimPolicy::class);
        Gate::policy(Insurance::class, InsurancePolicy::class);
        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(Tracker::class, TrackerPolicy::class);
    }
}
