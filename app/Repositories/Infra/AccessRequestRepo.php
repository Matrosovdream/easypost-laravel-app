<?php

namespace App\Repositories\Infra;

use Illuminate\Support\Facades\DB;

class AccessRequestRepo
{
    public function create(array $data): int
    {
        return DB::table('access_requests')->insertGetId(array_merge($data, [
            'status' => $data['status'] ?? 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
