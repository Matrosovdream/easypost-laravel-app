<?php

namespace App\Services\DummyData;

use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use RuntimeException;

/**
 * Resolves the demo team + seeded users that EasyPost dummy data attaches to.
 * Reads from the rows produced by UsersSeeder; throws if the seeder hasn't run.
 */
class AssignmentPicker
{
    public const DEMO_TEAM_NAME = 'Acme 3PL (demo)';

    private Team $team;
    private ?Client $client;

    /** @var array<string, int> email => user id */
    private array $usersByEmail = [];

    /** @var array<int, int> active user ids on the demo team */
    private array $teamUserIds = [];

    public function __construct()
    {
        $team = Team::query()->where('name', self::DEMO_TEAM_NAME)->first();
        if (!$team) {
            throw new RuntimeException(
                'Demo team "'.self::DEMO_TEAM_NAME.'" not found. Run `php artisan db:seed --class=UsersSeeder` first.'
            );
        }
        $this->team = $team;
        $this->client = Client::query()->where('team_id', $team->id)->first();

        $this->teamUserIds = User::query()
            ->where('current_team_id', $team->id)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $this->usersByEmail = User::query()
            ->whereIn('id', $this->teamUserIds)
            ->pluck('id', 'email')
            ->all();
    }

    public function team(): Team
    {
        return $this->team;
    }

    public function teamId(): int
    {
        return $this->team->id;
    }

    public function client(): ?Client
    {
        return $this->client;
    }

    public function admin(): ?int
    {
        return $this->usersByEmail['stan+admin@shipdesk.local']
            ?? $this->usersByEmail['alex+admin@shipdesk.local']
            ?? null;
    }

    public function manager(): ?int
    {
        return $this->usersByEmail['riley@shipdesk.local']
            ?? $this->usersByEmail['morgan@shipdesk.local']
            ?? null;
    }

    public function shipper(): ?int
    {
        return $this->usersByEmail['pat@shipdesk.local']
            ?? $this->usersByEmail['quinn@shipdesk.local']
            ?? $this->usersByEmail['river@shipdesk.local']
            ?? null;
    }

    public function csAgent(): ?int
    {
        return $this->usersByEmail['maya@shipdesk.local']
            ?? $this->usersByEmail['noah@shipdesk.local']
            ?? null;
    }

    public function randomTeamUserId(): ?int
    {
        if (empty($this->teamUserIds)) {
            return null;
        }
        return $this->teamUserIds[array_rand($this->teamUserIds)];
    }
}
