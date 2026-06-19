<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Support\UserContext;

trait InteractsWithUserContext
{
    private ?UserContext $resolvedUserContext = null;

    /**
     * Get the current user context.
     */
    protected function context(): UserContext
    {
        return $this->resolvedUserContext ??= app(UserContext::class);
    }
}
