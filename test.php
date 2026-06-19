<?php

use App\Models\User;
use App\Services\ServiceManager;
use App\Stats\ServiceStats;
use App\Support\UserContext;

$u = User::first();
$m = app(ServiceManager::class);
app(UserContext::class)->actingAs($u);
$stats = app(ServiceStats::class);
$stats->cards(); // this populates the cache
dump('Before create, cache has key: ' . (Cache::has('service_stats:' . $u->id) ? 'true' : 'false'));
$s = $m->create(['name' => 'Test Service ' . time(), 'status' => 'active']);
dump('After create, cache has key: ' . (Cache::has('service_stats:' . $u->id) ? 'true' : 'false'));
