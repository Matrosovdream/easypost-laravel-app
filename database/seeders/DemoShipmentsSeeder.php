<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\Parcel;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoShipmentsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoShipmentsSeeder: skipped in production.');
            return;
        }

        $team = Team::where('name', 'Acme 3PL (demo)')->first();
        if (! $team) {
            $this->command?->warn('DemoShipmentsSeeder: no demo team, skipping.');
            return;
        }

        $admin = User::where('email', 'stan+admin@shipdesk.local')->first();
        $shipper = User::where('email', 'pat@shipdesk.local')->first();
        $client = Client::where('team_id', $team->id)->first();

        $from = Address::firstOrCreate(
            ['team_id' => $team->id, 'street1' => '417 Montgomery St'],
            [
                'name' => 'ShipDesk Warehouse',
                'company' => 'ShipDesk',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94104',
                'country' => 'US',
                'phone' => '4155550100',
                'verified' => true,
            ],
        );

        $destinations = [
            ['Casey Buyer', '1600 Amphitheatre Pkwy', 'Mountain View', 'CA', '94043'],
            ['Avery Carter', '500 5th Ave', 'New York', 'NY', '10110'],
            ['Jesse Doe', '233 S Wacker Dr', 'Chicago', 'IL', '60606'],
            ['Morgan Klein', '701 Pennsylvania Ave NW', 'Washington', 'DC', '20004'],
            ['Sam Rivera', '999 Town & Country Rd', 'Orange', 'CA', '92868'],
        ];

        foreach ($destinations as $i => [$name, $street, $city, $state, $zip]) {
            $to = Address::firstOrCreate(
                ['team_id' => $team->id, 'street1' => $street, 'zip' => $zip],
                [
                    'name' => $name,
                    'city' => $city,
                    'state' => $state,
                    'country' => 'US',
                    'phone' => '4155550101',
                    'verified' => true,
                ],
            );

            $parcel = Parcel::create([
                'team_id' => $team->id,
                'predefined_package' => 'Parcel',
                'length_in' => 10,
                'width_in' => 8,
                'height_in' => 4,
                'weight_oz' => 16 + $i * 4,
            ]);

            $rates = [
                ['id' => 'rate_'.uniqid(), 'carrier' => 'USPS', 'service' => 'Priority', 'rate' => 9.95, 'delivery_days' => 2, 'retail_rate' => 12.95],
                ['id' => 'rate_'.uniqid(), 'carrier' => 'UPS',  'service' => 'Ground',   'rate' => 12.20, 'delivery_days' => 3, 'retail_rate' => 16.50],
                ['id' => 'rate_'.uniqid(), 'carrier' => 'FedEx','service' => 'Home Delivery','rate' => 14.60,'delivery_days' => 2, 'retail_rate' => 18.95],
            ];
            $selectedRate = $rates[0];

            $status = match ($i) {
                0 => 'rated',
                1 => 'pending_approval',
                2 => 'purchased',
                3 => 'packed',
                default => 'delivered',
            };

            $shipment = Shipment::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'client_id' => $client?->id,
                'ep_shipment_id' => 'shp_demo_'.$i,
                'reference' => 'DEMO-'.(1000 + $i),
                'status' => $status,
                'to_address_id' => $to->id,
                'from_address_id' => $from->id,
                'parcel_id' => $parcel->id,
                'requested_by' => $i === 1 ? $shipper?->id : $admin?->id,
                'assigned_to' => in_array($status, ['purchased', 'packed'], true) ? $shipper?->id : null,
                'rates_snapshot' => $rates,
                'selected_rate' => $status === 'rated' ? null : $selectedRate,
                'carrier' => $status === 'rated' ? null : 'USPS',
                'service' => $status === 'rated' ? null : 'Priority',
                'tracking_code' => $status === 'rated' ? null : 'EZ20000000'.$i,
                'cost_cents' => $status === 'rated' ? null : 995,
                'approved_by' => in_array($status, ['purchased', 'packed', 'delivered'], true) ? $admin?->id : null,
                'approved_at' => in_array($status, ['purchased', 'packed', 'delivered'], true) ? now()->subDays(1) : null,
                'packed_at' => in_array($status, ['packed', 'delivered'], true) ? now()->subHours(6) : null,
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'type' => 'created',
                'payload' => ['rates_count' => count($rates)],
                'created_by' => $admin?->id,
            ]);
        }

        $this->command?->info('DemoShipmentsSeeder: created '.count($destinations).' demo shipments.');
    }
}
