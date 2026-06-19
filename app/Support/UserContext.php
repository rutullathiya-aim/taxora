<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

final class UserContext
{
    private ?User $user = null;

    /**
     * Set a specific user context (useful for impersonation, CLI, or tests).
     */
    public function actingAs(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the current user or null if not authenticated.
     */
    public function get(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        /** @var User|null $authUser */
        $authUser = Auth::user();

        return $authUser;
    }

    /**
     * Get the current user or throw an exception if not authenticated.
     * Use this when a user is absolutely required for the action to proceed.
     *
     * @throws AuthenticationException
     */
    public function user(): User
    {
        $user = $this->get();

        if ($user === null) {
            throw new AuthenticationException('No authenticated user found in the current context.');
        }

        return $user;
    }

    /**
     * Get the current user's ID or throw an exception if not authenticated.
     *
     * @throws AuthenticationException
     */
    public function id(): string
    {
        return $this->user()->id;
    }

    /**
     * Get the current user's ID or null if not authenticated.
     */
    public function getId(): ?string
    {
        return $this->get()?->id;
    }
}
