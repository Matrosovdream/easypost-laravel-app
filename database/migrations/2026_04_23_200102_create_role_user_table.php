<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // team-scoped role assignment pivot
        Schema::create('role_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('assigned_at')->useCurrent();
            $t->timestamps();
            $t->unique(['user_id', 'role_id', 'team_id']);
            $t->index(['team_id', 'role_id']);
            $t->index(['user_id', 'team_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('role_user'); }
};
