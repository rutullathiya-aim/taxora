<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show;
use App\Livewire\Clients\Trash;
use App\Livewire\Dashboard;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\Services\Index as ServiceIndex;
use App\Livewire\Services\Show as ServiceShow;
use App\Livewire\Team\Index;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/invitation/{token}', AcceptInvitation::class)->name('invitation.accept');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('/team', Index::class)->name('team.index');
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('/clients/trash', Trash::class)->name('clients.trash');
    Route::get('/clients', ClientIndex::class)->name('clients.index');
    Route::get('/clients/{client}', Show::class)->name('clients.show');
    Route::get('/projects/trash', App\Livewire\Projects\Trash::class)->name('projects.trash');
    Route::get('/projects', ProjectIndex::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');

    Route::get('/services', ServiceIndex::class)->name('services.index');
    Route::get('/services/{service}', ServiceShow::class)->name('services.show');

    Route::get('/documents/{document}/{filename}', function (ClientDocument $document, string $filename) {
        if (! Storage::disk('local')->exists($document->path)) {
            abort(404, 'Document not found.');
        }

        $mime = Storage::disk('local')->mimeType($document->path);
        $path = Storage::disk('local')->path($document->path);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$document->name.'"',
        ]);
    })->name('documents.show');
});

require __DIR__.'/auth.php';
