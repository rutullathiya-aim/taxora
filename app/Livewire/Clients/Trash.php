<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class Trash extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function restoreClient(string $id): void
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $client->restore();
        Flux::toast('Client restored successfully.', variant: 'success');
    }

    public function forceDeleteClient(string $id): void
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $client->forceDelete();
        Flux::toast('Client permanently deleted.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.clients.trash', [
            'clients' => Client::onlyTrashed()
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('client_name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest('deleted_at')
                ->paginate(10),
            'totalTrashed' => Client::onlyTrashed()->count(),
        ])->layout('components.layouts.app');
    }
}
