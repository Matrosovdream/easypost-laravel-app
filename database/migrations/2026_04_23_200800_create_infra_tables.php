<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('ep_report_id', 64)->nullable();
            $t->string('type', 32);
            $t->date('start_date');
            $t->date('end_date');
            $t->string('status', 16)->default('queued');
            $t->string('s3_key', 255)->nullable();
            $t->json('columns')->nullable();
            $t->json('additional_columns')->nullable();
            $t->boolean('send_email')->default(false);
            $t->timestamp('url_expires_at')->nullable();
            $t->string('error', 500)->nullable();
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['team_id', 'type', 'status']);
        });

        Schema::create('notification_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $t->foreignId('tracker_id')->nullable()->constrained('trackers')->nullOnDelete();
            $t->string('channel', 16);
            $t->string('template', 64);
            $t->string('recipient', 190);
            $t->string('subject', 255)->nullable();
            $t->string('body_rendered_s3_key', 255)->nullable();
            $t->string('provider_message_id', 120)->nullable();
            $t->string('status', 16)->default('queued');
            $t->string('error_message', 500)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->index(['team_id', 'template', 'status']);
        });

        Schema::create('webhook_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $t->string('source', 16);
            $t->string('ep_event_id', 64);
            $t->string('description', 64);
            $t->boolean('signature_valid');
            $t->json('payload');
            $t->timestamp('processed_at')->nullable();
            $t->string('error', 500)->nullable();
            $t->timestamp('received_at')->useCurrent();
            $t->timestamps();
            $t->unique(['source', 'ep_event_id']);
            $t->index(['team_id', 'description', 'processed_at']);
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 64);
            $t->string('subject_type', 64)->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['team_id', 'action', 'created_at']);
            $t->index(['team_id', 'subject_type', 'subject_id']);
            $t->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('notification_events');
        Schema::dropIfExists('reports');
    }
};
