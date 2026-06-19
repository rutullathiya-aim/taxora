<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Events\Service\Created;
use App\Events\Service\Deleted;
use App\Events\Service\ForceDeleted;
use App\Events\Service\Restored;
use App\Events\Service\Updated;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Support\UserContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceManager
{
    public function __construct(
        private readonly UserContext $userContext,
    ) {}

    public function create(array $serviceData): Service
    {
        $service = DB::transaction(function () use ($serviceData) {
            $serviceData['slug'] = $this->generateSlug($serviceData['name']);
            $serviceData['created_by'] ??= $this->userContext->getId();

            return Service::create($serviceData);
        });

        Created::dispatch($service, $this->userContext->get());

        return $service;
    }

    public function update(Service $service, array $serviceData): Service
    {
        $service = DB::transaction(function () use ($service, $serviceData) {
            if (isset($serviceData['name'])) {
                $serviceData['slug'] = $this->generateSlug($serviceData['name'], $service->id);
            }

            $service->update($serviceData);

            return $service->fresh();
        });

        Updated::dispatch($service, $this->userContext->get());

        return $service;
    }

    public function delete(Service $service): void
    {
        DB::transaction(function () use ($service) {
            $service->update(['status' => ServiceStatus::Inactive->value]);
            $service->delete();
        });

        Deleted::dispatch($service, $this->userContext->get());
    }

    public function restore(Service $service): void
    {
        $service->restore();

        Restored::dispatch($service, $this->userContext->get());
    }

    public function forceDelete(Service $service): void
    {
        $service->forceDelete();

        ForceDeleted::dispatch($service, $this->userContext->get());
    }

    public function createChecklistItem(Service $service, array $itemData): ServiceChecklistItem
    {
        $itemData['sort_order'] ??= ($service->checklistItems()->max('sort_order') ?? 0) + 1;

        return $service->checklistItems()->create($itemData);
    }

    public function updateChecklistItem(ServiceChecklistItem $item, array $itemData): ServiceChecklistItem
    {
        $item->update($itemData);

        return $item;
    }

    public function deleteChecklistItem(ServiceChecklistItem $item): void
    {
        $item->delete();
    }

    public function reorderChecklistItems(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $position => $id) {
                ServiceChecklistItem::where('id', $id)->update(['sort_order' => $position + 1]);
            }
        });
    }

    private function generateSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            Service::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
