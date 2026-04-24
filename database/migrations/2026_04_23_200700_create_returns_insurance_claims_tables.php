<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $t->foreignId('original_shipment_id')->constrained('shipments')->restrictOnDelete();
            $t->foreignId('return_shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $t->string('reason', 64)->nullable();
            $t->json('items')->nullable();
            $t->string('photos_s3_key_prefix', 255)->nullable();
            $t->string('status', 24)->default('requested');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->boolean('auto_refund')->default(false);
            $t->string('refund_status', 16)->nullable();
            $t->unsignedInteger('refund_amount_cents')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['team_id', 'status']);
            $t->index('original_shipment_id');
        });

        Schema::create('insurances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $t->string('ep_insurance_id', 64)->nullable();
            $t->string('provider', 32)->nullable();
            $t->string('tracking_code', 64)->nullable();
            $t->string('carrier', 48)->nullable();
            $t->unsignedInteger('amount_cents');
            $t->unsignedInteger('fee_cents')->nullable();
            $t->char('currency', 3)->default('USD');
            $t->string('status', 16)->default('new');
            $t->string('reference', 64)->nullable();
            $t->json('messages')->nullable();
            $t->timestamps();
            $t->unique(['team_id', 'ep_insurance_id']);
            $t->index(['team_id', 'status']);
        });

        Schema::create('claims', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('shipment_id')->constrained('shipments')->restrictOnDelete();
            $t->foreignId('insurance_id')->nullable()->constrained('insurances')->nullOnDelete();
            $t->string('ep_claim_id', 64)->nullable();
            $t->string('type', 24);
            $t->unsignedInteger('amount_cents');
            $t->unsignedInteger('recovered_cents')->nullable();
            $t->char('currency', 3)->default('USD');
            $t->text('description');
            $t->string('evidence_s3_key_prefix', 255)->nullable();
            $t->string('state', 16)->default('open');
            $t->json('timeline')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->string('close_reason', 255)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['team_id', 'state']);
            $t->index('shipment_id');
            $t->index('insurance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
        Schema::dropIfExists('insurances');
        Schema::dropIfExists('returns');
    }
};
