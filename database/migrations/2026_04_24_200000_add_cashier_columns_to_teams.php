<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cashier's Billable trait looks for `stripe_id`, `pm_type`, `pm_last_four` on the
 * billable model. Our `teams` table already has `stripe_customer_id`, so we add
 * `stripe_id` as an alias (mirrored via the Team model accessor) and the two
 * payment-method columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $t) {
            if (! Schema::hasColumn('teams', 'stripe_id')) {
                $t->string('stripe_id')->nullable()->index();
            }
            if (! Schema::hasColumn('teams', 'pm_type')) {
                $t->string('pm_type')->nullable();
            }
            if (! Schema::hasColumn('teams', 'pm_last_four', )) {
                $t->string('pm_last_four', 4)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $t) {
            if (Schema::hasColumn('teams', 'stripe_id')) {
                $t->dropIndex(['stripe_id']);
                $t->dropColumn('stripe_id');
            }
            if (Schema::hasColumn('teams', 'pm_type')) {
                $t->dropColumn('pm_type');
            }
            if (Schema::hasColumn('teams', 'pm_last_four')) {
                $t->dropColumn('pm_last_four');
            }
        });
    }
};
