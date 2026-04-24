<?php

namespace App\Actions\Returns;

use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use RuntimeException;

class DeclineReturnAction
{
    public function __construct(private readonly ReturnRequestRepo $returns) {}

    public function execute(User $user, ReturnRequest $return, ?string $reason = null): ReturnRequest
    {
        if ($return->status !== 'requested') {
            throw new RuntimeException("Return is already {$return->status}.");
        }

        $notes = trim(($return->notes ? $return->notes."\n" : '').'Declined by '.$user->name.($reason ? ": {$reason}" : ''));

        return $this->returns->markDeclined($return, $user->id, $notes);
    }
}
