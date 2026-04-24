<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_forms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_scan_form_id', 64)->nullable();
            $t->string('carrier', 48);
            $t->foreignId('from_address_id')->constrained('addresses')->restrictOnDelete();
            $t->string('form_pdf_s3_key', 255)->nullable();
            $t->json('tracking_codes')->nullable();
            $t->string('status', 16)->default('creating');
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['team_id', 'ep_scan_form_id']);
            $t->index(['team_id', 'carrier', 'from_address_id']);
        });

        Schema::create('pickups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_pickup_id', 64)->nullable();
            $t->string('reference', 64)->nullable();
            $t->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $t->foreignId('address_id')->constrained('addresses')->restrictOnDelete();
            $t->timestamp('min_datetime');
            $t->timestamp('max_datetime');
            $t->string('instructions', 500)->nullable();
            $t->boolean('is_account_address')->default(false);
            $t->string('carrier', 48)->nullable();
            $t->string('service', 48)->nullable();
            $t->string('confirmation', 64)->nullable();
            $t->unsignedInteger('cost_cents')->nullable();
            $t->string('status', 16)->default('unknown');
            $t->json('rates_snapshot')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['team_id', 'ep_pickup_id']);
            $t->index(['team_id', 'status', 'min_datetime']);
        });

        Schema::create('batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_batch_id', 64)->nullable();
            $t->string('reference', 64)->nullable();
            $t->string('state', 32)->default('creating');
            $t->unsignedInteger('num_shipments')->default(0);
            $t->foreignId('carrier_account_id')->nullable()->constrained('carrier_accounts')->nullOnDelete();
            $t->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $t->string('label_pdf_s3_key', 255)->nullable();
            $t->foreignId('scan_form_id')->nullable()->constrained('scan_forms')->nullOnDelete();
            $t->foreignId('pickup_id')->nullable()->constrained('pickups')->nullOnDelete();
            $t->json('status_summary')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['team_id', 'ep_batch_id']);
            $t->index(['team_id', 'state']);
        });

        Schema::create('batch_shipment', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $t->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $t->string('batch_status', 32)->nullable();
            $t->string('batch_message', 500)->nullable();
            $t->timestamps();
            $t->unique(['batch_id', 'shipment_id']);
        });

        // Back-patch shipments FKs for scan_form_id, batch_id, pickup_id
        Schema::table('shipments', function (Blueprint $t) {
            $t->foreign('scan_form_id')->references('id')->on('scan_forms')->nullOnDelete();
            $t->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            $t->foreign('pickup_id')->references('id')->on('pickups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $t) {
            $t->dropForeign(['pickup_id']);
            $t->dropForeign(['batch_id']);
            $t->dropForeign(['scan_form_id']);
        });
        Schema::dropIfExists('batch_shipment');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('pickups');
        Schema::dropIfExists('scan_forms');
    }
};
