<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('email', 160);
            $t->string('company', 160)->nullable();
            $t->string('topic', 32)->default('other');
            $t->text('message');
            $t->string('source_ip', 45)->nullable();
            $t->string('user_agent', 500)->nullable();
            $t->string('status', 16)->default('new')->index();
            $t->timestamps();
            $t->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
