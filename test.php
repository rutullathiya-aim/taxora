<?php
$html = file_get_contents('resources/views/livewire/clients/show.blade.php');
$open = substr_count($html, '<div');
$close = substr_count($html, '</div');
echo "divs open: $open close: $close\n";
