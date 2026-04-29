<?php

use App\Actions\Reports\ListReportsAction;
use App\Helpers\Reports\ReportHelper;
use App\Models\User;
use App\Repositories\Infra\ReportRepo;

beforeEach(function () {
    $this->reports = mock(ReportRepo::class);
    $this->action = new ListReportsAction($this->reports, new ReportHelper());
});

it('aborts 403 when user lacks reports.view right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns shaped list payload via helper', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['reports.view']);
    $user->current_team_id = 3;

    $rows = collect([
        (object) [
            'id' => 1, 'type' => 'shipment', 'status' => 'queued',
            'start_date' => null, 'end_date' => null, 's3_key' => null,
            'created_at' => null,
        ],
    ]);

    $this->reports->shouldReceive('listForTeam')->with(3)->andReturn($rows);

    $out = $this->action->execute($user);
    expect($out['data']->count())->toBe(1);
});
