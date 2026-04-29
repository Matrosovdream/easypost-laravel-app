<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('email', 190)->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password', 255)->nullable();
            // HMAC-SHA256 hex of plaintext PIN (hex digest = 64 chars). UNIQUE globally.
            $t->char('pin_hash', 64)->nullable()->unique();
            $t->string('phone', 24)->nullable();
            $t->string('avatar_s3_key', 255)->nullable();
            $t->string('locale', 8)->default('en');
            $t->string('timezone', 64)->default('UTC');
            $t->boolean('is_active')->default(true);
            $t->text('two_factor_secret')->nullable();
            $t->text('two_factor_recovery_codes')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable();
            // current_team_id FK added in later migration after teams table exists
            $t->unsignedBigInteger('current_team_id')->nullable()->index();
            $t->timestamp('last_login_at')->nullable();
            $t->unsignedBigInteger('freshdesk_contact_id')->nullable();
            $t->rememberToken();
            $t->timestamps();
            $t->softDeletes();
            $t->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
