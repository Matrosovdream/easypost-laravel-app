<?php

use App\Actions\Trackers\DeleteTrackerAction;
use App\Actions\Trackers\GetPublicTrackerAction;
use App\Actions\Trackers\ListTrackersAction;
use App\Actions\Trackers\ShowTrackerAction;
use App\Helpers\Trackers\TrackerHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Tracker;
use App\Models\User;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(TrackerRepo::class);
    $this->ep = mock(EasyPostClient::class);
    $this->helper = new TrackerHelper();
});

it('ListTrackersAction returns paginated payload', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Tracker::class)->once();

    $action = new ListTrackersAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowTrackerAction 404s when not found', function () {
    $action = new ShowTrackerAction($this->repo, $this->helper);
    $this->repo->shouldReceive('findWithEvents')->andReturn(null);

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('DeleteTrackerAction skips EP call for non-trk_ ids', function () {
    $action = new DeleteTrackerAction($this->ep, $this->repo);

    $tracker = new Tracker();
    $tracker->id = 1;
    $tracker->ep_tracker_id = 'local_x';

    $this->repo->shouldReceive('findWithEvents')->with(1)->andReturn($tracker);
    Gate::shouldReceive('authorize')->with('delete', $tracker)->once();
    $this->ep->shouldNotReceive('deleteTracker');
    $this->repo->shouldReceive('delete')->with(1)->once();

    expect($action->execute(1))->toBe(['ok' => true]);
});

it('DeleteTrackerAction tries EP for trk_ ids and proceeds even if EP throws', function () {
    $action = new DeleteTrackerAction($this->ep, $this->repo);

    $tracker = new Tracker();
    $tracker->id = 1;
    $tracker->ep_tracker_id = 'trk_abc';

    $this->repo->shouldReceive('findWithEvents')->andReturn($tracker);
    Gate::shouldReceive('authorize')->once();
    $this->ep->shouldReceive('deleteTracker')->andThrow(new \RuntimeException('down'));
    $this->repo->shouldReceive('delete')->with(1)->once();

    expect($action->execute(1))->toBe(['ok' => true]);
});

it('GetPublicTrackerAction returns 404 JsonResponse when not found', function () {
    $action = new GetPublicTrackerAction($this->repo);
    $this->repo->shouldReceive('getByTrackingCode')->andReturn(null);

    $response = $action->execute('NOPE');
    expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
    expect($response->getStatusCode())->toBe(404);
});

it('GetPublicTrackerAction returns Resource when found', function () {
    $action = new GetPublicTrackerAction($this->repo);

    $tracker = new Tracker(['tracking_code' => 'EZ123', 'carrier' => 'USPS']);
    $tracker->id = 1;
    $tracker->setRelation('events', collect());

    $this->repo->shouldReceive('getByTrackingCode')->with('EZ123')->andReturn($tracker);

    $out = $action->execute('EZ123');
    expect($out)->toBeInstanceOf(\App\Http\Resources\Rest\PublicApi\PublicTrackerResource::class);
});
