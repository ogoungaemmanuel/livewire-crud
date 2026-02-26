<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

/**
 * Livewire trait — manages selected row IDs and bulk-action dispatch.
 *
 * Works alongside HasTable. The `$selectedIds` array is kept in sync with
 * an Alpine.js `selectedIds` data property via `wire:model`.
 *
 * Usage:
 *
 *   use Xslainadmin\LivewireCrud\Concerns\HasTable;
 *   use Xslainadmin\LivewireCrud\Concerns\WithBulkActions;
 *
 *   class Products extends Component
 *   {
 *       use HasTable, WithBulkActions;
 *
 *       public function bulkDelete(): void
 *       {
 *           Product::whereIn('id', $this->selectedIds)->delete();
 *           $this->clearSelection();
 *           $this->dispatch('notify', message: 'Records deleted.');
 *       }
 *   }
 */
trait WithBulkActions
{
    /** The row IDs the user has selected via checkboxes. */
    public array $selectedIds = [];

    /** Whether the "select all on this page" master checkbox is checked. */
    public bool $selectAll = false;

    // __ Lifecycle __

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectCurrentPage();
        } else {
            $this->selectedIds = [];
        }
    }

    /**
     * Select all IDs visible on the current page.
     * Override this method if you use a custom records property name.
     */
    protected function selectCurrentPage(): void
    {
        // The concrete component must implement getRecords() or a
        // paginator-returning computed property; we pull IDs from it.
        if (method_exists($this, 'getRecords')) {
            $this->selectedIds = $this->getRecords()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
        }
    }

    /** De-select all rows and reset the master checkbox. */
    public function clearSelection(): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    /** Check whether a specific row ID is currently selected. */
    public function isSelected(int|string $id): bool
    {
        return in_array((string) $id, array_map('strval', $this->selectedIds), true);
    }

    /** Count of currently selected rows. */
    public function selectedCount(): int
    {
        return count($this->selectedIds);
    }

    /**
     * Guard helper — throws if no rows are selected.
     *
     * @throws \RuntimeException
     */
    protected function requireSelection(): void
    {
        if (empty($this->selectedIds)) {
            throw new \RuntimeException('No records selected for bulk action.');
        }
    }
}
