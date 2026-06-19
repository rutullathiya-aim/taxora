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
        $this->phone = $user->phone ?? '';
        $this->role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('teams.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset([
            'name',
            'email',
            'phone',
            'user',
        ]);

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
