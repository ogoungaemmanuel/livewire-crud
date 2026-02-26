<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Xslainadmin\LivewireCrud\Filters\Filter;

/**
 * Livewire trait — manages filter state and URL persistence for components
 * that declare a $resource property pointing to a CrudResource class.
 *
 * Designed to be used alongside InteractsWithResource. The consuming component
 * only needs to declare the $resource property and call `applyFilters($query)`
 * inside its data-loading method.
 *
 * Example:
 *
 *   class Products extends Component
 *   {
 *       use InteractsWithResource;
 *       use WithCrudFilters;
 *
 *       protected static string $resource = ProductResource::class;
 *
 *       public function getProductsProperty(): LengthAwarePaginator
 *       {
 *           return $this->applyFilters(Product::query())->paginate();
 *       }
 *   }
 *
 * URL binding is provided via the `$activeFilters` property, which is
 * serialised as a single JSON query-string parameter (`?filters=…`) so that
 * a share-able URL always reflects the current filter state.
 */
trait WithCrudFilters
{
    /**
     * Persisted filter values keyed by filter name.
     *
     * The #[Url] attribute syncs this array with the ?filters= query-string
     * parameter automatically (requires Livewire 3.x).
     */
    #[Url(as: 'filters', history: true, except: [])]
    public array $activeFilters = [];

    // -----------------------------------------------------------------------
    // Boot / lifecycle
    // -----------------------------------------------------------------------

    /**
     * Mount hook — initialises $activeFilters from the Resource's filter list
     * so every filter key exists even before the user interacts with the form.
     */
    public function bootWithCrudFilters(): void
    {
        $this->initFilterDefaults();
    }

    // -----------------------------------------------------------------------
    // Initialisation
    // -----------------------------------------------------------------------

    /**
     * Populate $activeFilters with an empty-string default for each filter
     * declared in the Resource, preserving any value already set (e.g. from
     * the URL).
     */
    public function initFilterDefaults(): void
    {
        if (empty(static::$resource)) {
            return;
        }

        /** @var class-string<\Xslainadmin\LivewireCrud\Resource\CrudResource> $resource */
        $resource = static::$resource;

        foreach ($resource::getTable()->getFilters() as $filter) {
            /** @var Filter $filter */
            if (!array_key_exists($filter->getName(), $this->activeFilters)) {
                $this->activeFilters[$filter->getName()] = '';
            }
        }
    }

    // -----------------------------------------------------------------------
    // Query application
    // -----------------------------------------------------------------------

    /**
     * Apply all active (non-empty) filters from the Resource to the given
     * Eloquent builder. Each filter delegates the WHERE clause to the
     * Filter::apply() contract if present, otherwise falls back to a simple
     * equality check on the filter's column.
     *
     * @param  Builder $query
     * @return Builder
     */
    public function applyFilters(Builder $query): Builder
    {
        if (empty(static::$resource)) {
            return $query;
        }

        /** @var class-string<\Xslainadmin\LivewireCrud\Resource\CrudResource> $resource */
        $resource = static::$resource;

        foreach ($resource::getTable()->getFilters() as $filter) {
            /** @var Filter $filter */
            $name  = $filter->getName();
            $value = $this->activeFilters[$name] ?? '';

            if ($value === '' || $value === null) {
                continue;
            }

            // If the Filter subclass exposes an apply() method, delegate to it.
            // Otherwise fall back to a simple equality on the mapped column.
            if (method_exists($filter, 'apply')) {
                $filter->apply($query, $value);
            } else {
                $query->where($filter->getColumn(), $value);
            }
        }

        return $query;
    }

    // -----------------------------------------------------------------------
    // Reset
    // -----------------------------------------------------------------------

    /**
     * Clear all active filter values and reset to defaults.
     */
    public function resetFilters(): void
    {
        $this->activeFilters = [];
        $this->initFilterDefaults();
    }

    /**
     * Reset a single filter by name.
     */
    public function resetFilter(string $name): void
    {
        $this->activeFilters[$name] = '';
    }

    // -----------------------------------------------------------------------
    // Helpers for Blade
    // -----------------------------------------------------------------------

    /**
     * Returns true if any filter is currently active (has a non-empty value).
     */
    public function hasActiveFilters(): bool
    {
        foreach ($this->activeFilters as $value) {
            if ($value !== '' && $value !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an array of [label, value, name] for each active filter,
     * suitable for rendering an "active filter badges" bar in the view.
     *
     * @return array<int, array{name: string, label: string, value: string}>
     */
    public function activeFilterBadges(): array
    {
        if (empty(static::$resource)) {
            return [];
        }

        /** @var class-string<\Xslainadmin\LivewireCrud\Resource\CrudResource> $resource */
        $resource = static::$resource;

        $badges = [];

        foreach ($resource::getTable()->getFilters() as $filter) {
            /** @var Filter $filter */
            $name  = $filter->getName();
            $value = $this->activeFilters[$name] ?? '';

            if ($value !== '' && $value !== null) {
                $badges[] = [
                    'name'  => $name,
                    'label' => ucwords(str_replace(['-', '_'], ' ', $name)),
                    'value' => (string) $value,
                ];
            }
        }

        return $badges;
    }
}
