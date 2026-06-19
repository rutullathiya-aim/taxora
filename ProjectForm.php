            return;
        }

        $this->authorize('create', Project::class);
    }

    private function updateProject(ProjectManager $manager, array $validated): void
    {
        $manager->update(
            project: $this->project,
            data: $this->projectData($validated),
            assigneeIds: $validated['assignees'] ?? []
        );

        Toast::success(CrudAction::Updated, ResourceType::Project);
        $this->finish();
    }

    private function storeProject(ProjectManager $manager, array $validated): void
    {
        $manager->create(
            data: $this->projectData($validated, includeService: true),
            assigneeIds: $validated['assignees'] ?? []
        );

        Toast::success(CrudAction::Created, ResourceType::Project);
        $this->finish();
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('projects.saved');
    }

    public function resetForm(): void
    {
        $this->reset([
            'client_id',
            'project_name',
            'assignees',
            'service_id',
            'due_date',
            'project',
        ]);
        $this->status = ProjectStatus::Active->value;
        $this->resetValidation();
    }

    private function sanitize(): void
    {
        $this->project_name = trim($this->project_name);
        $this->due_date = blank($this->due_date) ? null : trim($this->due_date);
    }

    private function projectData(array $validated, bool $includeService = false): array
    {
        $data = Arr::only($validated, [
            'client_id',
            'project_name',