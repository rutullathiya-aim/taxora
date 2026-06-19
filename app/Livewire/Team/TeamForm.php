<?php

declare(strict_types=1);

namespace App\Livewire\Team;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\TeamManager;
use App\Support\Toast;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TeamForm extends Component
{
    public bool $showModal = false;

    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = UserRole::Staff->value;

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:rfc,strict', 'max:150', Rule::unique('users')->ignore($this->user?->id)],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', Rule::unique('users')->ignore($this->user?->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];

        if (auth()->user()->isManager()) {
            $rules['role'] = ['required', Rule::in([UserRole::Staff->value])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 150 characters.',
            'email.required' => 'Email Address is required.',
            'email.email' => 'Please enter a valid Email Address.',
            'email.max' => 'Email Address cannot exceed 150 characters.',
            'email.unique' => 'This Email Address is already registered.',
            'phone.required' => 'Mobile Number is required.',
            'phone.unique' => 'This Mobile Number is already registered to another user.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'role.required' => 'Role is required.',
            'role.enum' => 'Please select a valid Role.',
            'role.in' => 'You can only assign the Staff role.',
        ];
    }

    #[On('create-team-member')]
    public function openCreateModal(): void
    {
        $this->authorize('create', User::class);
        if ($this->user !== null) {
            $this->resetForm();
        }
        $this->resetValidation();
        $this->showModal = true;
    }

    #[On('edit-team-member')]
    public function openEditModal(string $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);

        $this->resetForm();
        $this->fillFromModel($user);
        $this->showModal = true;
    }

    public function save(TeamManager $manager): void
    {
        $this->authorizeSave();
        $this->sanitize();
        $validated = $this->validate();
        $userData = $this->userData($validated);

        if ($this->user !== null) {
            $this->updateTeamMember($manager, $userData);

            return;
        }

        $this->storeTeamMember($manager, $userData);
    }

    private function authorizeSave(): void
    {
        if ($this->user) {
            $this->authorize('update', $this->user);

            return;
        }

        $this->authorize('create', User::class);
    }

    private function updateTeamMember(TeamManager $manager, array $userData): void
    {
        $manager->update($this->user, $userData);
        Toast::success(CrudAction::Updated, ResourceType::Team);
        $this->finish();
    }

    private function storeTeamMember(TeamManager $manager, array $userData): void
    {
        $manager->create($userData);
        Toast::success(CrudAction::Created, ResourceType::Team);
        $this->finish();
    }

    public function resetForm(): void
    {
        $this->resetDefaults();
        $this->resetValidation();
    }

    private function fillFromModel(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role->value;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('teams.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset(['user', 'name', 'email', 'phone']);
        $this->role = UserRole::Staff->value;
    }

    private function sanitize(): void
    {
        $this->name = trim($this->name);
        $this->email = strtolower(trim($this->email));
        $this->phone = trim($this->phone);
    }

    private function userData(array $validated): array
    {
        return Arr::only($validated, [
            'name',
            'email',
            'phone',
            'role',
        ]);
    }

    public function render(): View
    {
        return view('livewire.team.team-form');
    }
}
