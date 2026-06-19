        $data['project_id'] = $validated['project_id'] ?: null;

        return $data;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('tasks.saved');
    }

    public function resetForm(): void
    {
        $this->reset([
            'task',
            'title',
            'description',
            'status',
            'priority',
            'due_at',
            'assigned_to',
            'client_id',
            'project_id',
        ]);

        $this->status = TaskStatus::Todo->value;
        $this->priority = TaskPriority::Medium->value;
        $this->resetValidation();
    }

    #[Computed]
    public function statuses(): array
    {
        return TaskStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return TaskPriority::cases();
    }

    #[Computed]
    public function users(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->whereNotNull('email_verified_at')
                    ->where('role', '!=', UserRole::Admin->value)
                    ->when($this->context()->get()->isManager(), fn ($q) => $q->where('role', '!=', UserRole::Manager->value));

                if ($this->task && ! empty($this->assigned_to)) {
                    $query->orWhereIn('id', $this->assigned_to);
                }
            })
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();
    }