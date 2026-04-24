<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Users\ChangeUserRoleAction;
use App\Actions\Users\InviteUserAction;
use App\Actions\Users\RegenerateUserPinAction;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(
        private readonly InviteUserAction $invite,
        private readonly ChangeUserRoleAction $changeRole,
        private readonly RegenerateUserPinAction $regenPin,
        private readonly UserRepo $users,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless(in_array('users.manage', $request->user()->rights(), true), 403);

        $rows = $this->users->listTeamMembers((int) $request->user()->current_team_id);

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'email' => $r->email,
                'role_slug' => $r->role_slug,
                'role_name' => $r->role_name,
                'is_active' => (bool) $r->is_active,
                'last_login_at' => $r->last_login_at,
                'spending_cap_cents' => $r->spending_cap_cents,
                'daily_cap_cents' => $r->daily_cap_cents,
                'client_id' => $r->client_id,
                'membership_status' => $r->membership_status,
            ])->values(),
        ]);
    }

    public function invite(Request $request): JsonResponse
    {
        abort_unless(in_array('users.invite', $request->user()->rights(), true), 403);

        $v = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'role_slug' => ['required', 'in:admin,manager,shipper,cs_agent,client,viewer'],
            'client_id' => ['nullable', 'integer'],
            'spending_cap_cents' => ['nullable', 'integer', 'min:0'],
            'daily_cap_cents' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($v['role_slug'] === 'admin' && ! in_array('users.role.assign.admin', $request->user()->rights(), true)) {
            return response()->json(['message' => 'Only admins may invite other admins.'], 403);
        }

        $out = $this->invite->execute($request->user(), $v);
        return response()->json($out, 201);
    }

    public function changeRole(Request $request, int $id): JsonResponse
    {
        abort_unless(in_array('users.role.assign', $request->user()->rights(), true), 403);

        $v = $request->validate([
            'role_slug' => ['required', 'in:admin,manager,shipper,cs_agent,client,viewer'],
        ]);

        if ($v['role_slug'] === 'admin' && ! in_array('users.role.assign.admin', $request->user()->rights(), true)) {
            return response()->json(['message' => 'Only admins may promote to admin.'], 403);
        }

        $target = $this->users->getModel()->newQuery()->find($id);
        abort_if(! $target, 404);

        try {
            $this->changeRole->execute($request->user(), $target, $v['role_slug']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        abort_unless(in_array('users.manage', $request->user()->rights(), true), 403);

        if ($id === (int) $request->user()->id) {
            return response()->json(['message' => 'Cannot disable yourself.'], 422);
        }

        $user = $this->users->getModel()->newQuery()->find($id);
        abort_if(! $user, 404);
        $this->users->setActive($user->id, false);
        return response()->json(['ok' => true]);
    }

    public function enable(Request $request, int $id): JsonResponse
    {
        abort_unless(in_array('users.manage', $request->user()->rights(), true), 403);

        $user = $this->users->getModel()->newQuery()->find($id);
        abort_if(! $user, 404);
        $this->users->setActive($user->id, true);
        return response()->json(['ok' => true]);
    }

    public function regeneratePin(Request $request, int $id): JsonResponse
    {
        abort_unless(in_array('users.manage', $request->user()->rights(), true), 403);

        $user = $this->users->getModel()->newQuery()->find($id);
        abort_if(! $user, 404);

        $pin = $this->regenPin->execute($user);
        return response()->json(['pin' => $pin]);
    }
}
