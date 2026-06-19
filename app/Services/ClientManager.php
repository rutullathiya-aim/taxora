<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClientStatus;
use App\Events\Client\Created;
use App\Events\Client\Deleted;
use App\Events\Client\ForceDeleted;
use App\Events\Client\Restored;
use App\Events\Client\Updated;
use App\Models\Client;
use App\Support\UserContext;
use Illuminate\Support\Facades\DB;

final readonly class ClientManager
{
    public function __construct(
        private ProjectManager $projectManager,
        private UserContext $userContext,
    ) {}

    public function create(array $clientData, ?array $projectData = null, ?array $assigneeIds = null): Client
    {
        $client = DB::transaction(function () use ($clientData, $projectData, $assigneeIds) {
            $clientData['created_by'] ??= $this->userContext->getId();
            $client = Client::create($clientData);

            if ($projectData !== null) {
                $projectData['client_id'] = $client->id;

                if ($assigneeIds === null && $client->created_by !== null) {
                    $assigneeIds = [$client->created_by];
                }

                $this->projectManager->create($projectData, $assigneeIds ?? []);
            }

            return $client;
        });

        Created::dispatch($client, $this->userContext->get());

        return $client;
    }

    public function update(Client $client, array $clientData): Client
    {
        $client = DB::transaction(function () use ($client, $clientData) {
            $client->update($clientData);

            return $client->fresh();
        });

        Updated::dispatch($client, $this->userContext->get());

        return $client;
    }

    public function delete(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $client->update(['status' => ClientStatus::Inactive->value]);
            $client->delete();
        });

        Deleted::dispatch($client, $this->userContext->get());
    }

    public function restore(Client $client): void
    {
        $client->restore();

        Restored::dispatch($client, $this->userContext->get());
    }

    public function forceDelete(Client $client): void
    {
        $client->forceDelete();

        ForceDeleted::dispatch($client, $this->userContext->get());
    }
}
