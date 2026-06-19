<?php

$dir = 'app/Events/Project';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    if (strpos($content, 'ShouldDispatchAfterCommit') !== false) {
        continue;
    }

    // Add use statement
    $content = preg_replace(
        '/use Illuminate\\\\Foundation\\\\Events\\\\Dispatchable;/',
        "use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;\nuse Illuminate\Foundation\Events\Dispatchable;",
        $content
    );

    // Add implements statement
    $content = preg_replace(
        '/class ([a-zA-Z]+)/',
        'class $1 implements ShouldDispatchAfterCommit',
        $content
    );

    file_put_contents($file, $content);
    echo "Updated $file\n";
}
