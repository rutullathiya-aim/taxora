<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'latest';

    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    #[On('client-saved')]
    public function refreshClients(): void {}

    public function deleteClient(string $id): void
    {
        $client = Client::findOrFail($id);
        $client->delete();
        Flux::toast('Client deleted successfully.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => Client::query()
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('client_name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%');
                    });
                })
                ->when($this->sortBy === 'latest', fn ($query) => $query->latest())
                ->when($this->sortBy === 'oldest', fn ($query) => $query->oldest())
                ->when($this->sortBy === 'a_to_z', fn ($query) => $query->orderBy('client_name', 'asc'))
                ->when($this->sortBy === 'z_to_a', fn ($query) => $query->orderBy('client_name', 'desc'))
                ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
                ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
                ->paginate(10),
            'totalClients' => Client::count(),
            'activeClients' => Client::where('is_active', true)->count(),
            'inactiveClients' => Client::where('is_active', false)->count(),
        ])->layout('components.layouts.app');
    }
}
