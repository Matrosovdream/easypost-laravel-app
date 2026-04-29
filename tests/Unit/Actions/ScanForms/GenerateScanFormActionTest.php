<?php

use App\Actions\ScanForms\GenerateScanFormAction;
use App\Helpers\ScanForms\ScanFormHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\ScanForm;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Operations\ScanFormRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->ep = mock(EasyPostClient::class);
    $this->forms = mock(ScanFormRepo::class);
    $this->shipments = mock(ShipmentRepo::class);
    $this->action = new GenerateScanFormAction($this->ep, $this->forms, $this->shipments, new ScanFormHelper());
});

it('throws when no qualifying shipments', function () {
    Gate::shouldReceive('authorize')->with('create', ScanForm::class)->once();

    $user = new User();
    $user->current_team_id = 3;

    $this->shipments->shouldReceive('inTeam')->andReturn(new Collection());

    expect(fn () => $this->action->execute($user, [1]))
        ->toThrow(RuntimeException::class, 'No qualifying shipments');
});

it('throws when carriers differ across shipments', function () {
    Gate::shouldReceive('authorize')->once();

    $user = new User();
    $user->current_team_id = 3;

    $a = new Shipment(['carrier' => 'USPS', 'from_address_id' => 1, 'tracking_code' => 'T1', 'ep_shipment_id' => 'shp_1']);
    $b = new Shipment(['carrier' => 'UPS',  'from_address_id' => 1, 'tracking_code' => 'T2', 'ep_shipment_id' => 'shp_2']);

    $this->shipments->shouldReceive('inTeam')->andReturn(new Collection([$a, $b]));

    expect(fn () => $this->action->execute($user, [1, 2]))
        ->toThrow(RuntimeException::class, 'same carrier');
});

it('persists scan form and returns shaped payload', function () {
    Gate::shouldReceive('authorize')->once();

    $user = new User();
    $user->id = 7;
    $user->current_team_id = 3;

    $a = new Shipment(['carrier' => 'USPS', 'from_address_id' => 1, 'tracking_code' => 'T1', 'ep_shipment_id' => 'shp_1']);

    $this->shipments->shouldReceive('inTeam')->andReturn(new Collection([$a]));
    $this->ep->shouldReceive('createScanForm')->andReturn(
        new \Illuminate\Http\Client\Response(
            new \GuzzleHttp\Psr7\Response(200, [], json_encode(['id' => 'sf_x', 'form_url' => 's3://f.pdf', 'status' => 'created']))
        )
    );

    $form = new ScanForm(['carrier' => 'USPS', 'status' => 'created', 'form_pdf_s3_key' => 's3://f.pdf']);
    $form->id = 1;

    $this->forms->shouldReceive('create')->andReturn(['Model' => $form]);

    $out = $this->action->execute($user, [1]);
    expect($out)->toBe(['id' => 1, 'status' => 'created', 'form_url' => 's3://f.pdf']);
});
