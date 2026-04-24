<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Insurance;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCareSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $team = Team::where('name', 'Acme 3PL (demo)')->first();
        if (! $team) {
            return;
        }

        $admin = User::where('email', 'stan+admin@shipdesk.local')->first();
        $manager = User::where('email', 'riley@shipdesk.local')->first();
        $csAgent = User::where('email', 'maya@shipdesk.local')->first();
        $client = User::where('email', 'jen@widgets.example.com')->first();

        $delivered = Shipment::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('status', 'delivered')
            ->first();

        $purchased = Shipment::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('status', 'purchased')
            ->first();

        if ($delivered) {
            ReturnRequest::create([
                'team_id' => $team->id,
                'client_id' => $delivered->client_id,
                'original_shipment_id' => $delivered->id,
                'reason' => 'wrong_item',
                'items' => ['SKU-1001 × 1'],
                'notes' => 'Customer received the wrong color. Pre-approved for a free return.',
                'status' => 'requested',
                'auto_refund' => true,
                'created_by' => $client?->id ?? $admin?->id,
            ]);
        }

        if ($purchased) {
            $claim = Claim::create([
                'team_id' => $team->id,
                'shipment_id' => $purchased->id,
                'type' => 'damage',
                'amount_cents' => 8995,
                'description' => "Box arrived crushed. Photo evidence attached. Item unusable.",
                'state' => 'submitted',
                'timeline' => [
                    ['at' => now()->subDays(2)->toIso8601String(), 'event' => 'opened', 'by' => $csAgent?->id ?? $admin?->id],
                    ['at' => now()->subDays(1)->toIso8601String(), 'event' => 'submitted', 'by' => $csAgent?->id ?? $admin?->id, 'ep_id' => 'clm_demo_1'],
                ],
                'ep_claim_id' => 'clm_demo_1',
                'assigned_to' => $csAgent?->id,
            ]);

            Insurance::create([
                'team_id' => $team->id,
                'shipment_id' => $purchased->id,
                'ep_insurance_id' => 'ins_demo_1',
                'provider' => 'EasyPost',
                'tracking_code' => $purchased->tracking_code ?? 'EZ20000000X',
                'carrier' => $purchased->carrier ?? 'USPS',
                'amount_cents' => 10000,
                'fee_cents' => 50,
                'status' => 'new',
                'reference' => 'INS-DEMO-0001',
            ]);
        }

        $this->command?->info('DemoCareSeeder: seeded 1 return + 1 claim + 1 insurance.');
    }
}
