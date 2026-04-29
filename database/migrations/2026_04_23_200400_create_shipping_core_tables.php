<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_carrier_account_id', 64);
            $t->string('type', 48);
            $t->string('readable', 48);
            $t->string('description', 120)->nullable();
            $t->string('reference', 64)->nullable();
            $t->string('billing_type', 16)->nullable();
            $t->boolean('is_default')->default(false);
            $t->timestamp('validated_at')->nullable();
            $t->json('credentials_masked')->nullable();
            $t->json('settings')->nullable();
            $t->string('status', 16)->default('active');
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['team_id', 'ep_carrier_account_id']);
            $t->index(['team_id', 'type']);
        });

        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $t->string('ep_address_id', 64)->nullable()->index();
            $t->string('name', 120)->nullable();
            $t->string('company', 120)->nullable();
            $t->string('street1', 120);
            $t->string('street2', 120)->nullable();
            $t->string('city', 80)->nullable();
            $t->string('state', 40)->nullable();
            $t->string('zip', 20)->nullable();
            $t->char('country', 2);
            $t->string('phone', 24)->nullable();
            $t->string('email', 190)->nullable();
            $t->string('federal_tax_id', 24)->nullable();
            $t->string('state_tax_id', 24)->nullable();
            $t->boolean('residential')->nullable();
            $t->string('carrier_facility', 48)->nullable();
            $t->boolean('verified')->default(false);
            $t->timestamp('verified_at')->nullable();
            $t->json('verification')->nullable();
            $t->decimal('latitude', 9, 6)->nullable();
            $t->decimal('longitude', 9, 6)->nullable();
            $t->string('time_zone', 64)->nullable();
            $t->string('hash', 64)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['team_id', 'client_id']);
            $t->index(['team_id', 'verified']);
            $t->index(['team_id', 'hash']);
        });

        // Back-patch FKs that required addresses
        Schema::table('warehouses', function (Blueprint $t) {
            $t->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
        });
        Schema::table('clients', function (Blueprint $t) {
            $t->foreign('default_from_address_id')->references('id')->on('addresses')->nullOnDelete();
        });

        Schema::create('parcels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_parcel_id', 64)->nullable()->index();
            $t->string('predefined_package', 48)->nullable();
            $t->decimal('length_in', 7, 2)->nullable();
            $t->decimal('width_in', 7, 2)->nullable();
            $t->decimal('height_in', 7, 2)->nullable();
            $t->decimal('weight_oz', 8, 2);
            $t->json('line_items')->nullable();
            $t->timestamps();
            $t->index('team_id');
        });

        Schema::create('customs_infos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            // shipment_id FK added after shipments is created
            $t->unsignedBigInteger('shipment_id')->nullable()->index();
            $t->string('ep_customsinfo_id', 64)->nullable()->index();
            $t->string('contents_type', 32);
            $t->string('contents_explanation', 255)->nullable();
            $t->boolean('customs_certify')->default(true);
            $t->string('customs_signer', 120)->nullable();
            $t->string('eel_pfc', 48)->nullable();
            $t->string('non_delivery_option', 16)->default('return');
            $t->string('restriction_type', 48)->default('none');
            $t->string('restriction_comments', 255)->nullable();
            $t->string('declaration', 255)->nullable();
            $t->json('items')->nullable();
            $t->timestamps();
            $t->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customs_infos');
        Schema::dropIfExists('parcels');
        Schema::table('clients', function (Blueprint $t) { $t->dropForeign(['default_from_address_id']); });
        Schema::table('warehouses', function (Blueprint $t) { $t->dropForeign(['address_id']); });
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('carrier_accounts');
    }
};
