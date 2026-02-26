<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract that every CRUD generator command must satisfy.
 * Implementing this interface allows the service-provider to discover and
 * register alternative generator drivers without touching the core.
 */
interface CrudGeneratorInterface
{
    /**
     * Generate the Eloquent model, Livewire component, and all related PHP
     * classes (imports, exports, notifications, etc.) for the given table.
     *
     * @return static
     */
    public function buildModel(): static;

    /**
     * Generate all Blade view files that belong to the CRUD module.
     *
     * @return static
     */
    public function buildViews(): static;
}
