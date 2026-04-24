<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleRight;
use App\Support\Rights;
use Illuminate\Database\Seeder;

class RoleRightsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Rights::byRole() as $roleSlug => $rightSlugs) {
            $roleId = Role::where('slug', $roleSlug)->value('id');
            if (! $roleId) continue;

            RoleRight::where('role_id', $roleId)->delete();

            $rows = collect($rightSlugs)
                ->filter(fn ($slug) => Rights::exists($slug))
                ->unique()
                ->map(fn (string $slug) => [
                    'role_id'    => $roleId,
                    'right'      => $slug,
                    'group'      => explode('.', $slug)[0],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

            if (!empty($rows)) {
                RoleRight::insert($rows);
            }
        }
    }
}
