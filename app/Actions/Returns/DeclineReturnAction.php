<?php

namespace App\Actions\Returns;

use App\Helpers\Returns\ReturnHelper;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use RuntimeException;

class DeclineReturnAction
{
    public function __construct(
        private readonly ReturnRequestRepo $returns,
        private readonly ReturnHelper $helper,
    ) {}

    public function execute(User $user, int $id, ?string $reason = null): array
    {
        $return = $this->returns->findWithDetails($id);
        abort_if(! $return, 404);
        abort_unless($user->can('approve', ReturnRequest::class), 403);

        if ($return->status !== 'requested') {
            throw new RuntimeException("Return is already {$return->status}.");
        }

        $notes = trim(($return->notes ? $return->notes."\n" : '').'Declined by '.$user->name.($reason ? ": {$reason}" : ''));

        $return = $this->returns->markDeclined($return, $user->id, $notes);

        return $this->helper->toIdentity($return);
    }
}
