<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Pickup;
use App\Models\ScanForm;
use App\Models\Shipment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoOperationsSeeder extends Seeder
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
        $shipper = User::where('email', 'pat@shipdesk.local')->first();

        $purchased = Shipment::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->whereIn('status', ['purchased', 'packed'])
            ->get();

        if ($purchased->count() < 2) {
            $this->command?->warn('DemoOperationsSeeder: not enough purchased shipments to build demo ops.');
            return;
        }

        // Demo batch with the purchased shipments
        $batch = Batch::create([
            'team_id' => $team->id,
            'ep_batch_id' => 'batch_demo_0',
            'reference' => 'WAVE-'.now()->format('Ymd'),
            'state' => 'purchased',
            'num_shipments' => $purchased->count(),
            'label_pdf_s3_key' => 'https://easypost-labels.example.com/batch-demo.pdf',
            'status_summary' => [
                'postage_purchased' => $purchased->count(),
                'queued_for_purchase' => 0,
                'postage_purchase_failed' => 0,
            ],
            'created_by' => $admin?->id,
        ]);
        $batch->shipments()->sync(
            $purchased->mapWithKeys(fn ($s) => [
                $s->id => [
                    'batch_status' => 'postage_purchased',
                    'batch_message' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ])->all()
        );

        // Demo scan form covering those shipments
        ScanForm::create([
            'team_id' => $team->id,
            'ep_scan_form_id' => 'sf_demo_0',
            'carrier' => 'USPS',
            'from_address_id' => $purchased->first()->from_address_id,
            'form_pdf_s3_key' => 'https://easypost-labels.example.com/scanform-demo.pdf',
            'tracking_codes' => $purchased->pluck('tracking_code')->filter()->values()->all(),
            'status' => 'created',
            'created_by' => $shipper?->id ?? $admin?->id,
        ]);

        // Demo pickup tomorrow 1–5pm
        Pickup::create([
            'team_id' => $team->id,
            'ep_pickup_id' => 'pu_demo_0',
            'reference' => 'PICKUP-'.now()->addDay()->format('Ymd'),
            'address_id' => $purchased->first()->from_address_id,
            'min_datetime' => now()->addDay()->setTime(13, 0),
            'max_datetime' => now()->addDay()->setTime(17, 0),
            'instructions' => 'Ring the back door, packages are in the staging area.',
            'is_account_address' => true,
            'carrier' => 'USPS',
            'service' => 'NextDay',
            'confirmation' => 'WTC50328',
            'cost_cents' => 0,
            'status' => 'scheduled',
            'rates_snapshot' => [
                ['carrier' => 'USPS', 'service' => 'NextDay', 'rate' => 0.00],
                ['carrier' => 'UPS',  'service' => 'Future', 'rate' => 7.50],
            ],
            'created_by' => $shipper?->id ?? $admin?->id,
        ]);

        $this->command?->info('DemoOperationsSeeder: seeded 1 batch, 1 scan form, 1 pickup.');
    }
}
