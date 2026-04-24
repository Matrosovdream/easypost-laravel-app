<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
uses(RefreshDatabase::class);
beforeEach(function () { $this->seed(); Cache::flush(); });
it("hits users list", function () {
    $admin = User::where("email", "stan+admin@shipdesk.local")->firstOrFail();
    $this->actingAs($admin)->getJson("/api/settings/users")->assertOk();
});
