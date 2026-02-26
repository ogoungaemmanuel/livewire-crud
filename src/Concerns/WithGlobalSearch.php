<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

use Illuminate\Support\Collection;
use Xslainadmin\LivewireCrud\Contracts\GloballySearchable;
use Xslainadmin\LivewireCrud\ResourceRegistry;

/**
 * Livewire trait — powers a global search overlay that queries all registered
 * Resources that implement the GloballySearchable contract.
 *
 * Usage: add to your top-level layout Livewire component or a standalone
 * GlobalSearch component.
 *
 *   use Xslainadmin\LivewireCrud\Concerns\WithGlobalSearch;
 *
 *   class TopBar extends Component
 *   {
 *       use WithGlobalSearch;
 *
 *       // Render the search input and results dropdown
 *   }
 */
trait WithGlobalSearch
{
    public string $globalSearch = '';

    /** Minimum characters before search fires. */
    protected int $globalSearchMinChars = 2;

    /** Max results per Resource. */
    protected int $globalSearchMaxPerResource = 5;

    /**
     * Called automatically when the search input changes.
     * Results are computed lazily via the `results` property.
     */
    public function updatedGlobalSearch(): void
    {
        // Livewire reactivity handles view re-render automatically.
        // No explicit action needed; results computed in getResultsProperty().
    }

    /**
     * Execute the global search and return collated results.
     *
     * @return Collection<int, array{title: string, detail: string, url: string, resource: string}>
     */
    public function getGlobalSearchResultsProperty(): Collection
    {
        if (strlen($this->globalSearch) < $this->globalSearchMinChars) {
            return collect();
        }

        $term    = $this->globalSearch;
        $results = collect();

        foreach (ResourceRegistry::all() as $resourceClass) {
            if (!in_array(GloballySearchable::class, class_implements($resourceClass) ?: [], true)) {
                continue;
            }

            /** @var GloballySearchable&\Xslainadmin\LivewireCrud\Resource\CrudResource $resourceClass */
            $model      = $resourceClass::getModelClass();
            $attributes = $resourceClass::getGloballySearchableAttributes();

            if (empty($attributes)) {
                continue;
            }

            $query = $model::query();
            $query->where(function ($q) use ($attributes, $term) {
                foreach ($attributes as $attr) {
                    $q->orWhere($attr, 'like', "%{$term}%");
                }
            });

            $query->limit($this->globalSearchMaxPerResource)
                  ->get()
                  ->each(function ($record) use ($resourceClass, $results) {
                      $results->push([
                          'title'    => $resourceClass::getGlobalSearchResultTitle($record),
                          'details'  => $resourceClass::getGlobalSearchResultDetails($record),
                          'url'      => $resourceClass::getGlobalSearchResultUrl($record),
                          'resource' => $resourceClass::getNavigationLabel(),
                      ]);
                  });
        }

        return $results;
    }

    public function clearGlobalSearch(): void
    {
        $this->globalSearch = '';
    }
}
