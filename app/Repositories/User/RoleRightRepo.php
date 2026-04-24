<?php

namespace App\Repositories\User;

use App\Models\RoleRight;
use App\Repositories\AbstractRepo;
use App\Support\Rights;

class RoleRightRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new RoleRight();
    }

    public function getByRole(int $roleId): array
    {
        return $this->model
            ->where('role_id', $roleId)
            ->get()
            ->map(fn ($r) => ['right' => $r->right, 'group' => $r->group])
            ->all();
    }

    public function getCatalog(): array
    {
        return Rights::catalog();
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id'      => $item->id,
            'role_id' => $item->role_id,
            'right'   => $item->right,
            'group'   => $item->group,
            'Model'   => $item,
        ];
    }
}
