<?php

namespace App\Livewire\Team;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\TeamMemberInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = 'active';

    public bool $showDeleteModal = false;

    public ?string $userId = null;

    public function mount()
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->dispatch('open-team-form');
    }

    public function edit(string $id)
    {
        $this->dispatch('open-team-form', userId: $id);
    }

    public function confirmDelete(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->userId && $this->userId !== auth()->id()) {
            $user = User::findOrFail($this->userId);
            $this->authorize('delete', $user);
            $user->delete();
            Flux::toast('Team member deleted.', variant: 'success');
        }

        $this->showDeleteModal = false;
    }

    public function restore(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('restore', $user);
        $user->restore();
        Flux::toast('Team member restored.', variant: 'success');
    }

    public function resendInvitation(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        if ($user->status !== UserStatus::PendingInvitation) {
            Flux::toast('User is not pending invitation.', variant: 'danger');

            return;
        }

        $invitation = $user->invitations()->create([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $user->notify(new TeamMemberInvitation($invitation->token));

        Flux::toast('Invitation resent successfully.', variant: 'success');
    }

    public function sendPasswordReset(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        if ($user->status === UserStatus::PendingInvitation) {
            Flux::toast('User is pending invitation. Resend invitation instead.', variant: 'danger');

            return;
        }

        Password::broker()->sendResetLink(
            ['email' => $user->email]
        );

        Flux::toast('Password reset link sent.', variant: 'success');
    }

    public function toggleStatus(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            Flux::toast('You cannot change your own status.', variant: 'danger');

            return;
        }

        if ($user->status === UserStatus::PendingInvitation) {
            Flux::toast('Cannot toggle status of pending users.', variant: 'danger');

            return;
        }

        $user->update([
            'status' => $user->status === UserStatus::Active
                ? UserStatus::Inactive
                : UserStatus::Active,
        ]);

        Flux::toast('User status updated.', variant: 'success');
    }

    #[On('team-member-saved')]
    public function refreshList(): void
    {
        // This will trigger a re-render
    }

    public function render()
    {
        $query = User::with('invitation');

        if ($this->statusFilter === 'deleted') {
            $query->onlyTrashed();
        }

        $users = $query
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.team.index', [
            'users' => $users,
        ]);
    }
}
