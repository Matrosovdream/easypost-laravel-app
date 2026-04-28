<?php

use App\Actions\Trackers\CreateStandaloneTrackerAction;
use App\Helpers\Trackers\TrackerHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Tracker;
use App\Models\User;
use App\Repositories\Tracker\TrackerEventRepo;
use App\Repositories\Tracker\TrackerRepo;

beforeEach(function () {
    $this->ep = mock(EasyPostClient::class);
    $this->trackers = mock(TrackerRepo::class);
    $this->events = mock(TrackerEventRepo::class);
    $this->action = new CreateStandaloneTrackerAction($this->ep, $this->trackers, $this->events, new TrackerHelper());
});

it('falls through when EP throws and persists tracker locally', function () {
    $tracker = mock(Tracker::class)->makePartial();
    $tracker->id = 1;
    $tracker->tracking_code = 'EZ';
    $tracker->carrier = 'USPS';
    $tracker->status = 'pre_transit';
    $tracker->setRelation('events', collect());
    $tracker->shouldReceive('fresh')->andReturnSelf();

    $this->ep->shouldReceive('createTracker')->andThrow(new \RuntimeException('down'));
    $this->trackers->shouldReceive('create')->andReturn(['Model' => $tracker]);

    $user = new User();
    $user->current_team_id = 3;

    $out = $this->action->execute($user, 'EZ', 'USPS');
    expect($out['tracking_code'])->toBe('EZ');
});
