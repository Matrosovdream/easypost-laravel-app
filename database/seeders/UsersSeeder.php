<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $pepper = config('app.pin_pepper');
        if (! $pepper) {
            throw new \RuntimeException('PIN_PEPPER is not set — cannot seed users without a pepper.');
        }

        $team = Team::firstOrCreate(
            ['name' => 'Acme 3PL (demo)'],
            [
                'plan'             => 'business',
                'mode'             => '3pl',
                'status'           => 'active',
                'default_currency' => 'USD',
                'time_zone'        => 'America/New_York',
                'settings'         => ['onboarding_state' => ['completed_at' => null]],
            ],
        );

        $widgetsClient = Client::firstOrCreate(
            ['team_id' => $team->id, 'company_name' => 'Widgets+'],
            ['contact_email' => 'ops@widgets.example.com', 'status' => 'active'],
        );

        $roleIds = Role::pluck('id', 'slug');

        /** @var array<int,array{0:string,1:string,2:string,3:string}> $accounts */
        $accounts = [
            ['9999', 'admin',    'Stan Admin',      'stan+admin@shipdesk.local'],
            ['9998', 'admin',    'Alex Admin',      'alex+admin@shipdesk.local'],
            ['8888', 'manager',  'Riley Manager',   'riley@shipdesk.local'],
            ['8887', 'manager',  'Morgan Manager',  'morgan@shipdesk.local'],
            ['7777', 'shipper',  'Pat Shipper',     'pat@shipdesk.local'],
            ['7776', 'shipper',  'Quinn Shipper',   'quinn@shipdesk.local'],
            ['7775', 'shipper',  'River Shipper',   'river@shipdesk.local'],
            ['6666', 'cs_agent', 'Maya CS',         'maya@shipdesk.local'],
            ['6665', 'cs_agent', 'Noah CS',         'noah@shipdesk.local'],
            ['5555', 'client',   'Jen Widgets',     'jen@widgets.example.com'],
            ['5554', 'client',   'Bob Widgets',     'bob@widgets.example.com'],
            ['4444', 'viewer',   'Jordan Viewer',   'jordan@shipdesk.local'],
        ];

        foreach ($accounts as [$pin, $roleSlug, $name, $email]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'email_verified_at' => now(),
                    'password'          => null,
                    'pin_hash'          => hash_hmac('sha256', $pin, $pepper),
                    'is_active'         => true,
                    'current_team_id'   => $team->id,
                ],
            );

            DB::table('team_user')->updateOrInsert(
                ['team_id' => $team->id, 'user_id' => $user->id],
                [
                    'status'     => 'active',
                    'client_id'  => $roleSlug === 'client' ? $widgetsClient->id : null,
                    'joined_at'  => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $roleId = $roleIds[$roleSlug] ?? null;
            if ($roleId) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'team_id' => $team->id, 'role_id' => $roleId],
                    [
                        'assigned_at' => now(),
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ],
                );
            }
        }

        $this->command?->info('UsersSeeder: 12 demo users created in team "'.$team->name.'".');
    }
}
