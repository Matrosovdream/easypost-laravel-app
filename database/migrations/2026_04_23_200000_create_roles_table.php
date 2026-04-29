<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 48)->unique();
            $t->string('name', 64);
            $t->string('description', 255)->nullable();
            $t->boolean('is_system')->default(false)->index();
            $t->unsignedSmallInteger('sort_order')->default(100);
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('roles'); }
};
