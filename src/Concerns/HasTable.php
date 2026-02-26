<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

use Livewire\Attributes\Url;
use Xslainadmin\LivewireCrud\Schema\TableSchema;

/**
 * Livewire trait — provides full-featured table state management for components
 * that define a $resource pointing to a CrudResource subclass.
 *
 * Provides:
 *  - Search with URL persistence
 *  - Sort column + direction with URL persistence
 *  - Per-page selection
 *  - Column toggle (show/hide) persistence per user session
 *  - Query builder integration via the Resource's applySearch() + applySort()
 *
 * Usage in a Livewire component:
 *
 *   use Xslainadmin\LivewireCrud\Concerns\HasTable;
 *   use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;
 *
 *   class Users extends Component
 *   {
 *       use InteractsWithResource, HasTable;
 *
 *       protected static string $resource = UserResource::class;
 *
 *       public function getRecords(): LengthAwarePaginator
 *       {
 *           return $this->applyTableQuery(User::query())->paginate($this->perPage);
 *       }
 *   }
 */
trait HasTable
{
    // ── Public state (URL-bound) ──────────────────────────────────────────

    #[Url(as: 'search', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'sort', history: true, except: '')]
    public string $sortColumn = '';

    #[Url(as: 'dir', history: true, except: 'asc')]
    public string $sortDirection = 'asc';

    /** Current records-per-page selection. */
    public int $perPage = 10;

    /** Column names the user has toggled off. */
    public array $hiddenColumns = [];

    // ── Lifecycle ────────────────────────────────────────────────────────

    public function bootHasTable(): void
    {
        // Initialise sort defaults from the Resource's TableSchema
        if ($this->sortColumn === '' && isset(static::$resource)) {
            $table = static::$resource::getTable();
            $this->sortColumn    = $table->getDefaultSort();
            $this->sortDirection = $table->getDefaultSortDir();
        }
    }

    // ── Sorting ──────────────────────────────────────────────────────────

    /** Toggle sort on a column (called from the view's th @click handler). */
    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── Search ───────────────────────────────────────────────────────────

    /** Called when the search input changes (wire:model.live). */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // __ Per-page __

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // __ Column visibility __

    public function toggleColumn(string $column): void
    {
        if (in_array($column, $this->hiddenColumns, true)) {
            $this->hiddenColumns = array_values(
                array_filter($this->hiddenColumns, fn ($c) => $c !== $column)
            );
        } else {
            $this->hiddenColumns[] = $column;
        }
    }

    public function isColumnVisible(string $column): bool
    {
        return !in_array($column, $this->hiddenColumns, true);
    }

    // __ Query builder helper __

    /**
     * Apply search + sort to any Eloquent Builder.
     * Call this inside your getRecords() / data() method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyTableQuery($query)
    {
        if ($this->search !== '' && isset(static::$resource)) {
            static::$resource::applySearch($query, $this->search);
        }

        if ($this->sortColumn !== '') {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        }

        return $query;
    }

    // __ Helpers __

    protected function getTableSchema(): TableSchema
    {
        return static::$resource::getTable();
    }

    /** Sort icon for a given column header (Bootstrap Icons). */
    public function sortIcon(string $column): string
    {
        if ($this->sortColumn !== $column) {
            return 'bi-arrow-down-up text-muted';
        }
        return $this->sortDirection === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
    }

    // __ Trait requirement (must use Livewire's WithPagination) __

    abstract public function resetPage(): void;
}
