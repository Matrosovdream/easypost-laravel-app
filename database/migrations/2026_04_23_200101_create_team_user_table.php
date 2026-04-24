<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // client_id FK added in later migration (clients table created next)
            $t->unsignedBigInteger('client_id')->nullable()->index();
            $t->unsignedInteger('spending_cap_cents')->nullable();
            $t->unsignedInteger('daily_cap_cents')->nullable();
            $t->unsignedBigInteger('station_id')->nullable()->index();
            $t->unsignedBigInteger('warehouse_id')->nullable()->index();
            $t->string('status', 16)->default('active');
            $t->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('joined_at')->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamps();
            $t->unique(['team_id', 'user_id']);
            $t->index(['team_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('team_user'); }
};
