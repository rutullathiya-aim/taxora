<?php

namespace App\Livewire\Team;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\TeamMemberInvitation;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Form extends Component
{
    public bool $showModal = false;

    public ?string $userId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'staff';

    #[On('open-team-form')]
    public function open(?string $userId = null)
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->authorize('update', $user);
            $this->resetValidation();

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
        } else {
            $this->authorize('create', User::class);
            $this->reset(['userId', 'name', 'email', 'phone']);
            $this->role = 'staff';
            $this->resetValidation();
        }

        $this->showModal = true;
    }

    public function save()
    {
        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $this->authorize('update', $user);
        } else {
            $this->authorize('create', User::class);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::enum(UserRole::class)],
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
        ];

        if ($this->userId) {
            $user->update($data);
        } else {
            $data['status'] = UserStatus::PendingInvitation;
            $data['password'] = bcrypt(Str::random(32));
            $user = User::create($data);

            $invitation = $user->invitations()->create([
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ]);

            $user->notify(new TeamMemberInvitation($invitation->token));
        }

        $user->syncRoles([$this->role]);

        $this->showModal = false;
        Flux::toast($this->userId ? 'Team member updated.' : 'Team member created.', variant: 'success');

        $this->dispatch('team-member-saved');
    }

    public function render()
    {
        return view('livewire.team.form');
    }
}
