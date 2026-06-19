<?php

$files = [
    'app/Models/Task.php',
    'app/Models/Service.php',
    'app/Models/Project.php',
    'app/Models/Document.php',
    'app/Livewire/Tasks/Index.php',
    'app/Livewire/Projects/Index.php',
    'app/Livewire/Documents/Index.php',
    'app/Livewire/Clients/Show.php',
    'resources/views/components/tasks/table.blade.php',
    'resources/views/components/tasks/stats.blade.php',
    'resources/views/components/projects/table.blade.php',
    'resources/views/components/projects/stats.blade.php',
    'resources/views/components/documents/grid.blade.php',
    'resources/views/components/documents/stats.blade.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('App\Enums\ListFilter', 'App\Enums\ClientListStatus', $content);
        $content = str_replace('ListFilter::Deleted', 'ClientListStatus::Deleted', $content);
        $content = str_replace('ListFilter::All', 'ClientListStatus::All', $content);
        $content = str_replace('ListFilter::options', 'ClientListStatus::options', $content);
        $content = str_replace('use App\Enums\ListFilter;', 'use App\Enums\ClientListStatus;', $content);
        $content = str_replace('|ListFilter', '|ClientListStatus', $content);
        file_put_contents($file, $content);
        echo 'Updated ' . $file . "\n";
    }
}
