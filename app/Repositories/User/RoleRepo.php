<?php

namespace App\Repositories\User;

use App\Models\Role;
use App\Repositories\AbstractRepo;

class RoleRepo extends AbstractRepo
{
    protected $withRelations = ['rights'];

    public function __construct()
    {
        $this->model = new Role();
    }

    public function syncRights(int $roleId, array $rightSlugs): void
    {
        $role = $this->model->find($roleId);
        if (!$role) {
            return;
        }

        $rows = collect($rightSlugs)->unique()->map(fn ($slug) => [
            'role_id'    => $roleId,
            'right'      => $slug,
            'group'      => explode('.', $slug)[0],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        $role->rights()->delete();
        if (!empty($rows)) {
            $role->rights()->getRelated()->insert($rows);
        }
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id'          => $item->id,
            'slug'        => $item->slug,
            'name'        => $item->name,
            'description' => $item->description,
            'is_system'   => (bool) $item->is_system,
            'rights'      => $item->relationLoaded('rights')
                ? $item->rights->map(fn ($r) => [
                    'right' => $r->right,
                    'group' => $r->group,
                ])->values()->toArray()
                : [],
            'created_at'  => $item->created_at,
            'Model'       => $item,
        ];
    }
}
