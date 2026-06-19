<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show;
use App\Livewire\Dashboard;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\Services\Index as ServiceIndex;
use App\Livewire\Services\Show as ServiceShow;
use App\Livewire\Tasks\Index as TaskIndex;
use App\Livewire\Tasks\MyTasks;
use App\Livewire\Tasks\Show as TaskShow;
use App\Livewire\Team\Index;
use App\Livewire\Team\Show as TeamShow;
use App\Models\Document;
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
    Route::get('/team/{user}', TeamShow::class)->name('team.show')->withTrashed();
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('/clients', ClientIndex::class)->name('clients.index');
    Route::get('/clients/{client}', Show::class)->name('clients.show')->withTrashed();
    Route::get('/projects', ProjectIndex::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShow::class)->name('projects.show')->withTrashed();

    Route::get('/services', ServiceIndex::class)->name('services.index');
    Route::get('/services/{service}', ServiceShow::class)->name('services.show')->withTrashed();

    Route::get('/tasks', TaskIndex::class)->name('tasks.index');
    Route::get('/my-tasks', MyTasks::class)->name('tasks.my');
    Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');

    Route::get('/documents/{document}/{filename}', function (Document $document, string $filename) {
        if (! Storage::exists($document->path)) {
            abort(404, 'Document not found.');
        }

        return Storage::response($document->path, $document->name);
    })->name('documents.show');
});

require __DIR__ . '/auth.php';
