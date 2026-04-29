<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('logo_s3_key', 255)->nullable();
            $t->string('plan', 32)->default('starter')->index();
            $t->string('status', 16)->default('active')->index();
            $t->string('mode', 16)->default('standard');
            $t->string('stripe_customer_id', 64)->nullable()->unique();
            $t->string('stripe_subscription_id', 64)->nullable();
            $t->timestamp('trial_ends_at')->nullable();
            $t->string('ep_user_id', 64)->nullable();
            $t->char('default_currency', 3)->default('USD');
            $t->string('time_zone', 64)->default('UTC');
            $t->json('settings')->nullable();
            $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });

        // Back-patch users.current_team_id FK now that teams exists
        Schema::table('users', function (Blueprint $t) {
            $t->foreign('current_team_id')->references('id')->on('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropForeign(['current_team_id']);
        });
        Schema::dropIfExists('teams');
    }
};
