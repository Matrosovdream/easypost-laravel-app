<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'is_system', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_system'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rights(): HasMany
    {
        return $this->hasMany(RoleRight::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['team_id', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }
}
