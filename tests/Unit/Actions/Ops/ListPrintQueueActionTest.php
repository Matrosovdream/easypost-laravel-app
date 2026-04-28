<?php

use App\Actions\Ops\ListPrintQueueAction;
use App\Helpers\Ops\PrintQueueHelper;
use App\Models\User;
use App\Repositories\Shipping\ShipmentRepo;

beforeEach(function () {
    $this->repo = mock(ShipmentRepo::class);
    $this->action = new ListPrintQueueAction($this->repo, new PrintQueueHelper());
});

it('aborts 403 when user lacks labels.print right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns shaped data list when authorized', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['labels.print']);

    $this->repo->shouldReceive('printQueue')->andReturn(new \Illuminate\Database\Eloquent\Collection());

    expect($this->action->execute($user)['data']->count())->toBe(0);
});
