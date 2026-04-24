<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use App\Repositories\User\UserRepo;
use RuntimeException;

class RegenerateUserPinAction
{
    public function __construct(
        private readonly UserRepo $users,
        private readonly AuditLogRepo $auditLogs,
    ) {}

    /**
     * Returns the newly generated plaintext PIN ONCE — the caller is expected to
     * display or email it immediately, then forget it.
     */
    public function execute(User $target): string
    {
        $pepper = config('app.pin_pepper');
        if (! is_string($pepper) || $pepper === '') {
            throw new RuntimeException('PIN_PEPPER is not configured.');
        }

        // 4-digit PIN that is not already in use (collision-guarded).
        $attempts = 0;
        do {
            $pin = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $hash = hash_hmac('sha256', $pin, $pepper);
            $taken = $this->users->pinHashInUseByOther($hash, $target->id);
            $attempts++;
            if ($attempts > 100) {
                throw new RuntimeException('Could not find a free PIN after 100 attempts.');
            }
        } while ($taken);

        $this->users->setPinHash($target->id, $hash);

        $this->auditLogs->record([
            'team_id' => $target->current_team_id,
            'user_id' => $target->id,
            'action' => 'auth.pin.regenerated',
            'subject_type' => User::class,
            'subject_id' => $target->id,
            'meta' => json_encode(['method' => 'regenerate']),
        ]);

        return $pin;
    }
}
