<?php

declare(strict_types=1);

namespace App\Livewire\Team;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Models\User;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasTeamActions
{
    use AuthorizesRequests;

    private function ensureNotCurrentUser(string $id): bool
    {
        if ($id === (string) auth()->id()) {
            Flux::toast('You cannot perform this action on your own account.', variant: 'danger');

            return false;
        }

        return true;
    }

    #[On('delete-team-member')]
    public function deleteTeamMember(string $id): void
    {
        if (! $this->ensureNotCurrentUser($id)) {
            return;
        }

        $user = User::query()->findOrFail($id);
        $this->authorize('delete', $user);
        $this->teamManager->delete($user);
        $this->invokeAfterTeamAction(CrudAction::Deleted, $user);
        Flux::toast(CrudAction::Deleted->message(ResourceType::Team), variant: 'success');
    }

    #[On('restore-team-member')]
    public function restoreTeamMember(string $id): void
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $user);
        $this->teamManager->restore($user);
        $this->invokeAfterTeamAction(CrudAction::Restored, $user);
        Flux::toast(CrudAction::Restored->message(ResourceType::Team), variant: 'success');
    }

    #[On('force-delete-team-member')]
    public function forceDeleteTeamMember(string $id): void
    {
        if (! $this->ensureNotCurrentUser($id)) {
            return;
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);
        $this->teamManager->forceDelete($user);
        $this->invokeAfterTeamAction(CrudAction::ForceDeleted, $user);
        Flux::toast(CrudAction::ForceDeleted->message(ResourceType::Team), variant: 'success');
    }

    public function resendInvitation(string $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);
        if ($this->teamManager->resendInvitation($user)) {
            Flux::toast('Invitation resent successfully.', variant: 'success');
        } else {
            Flux::toast('Team member is not pending invitation.', variant: 'danger');
        }
    }

    public function sendPasswordReset(string $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);

        if ($user->email_verified_at === null) {
            Flux::toast('Team member has not accepted the invitation yet. Resend invitation instead.', variant: 'danger');

            return;
        }

        if ($this->teamManager->sendPasswordReset($user)) {
            Flux::toast('Password reset link sent successfully.', variant: 'success');
        } else {
            Flux::toast('Failed to send password reset link.', variant: 'danger');
        }
    }

    public function toggleStatus(string $id): void
    {
        if (! $this->ensureNotCurrentUser($id)) {
            return;
        }

        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);

        if ($user->email_verified_at === null) {
            Flux::toast('Cannot toggle status of pending team members.', variant: 'danger');

            return;
        }

        $this->teamManager->toggleStatus($user);

        Flux::toast('Team member status updated successfully.', variant: 'success');
    }

    private function invokeAfterTeamAction(CrudAction $action, User $user): void
    {
        if (method_exists($this, 'afterTeamAction')) {
            $this->afterTeamAction($action, $user);
        }
    }
}
