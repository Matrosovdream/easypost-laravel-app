<?php

use App\Actions\Reports\CreateReportAction;
use App\Models\User;
use App\Repositories\Infra\ReportRepo;

beforeEach(function () {
    $this->reports = mock(ReportRepo::class);
    $this->action = new CreateReportAction($this->reports);
});

it('aborts 403 when user lacks reports.create right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user, [
        'type' => 'shipment', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31',
    ]))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('inserts report and returns id+queued status', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['reports.create']);
    $user->id = 7;
    $user->current_team_id = 3;

    $this->reports->shouldReceive('create')
        ->withArgs(fn ($data) =>
            $data['team_id'] === 3
            && $data['type'] === 'shipment'
            && $data['requested_by'] === 7)
        ->once()
        ->andReturn(99);

    $out = $this->action->execute($user, [
        'type' => 'shipment', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31',
    ]);
    expect($out)->toBe(['id' => 99, 'status' => 'queued']);
});
