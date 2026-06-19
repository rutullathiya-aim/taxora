<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\Team\Created;
use App\Events\Team\Deleted;
use App\Events\Team\ForceDeleted;
use App\Events\Team\Restored;
use App\Events\Team\Updated;
use App\Models\User;
use App\Notifications\TeamMemberInvitation;
use App\Support\UserContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class TeamManager
{
    public function __construct(
        private UserContext $userContext
    ) {}

    public function create(array $data): User
    {
        $user = DB::transaction(function () use ($data) {
            $data['is_active'] = true;
            $data['password'] = Hash::make(Str::random(32));

            $user = User::create($data);

            $invitation = $user->invitations()->create([
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ]);

            $user->syncRoles([$data['role']]);
            $user->notify(new TeamMemberInvitation($invitation->token));

            return $user;
        });

        Created::dispatch($user, $this->userContext->get());

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user = DB::transaction(function () use ($user, $data) {
            $user->update($data);

            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user->fresh();
        });

        Updated::dispatch($user, $this->userContext->get());

        return $user;
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });

        Deleted::dispatch($user, $this->userContext->get());
    }

    public function restore(User $user): void
    {
        $user->restore();

        Restored::dispatch($user, $this->userContext->get());
    }

    public function forceDelete(User $user): void
    {
        $user->forceDelete();

        ForceDeleted::dispatch($user, $this->userContext->get());
    }

    public function resendInvitation(User $user): bool
    {
        if ($user->email_verified_at !== null) {
            return false;
        }

        $invitation = null;
        DB::transaction(function () use ($user, &$invitation) {
            $user->invitations()->delete();

            $invitation = $user->invitations()->create([
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ]);
        });

        $user->notify(new TeamMemberInvitation($invitation->token));

        return true;
    }

    public function sendPasswordReset(User $user): bool
    {
        if ($user->email_verified_at === null) {
            return false;
        }

        $status = Password::broker()->sendResetLink(
            ['email' => $user->email]
        );

        return $status === Password::RESET_LINK_SENT;
    }

    public function toggleStatus(User $user): void
    {
        $user = DB::transaction(function () use ($user) {
            $user->update([
                'is_active' => ! $user->is_active,
            ]);

            return $user;
        });

        Updated::dispatch($user, $this->userContext->get());
    }
}
