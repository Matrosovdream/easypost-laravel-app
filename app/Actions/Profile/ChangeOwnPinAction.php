<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;
use RuntimeException;

class ChangeOwnPinAction
{
    public function __construct(
        private readonly UserRepo $users,
        private readonly AuditLogRepo $auditLogs,
    ) {}

    public function execute(User $user, string $oldPin, string $newPin): void
    {
        $pepper = config('app.pin_pepper');
        if (! is_string($pepper) || $pepper === '') {
            throw new RuntimeException('PIN_PEPPER is not configured.');
        }

        $oldHash = hash_hmac('sha256', $oldPin, $pepper);
        if ($user->pin_hash !== $oldHash) {
            throw new RuntimeException('Current PIN is incorrect.');
        }

        if ($oldPin === $newPin) {
            throw new RuntimeException('New PIN must differ from current.');
        }

        $newHash = hash_hmac('sha256', $newPin, $pepper);
        if ($this->users->pinHashInUseByOther($newHash, $user->id)) {
            throw new RuntimeException('That PIN is already in use.');
        }

        $this->users->setPinHash($user->id, $newHash);

        $this->auditLogs->record([
            'team_id' => $user->current_team_id,
            'user_id' => $user->id,
            'action' => 'auth.pin.changed',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'meta' => json_encode(['method' => 'self']),
        ]);
    }
}
