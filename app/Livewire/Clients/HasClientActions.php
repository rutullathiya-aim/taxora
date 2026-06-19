<?php

declare(strict_types=1);

namespace App\Livewire\Clients;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Models\Client;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasClientActions
{
    use AuthorizesRequests;

    #[On('delete-client')]
    public function deleteClient(string $id): void
    {
        $client = Client::query()->findOrFail($id);
        $this->authorize('delete', $client);
        $this->clientManager->delete($client);
        $this->invokeAfterClientAction(CrudAction::Deleted, $client);
        Flux::toast(CrudAction::Deleted->message(ResourceType::Client), variant: 'success');
    }

    #[On('restore-client')]
    public function restoreClient(string $id): void
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $client);
        $this->clientManager->restore($client);
        $this->invokeAfterClientAction(CrudAction::Restored, $client);
        Flux::toast(CrudAction::Restored->message(ResourceType::Client), variant: 'success');
    }

    #[On('force-delete-client')]
    public function forceDeleteClient(string $id): void
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $client);
        $this->clientManager->forceDelete($client);
        $this->invokeAfterClientAction(CrudAction::ForceDeleted, $client);
        Flux::toast(CrudAction::ForceDeleted->message(ResourceType::Client), variant: 'success');
    }

    private function invokeAfterClientAction(CrudAction $action, Client $client): void
    {
        if (method_exists($this, 'afterClientAction')) {
            $this->afterClientAction($action, $client);
        }
    }
}
