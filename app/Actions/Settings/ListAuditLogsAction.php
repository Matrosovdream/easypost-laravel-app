<?php

namespace App\Actions\Settings;

use App\Helpers\Settings\AuditLogHelper;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;

class ListAuditLogsAction
{
    public function __construct(
        private readonly AuditLogRepo $auditLogs,
        private readonly AuditLogHelper $helper,
    ) {}

    public function execute(User $user, ?string $actionPrefix = null, int $perPage = 50): array
    {
        $rights = $user->rights();
        abort_unless((bool) array_intersect($rights, ['audit_log.view.any', 'audit_log.view.own']), 403);

        $userScope = in_array('audit_log.view.any', $rights, true) ? null : (int) $user->id;

        $page = $this->auditLogs->paginateForTeam(
            teamId: (int) $user->current_team_id,
            userIdScope: $userScope,
            actionPrefix: $actionPrefix,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}
