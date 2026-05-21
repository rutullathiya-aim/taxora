<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ClientForm extends Component
{
    public bool $showModal = false;

    public ?string $editingClientId = null;

    public string $client_name = '';

    public string $company_name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public bool $is_active = true;

    public ?string $service_id = null;

    public string $project_name = '';

    public ?string $due_date = null;

    #[On('create-client')]
    public function createClient(): void
    {
        if ($this->editingClientId !== null) {
            $this->resetForm();
        }
        $this->resetValidation();
        $this->showModal = true;
    }

    #[On('edit-client')]
    public function editClient(string $id): void
    {
        $this->resetForm();
        $client = Client::findOrFail($id);
        $this->editingClientId = $client->id;
        $this->client_name = $client->client_name;
        $this->company_name = $client->company_name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        $this->is_active = $client->is_active;
        $this->showModal = true;
    }

    public function rules(): array
    {
        $rules = [
            'client_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($this->editingClientId),
            ],
            'phone' => ['required', 'numeric', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
            'address' => 'required|string',
            'is_active' => 'boolean',
        ];

        if (! $this->editingClientId) {
            $rules['project_name'] = 'nullable|required_with:service_id|string|max:255';
            $rules['service_id'] = 'nullable|required_with:project_name|exists:services,id';
            $rules['due_date'] = 'nullable|date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'client_name.required' => 'Client Name is required.',
            'company_name.required' => 'Company Name is required.',
            'email.required' => 'Email Address is required.',
            'email.email' => 'Please enter a valid Email Address.',
            'email.unique' => 'This Email Address is already registered.',
            'phone.required' => 'Mobile Number is required.',
            'phone.numeric' => 'Only Numbers allowed',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'project_name.required_with' => 'Project Name and Service must be provided together.',
            'service_id.required_with' => 'Project Name and Service must be provided together.',
        ];
    }

    public function saveClient(): void
    {
        $this->validate();

        if ($this->editingClientId) {
            $client = Client::findOrFail($this->editingClientId);
            $client->update($this->only(['client_name', 'company_name', 'email', 'phone', 'address', 'is_active']));
            Flux::toast('Client updated successfully.', variant: 'success');
        } else {
            DB::transaction(function () {
                $client = Client::create($this->only(['client_name', 'company_name', 'email', 'phone', 'address', 'is_active']));

                if ($this->service_id) {
                    $project = Project::create([
                        'client_id' => $client->id,
                        'project_name' => $this->project_name,
                        'service_id' => $this->service_id,
                        'status' => 'in_progress',
                        'due_date' => $this->due_date,
                    ]);

                    $items = ServiceChecklistItem::where('service_id', $this->service_id)
                        ->where('status', 'active')
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($items as $item) {
                        ProjectChecklist::create([
                            'project_id' => $project->id,
                            'name' => $item->title,
                            'is_mandatory' => $item->is_mandatory,
                            'status' => 'Pending',
                        ]);
                    }
                }
            });

            Flux::toast('Client created successfully.', variant: 'success');
        }

        $this->resetForm();
        $this->showModal = false;
        $this->dispatch('client-saved');
    }

    public function resetForm(): void
    {
        $this->reset([
            'client_name',
            'company_name',
            'email',
            'phone',
            'address',
            'is_active',
            'editingClientId',
            'service_id',
            'project_name',
            'due_date',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clients.client-form', [
            'services' => Service::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
