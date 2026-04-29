<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $t->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $t->foreignId('station_id')->nullable()->constrained('stations')->nullOnDelete();
            $t->string('ep_shipment_id', 64)->nullable();
            $t->string('reference', 64)->nullable();
            $t->string('status', 24)->default('requested');
            $t->string('status_detail', 48)->nullable();
            $t->foreignId('to_address_id')->constrained('addresses')->restrictOnDelete();
            $t->foreignId('from_address_id')->constrained('addresses')->restrictOnDelete();
            $t->foreignId('return_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $t->foreignId('parcel_id')->constrained('parcels')->restrictOnDelete();
            $t->foreignId('customs_info_id')->nullable()->constrained('customs_infos')->nullOnDelete();
            $t->string('ep_endshipper_id', 64)->nullable();
            $t->boolean('is_return')->default(false);
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->string('tracking_code', 64)->nullable()->index();
            $t->string('carrier', 48)->nullable();
            $t->string('service', 48)->nullable();
            $t->json('selected_rate')->nullable();
            $t->json('rates_snapshot')->nullable();
            $t->json('options')->nullable();
            $t->string('label_s3_key', 255)->nullable();
            $t->string('label_pdf_s3_key', 255)->nullable();
            $t->string('label_zpl_s3_key', 255)->nullable();
            $t->string('label_epl2_s3_key', 255)->nullable();
            $t->json('forms')->nullable();
            $t->unsignedInteger('cost_cents')->nullable();
            $t->unsignedInteger('insurance_cents')->nullable();
            $t->unsignedInteger('declared_value_cents')->nullable();
            $t->char('currency', 3)->default('USD');
            $t->json('fees')->nullable();
            $t->string('refund_status', 16)->nullable();
            $t->timestamp('refund_submitted_at')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->timestamp('packed_at')->nullable();
            // scan_form_id / batch_id / pickup_id FKs added in their own migrations below
            $t->unsignedBigInteger('scan_form_id')->nullable()->index();
            $t->unsignedBigInteger('batch_id')->nullable()->index();
            $t->unsignedBigInteger('pickup_id')->nullable()->index();
            $t->json('messages')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['team_id', 'ep_shipment_id']);
            $t->index(['team_id', 'status']);
            $t->index(['team_id', 'client_id', 'status']);
            $t->index(['team_id', 'assigned_to', 'status']);
            $t->index(['team_id', 'created_at']);
            $t->index(['team_id', 'is_return']);
        });

        // Back-patch customs_infos.shipment_id FK
        Schema::table('customs_infos', function (Blueprint $t) {
            $t->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
        });

        Schema::create('shipment_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $t->string('type', 48);
            $t->json('payload')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['shipment_id', 'created_at']);
        });

        Schema::create('approvals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('rate_id', 64)->nullable();
            $t->json('rate_snapshot')->nullable();
            $t->unsignedInteger('cost_cents');
            $t->string('reason', 32);
            $t->string('note', 500)->nullable();
            $t->string('status', 16)->default('pending');
            $t->string('decline_reason', 500)->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
            $t->index(['team_id', 'status']);
            $t->index(['approver_id', 'status']);
        });

        Schema::create('trackers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $t->string('ep_tracker_id', 64);
            $t->string('tracking_code', 64);
            $t->string('carrier', 48);
            $t->string('status', 24);
            $t->string('status_detail', 48)->nullable();
            $t->timestamp('est_delivery_date')->nullable();
            $t->string('public_url', 255)->nullable();
            $t->string('signed_by', 120)->nullable();
            $t->decimal('weight_oz', 8, 2)->nullable();
            $t->timestamp('last_event_at')->nullable();
            $t->boolean('is_return')->default(false);
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['team_id', 'ep_tracker_id']);
            $t->unique(['team_id', 'tracking_code', 'carrier']);
            $t->index(['team_id', 'status', 'status_detail']);
        });

        Schema::create('tracker_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tracker_id')->constrained('trackers')->cascadeOnDelete();
            $t->string('message', 255);
            $t->string('status', 24);
            $t->string('status_detail', 48)->nullable();
            $t->string('source', 48)->nullable();
            $t->timestamp('event_datetime');
            $t->json('location')->nullable();
            $t->string('confidence', 8)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['tracker_id', 'event_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_events');
        Schema::dropIfExists('trackers');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('shipment_events');
        Schema::table('customs_infos', function (Blueprint $t) { $t->dropForeign(['shipment_id']); });
        Schema::dropIfExists('shipments');
    }
};
