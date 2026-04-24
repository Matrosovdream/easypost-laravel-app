<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleRight extends Model
{
    protected $fillable = ['role_id', 'right', 'group'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
