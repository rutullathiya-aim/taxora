<?php

namespace App\Listeners;

use App\Events\ClientForceDeleted;
use Illuminate\Support\Facades\Storage;

class DeleteClientFiles
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClientForceDeleted $event): void
    {
        Storage::deleteDirectory($event->client->documentsDirectory());
    }
}
