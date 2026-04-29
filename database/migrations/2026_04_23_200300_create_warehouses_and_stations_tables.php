<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->string('name', 120);
            // address_id FK added in addresses migration
            $t->unsignedBigInteger('address_id')->nullable()->index();
            $t->time('default_min_pickup_time')->nullable();
            $t->time('default_max_pickup_time')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['team_id', 'is_active']);
        });

        Schema::create('stations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('default_printer', 120)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['team_id', 'warehouse_id']);
            $t->index(['team_id', 'is_active']);
        });

        Schema::create('station_shipper', function (Blueprint $t) {
            $t->id();
            $t->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['station_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_shipper');
        Schema::dropIfExists('stations');
        Schema::dropIfExists('warehouses');
    }
};
