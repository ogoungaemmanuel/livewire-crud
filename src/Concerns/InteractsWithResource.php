<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

use Xslainadmin\LivewireCrud\Contracts\ResourceContract;
use Xslainadmin\LivewireCrud\Resource\CrudResource;
use Xslainadmin\LivewireCrud\Schema\FormSchema;
use Xslainadmin\LivewireCrud\Schema\TableSchema;

/**
 * Livewire component trait that connects a generated Livewire component to its
 * paired Resource class for column/field/filter/action definitions.
 *
 * Usage in a generated Livewire component:
 *
 *   use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;
 *
 *   class UserComponent extends Component
 *   {
 *       use InteractsWithResource;
 *
 *       protected static string $resource = UserResource::class;
 *   }
 */
trait InteractsWithResource
{
    /**
     * The fully-qualified Resource class.
     * Set this in the concrete Livewire component.
     *
     * @var class-string<CrudResource>
     */
    protected static string $resource = '';

    // -----------------------------------------------------------------------
    // Resource accessors
    // -----------------------------------------------------------------------

    /**
     * Return the Resource class string.
     *
     * @return class-string<CrudResource>
     */
    public static function getResource(): string
    {
        return static::$resource;
    }

    /**
     * Return the fully configured FormSchema for the linked Resource.
     */
    public function getForm(): FormSchema
    {
        return static::getResource()::getForm();
    }

    /**
     * Return the fully configured TableSchema for the linked Resource.
     */
    public function getTable(): TableSchema
    {
        return static::getResource()::getTable();
    }

    /**
     * Return the page title for the component's index view.
     */
    public function getPageTitle(): string
    {
        return static::getResource()::getPluralModelLabel();
    }

    /**
     * Return the model label (singular) for modal headings.
     */
    public function getModelLabel(): string
    {
        return static::getResource()::getModelLabel();
    }

    /**
     * Return the navigation icon for use in the sidebar.
     */
    public function getNavigationIcon(): string
    {
        return static::getResource()::getNavigationIcon();
    }

    // -----------------------------------------------------------------------
    // Dynamic property registration
    // -----------------------------------------------------------------------

    /**
     * Automatically initialise filter Livewire properties from the Resource's
     * TableSchema filters.  Call this from mount() or hydrate() in the
     * generated component.
     *
     * Example usage in mount():
     *   $this->initResourceFilters();
     */
    public function initResourceFilters(): void
    {
        foreach ($this->getTable()->getFilters() as $filter) {
            $prop = 'filter' . ucfirst($filter->getName());
            if (!isset($this->{$prop})) {
                $this->{$prop} = '';
            }
        }
    }

    // -----------------------------------------------------------------------
    // Search helper
    // -----------------------------------------------------------------------

    /**
     * Apply the Resource's searchable columns to an Eloquent query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $term
     */
    public function applyResourceSearch($query, string $term): void
    {
        if (trim($term) === '') {
            return;
        }
        static::getResource()::applySearch($query, $term);
    }

    // -----------------------------------------------------------------------
    // Per-page helper
    // -----------------------------------------------------------------------

    public function getResourcePerPage(): int
    {
        return static::getResource()::getRecordsPerPage();
    }
}
