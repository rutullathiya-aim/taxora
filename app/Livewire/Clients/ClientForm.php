<?php

declare(strict_types=1);

namespace App\Livewire\Clients;

use App\Enums\ClientStatus;
use App\Enums\CrudAction;
use App\Enums\ProjectStatus;
use App\Enums\ResourceType;
use App\Enums\ServiceStatus;
use App\Models\Client;
use App\Models\Service;
use App\Services\ClientManager;
use App\Support\Toast;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ClientForm extends Component
{
    public bool $showModal = false;

    public ?Client $client = null;

    public string $client_name = '';

    public ?string $company_name = null;

    public string $email = '';

    public string $phone = '';

    public ?string $address = null;

    public string $status = ClientStatus::Active->value;

    public ?string $service_id = null;

    public ?string $project_name = null;

    public function rules(): array
    {
        $rules = [
            'client_name' => ['required', 'string', 'min:2', 'max:150'],
            'company_name' => ['nullable', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:rfc,strict', 'max:150', Rule::unique('clients', 'email')->ignore($this->client?->id)],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', Rule::unique('clients', 'phone')->ignore($this->client?->id)],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(ClientStatus::class)],
        ];

        if ($this->client === null) {
            $rules['project_name'] = ['nullable', 'required_with:service_id', 'string', 'min:2', 'max:150'];
            $rules['service_id'] = [
                'nullable',
                'required_with:project_name',
                Rule::exists('services', 'id')->where('status', ServiceStatus::Active->value)->whereNull('deleted_at'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'client_name.required' => 'Client Name is required.',
            'client_name.min' => 'Client Name must be at least 2 characters.',
            'client_name.max' => 'Client Name cannot exceed 150 characters.',
            'company_name.min' => 'Company Name must be at least 2 characters.',
            'company_name.max' => 'Company Name cannot exceed 150 characters.',
            'email.required' => 'Email Address is required.',
            'email.email' => 'Please enter a valid Email Address.',
            'email.max' => 'Email Address cannot exceed 150 characters.',
            'email.unique' => 'This Email Address is already registered.',
            'phone.required' => 'Mobile Number is required.',
            'phone.unique' => 'This Mobile Number is already registered to another client.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'address.max' => 'Address cannot exceed 1000 characters.',
            'project_name.required_with' => 'Project Name is required when a Service is selected.',
            'project_name.min' => 'Project Name must be at least 2 characters.',
            'project_name.max' => 'Project Name cannot exceed 150 characters.',
            'service_id.required_with' => 'Project Name and Service must be provided together.',
            'service_id.exists' => 'The selected Service is invalid or inactive.',
            'status.required' => 'Status is required.',
            'status.enum' => 'Selected Status is invalid.',
        ];
    }

    #[On('create-client')]
    public function openCreateModal(): void
    {
        $this->authorize('create', Client::class);
        if ($this->client !== null) {
            $this->resetForm();
        }
        $this->resetValidation();
        $this->showModal = true;
    }

    #[On('edit-client')]
    public function openEditModal(string $id): void
    {
        $client = Client::query()->findOrFail($id);
        $this->authorize('update', $client);
        $this->resetForm();
        $this->fillFromModel($client);
        $this->showModal = true;
    }

    public function save(ClientManager $manager): void
    {
        $this->authorizeSave();
        $this->sanitize();
        $validated = $this->validate();
        $clientData = $this->clientData($validated);

        if ($this->client !== null) {
            $this->updateClient($manager, $clientData);

            return;
        }

        $this->storeClient($manager, $clientData, $validated);
    }

    private function authorizeSave(): void
    {
        if ($this->client) {
            $this->authorize('update', $this->client);

            return;
        }

        $this->authorize('create', Client::class);
    }

    private function updateClient(ClientManager $manager, array $clientData): void
    {
        $manager->update($this->client, $clientData);
        Toast::success(CrudAction::Updated, ResourceType::Client);
        $this->finish();
    }

    private function storeClient(ClientManager $manager, array $clientData, array $validated): void
    {
        $manager->create(clientData: $clientData, projectData: $this->projectData($validated));
        Toast::success(CrudAction::Created, ResourceType::Client);
        $this->finish();
    }

    private function projectData(array $validated): ?array
    {
        if (blank($validated['service_id'] ?? null)) {
            return null;
        }

        return [
            'project_name' => $validated['project_name'],
            'service_id' => $validated['service_id'],
            'status' => ProjectStatus::Active->value,
        ];
    }

    public function resetForm(): void
    {
        $this->resetDefaults();
        $this->resetValidation();
    }

    #[Computed]
    public function services(): Collection
    {
        if (! $this->showModal || $this->client !== null) {
            return collect();
        }

        return Service::query()
            ->status(ServiceStatus::Active)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    private function fillFromModel(Client $client): void
    {
        $this->client = $client;
        $this->client_name = $client->client_name;
        $this->company_name = $client->company_name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        $this->status = $client->status->value;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('clients.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset([
            'client_name',
            'company_name',
            'email',
            'phone',
            'address',
            'client',
            'service_id',
            'project_name',
        ]);

        $this->status = ClientStatus::Active->value;
    }

    private function sanitize(): void
    {
        $this->client_name = trim($this->client_name);
        $this->company_name = $this->company_name === null ? null : trim($this->company_name);
        $this->email = strtolower(trim($this->email));
        $this->phone = trim($this->phone);
        $this->address = $this->address === null ? null : trim($this->address);
        $this->project_name = $this->project_name === null ? null : trim($this->project_name);
    }

    private function clientData(array $validated): array
    {
        return Arr::only($validated, [
            'client_name',
            'company_name',
            'email',
            'phone',
            'address',
            'status',
        ]);
    }

    public function render(): View
    {
        return view('livewire.clients.client-form');
    }
}
