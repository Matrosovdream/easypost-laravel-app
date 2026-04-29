<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('company_name', 120);
            $t->string('contact_name', 120)->nullable();
            $t->string('contact_email', 190)->nullable();
            $t->string('contact_phone', 24)->nullable();
            // default_from_address_id FK added later (addresses created next)
            $t->unsignedBigInteger('default_from_address_id')->nullable();
            $t->json('default_carrier_account_ids')->nullable();
            $t->decimal('flexrate_markup_pct', 5, 2)->default(0);
            $t->json('per_service_markups')->nullable();
            $t->string('billing_mode', 16)->default('postpaid');
            $t->unsignedSmallInteger('credit_terms_days')->default(30);
            $t->string('ep_endshipper_id', 64)->nullable()->index();
            $t->string('status', 16)->default('active');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['team_id', 'status']);
        });

        // Back-patch team_user.client_id FK
        Schema::table('team_user', function (Blueprint $t) {
            $t->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
        Schema::table('invitations', function (Blueprint $t) {
            $t->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $t) { $t->dropForeign(['client_id']); });
        Schema::table('team_user', function (Blueprint $t) { $t->dropForeign(['client_id']); });
        Schema::dropIfExists('clients');
    }
};
