<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_rights', function (Blueprint $t) {
            $t->id();
            $t->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $t->string('right', 96);
            $t->string('group', 48)->index();
            $t->timestamps();
            $t->unique(['role_id', 'right']);
            $t->index('right');
        });
    }

    public function down(): void { Schema::dropIfExists('role_rights'); }
};
