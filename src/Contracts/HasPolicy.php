<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract for Resource classes that bind to a Laravel Policy.
 *
 * Implementing this interface opts the Resource into automatic policy-based
 * authorization checks. The package's authorization helpers call through to
 * the bound policy rather than using raw permissions strings, giving you the
 * full power of Laravel's Gate.
 */
interface HasPolicy
{
    /**
     * Return the fully-qualified Policy class for this Resource.
     * Returning null disables policy-based authorization for the Resource.
     *
     * Example:
     *   return UserPolicy::class;
     */
    public static function getPolicy(): ?string;

    /**
     * Return the guard name to use for authorization checks.
     * Defaults to the value in config('livewire-crud.permissions.guard').
     */
    public static function getAuthorizationGuard(): string;
}
