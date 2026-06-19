<?php

declare(strict_types=1);

namespace App\Livewire\Services;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Models\Service;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasServiceActions
{
    use AuthorizesRequests;

    #[On('delete-service')]
    public function deleteService(string $id): void
    {
        $service = Service::query()->findOrFail($id);
        $this->authorize('delete', $service);
        $this->serviceManager->delete($service);
        $this->invokeAfterServiceAction(CrudAction::Deleted, $service);
        Flux::toast(CrudAction::Deleted->message(ResourceType::Service), variant: 'success');
    }

    #[On('restore-service')]
    public function restoreService(string $id): void
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $service);
        $this->serviceManager->restore($service);
        $this->invokeAfterServiceAction(CrudAction::Restored, $service);
        Flux::toast(CrudAction::Restored->message(ResourceType::Service), variant: 'success');
    }

    #[On('force-delete-service')]
    public function forceDeleteService(string $id): void
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $service);
        $this->serviceManager->forceDelete($service);
        $this->invokeAfterServiceAction(CrudAction::ForceDeleted, $service);
        Flux::toast(CrudAction::ForceDeleted->message(ResourceType::Service), variant: 'success');
    }

    private function invokeAfterServiceAction(CrudAction $action, Service $service): void
    {
        if (method_exists($this, 'afterServiceAction')) {
            $this->afterServiceAction($action, $service);
        }
    }
}
