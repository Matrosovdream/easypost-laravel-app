<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('email', 190);
            $t->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $t->unsignedBigInteger('client_id')->nullable()->index();
            $t->unsignedInteger('spending_cap_cents')->nullable();
            $t->unsignedInteger('daily_cap_cents')->nullable();
            $t->string('token', 80)->unique();
            $t->timestamp('expires_at');
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->foreignId('invited_by')->constrained('users')->restrictOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['team_id', 'email']);
            $t->index(['email', 'expires_at']);
        });

        Schema::create('access_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('requested_permission', 96);
            $t->string('target_url', 512)->nullable();
            $t->string('status', 16)->default('pending');
            $t->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->text('resolution_note')->nullable();
            $t->timestamps();
            $t->index(['team_id', 'status']);
            $t->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
        Schema::dropIfExists('invitations');
    }
};
