<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

use Livewire\Attributes\Url;

/**
 * Livewire trait — provides URL-persisted sort state as a lightweight
 * alternative to the full HasTable trait for components that only need
 * sort support without search or column-toggle.
 *
 * Usage:
 *
 *   use Xslainadmin\LivewireCrud\Concerns\WithSorting;
 *
 *   class SimpleList extends Component
 *   {
 *       use WithSorting;
 *
 *       public function getRowsProperty()
 *       {
 *           return Product::orderBy($this->sortColumn, $this->sortDirection)->get();
 *       }
 *   }
 */
trait WithSorting
{
    #[Url(as: 'sort', history: true, except: 'id')]
    public string $sortColumn = 'id';

    #[Url(as: 'dir', history: true, except: 'asc')]
    public string $sortDirection = 'asc';

    /**
     * Toggle sort on the given column.
     * If already sorting by this column, flip the direction.
     */
    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = 'asc';
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Return a Bootstrap Icons class for the sort indicator on a column header.
     */
    public function sortIcon(string $column): string
    {
        if ($this->sortColumn !== $column) {
            return 'bi-arrow-down-up text-muted opacity-50';
        }
        return $this->sortDirection === 'asc' ? 'bi-sort-up text-primary' : 'bi-sort-down text-primary';
    }

    /**
     * Return the ARIA sort value for a <th> header (for screen readers).
     */
    public function ariaSort(string $column): string
    {
        if ($this->sortColumn !== $column) {
            return 'none';
        }
        return $this->sortDirection === 'asc' ? 'ascending' : 'descending';
    }
}
