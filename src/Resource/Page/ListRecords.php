<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource\Page;

use Livewire\WithPagination;
use Xslainadmin\LivewireCrud\Concerns\HasForms;
use Xslainadmin\LivewireCrud\Concerns\HasTable;
use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;
use Xslainadmin\LivewireCrud\Concerns\WithBulkActions;
use Xslainadmin\LivewireCrud\Concerns\WithCrudFilters;

/**
 * ListRecords page — the index/list view for a Resource.
 *
 * Composes the full suite of table traits: search, sort, filtering,
 * bulk actions, and form modal support for inline create/edit.
 *
 * Usage:
 *
 *   class ListProducts extends ListRecords
 *   {
 *       protected static string $resource = ProductResource::class;
 *
 *       protected function getRecordsProperty()
 *       {
 *           return $this->applyTableQuery(Product::query())->paginate($this->perPage);
 *       }
 *   }
 */
abstract class ListRecords extends Page
{
    use WithPagination;
    use InteractsWithResource;
    use HasTable;
    use HasForms;
    use WithCrudFilters;
    use WithBulkActions;

    protected string $paginationTheme = 'bootstrap';

    // -----------------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------------

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->initFormMode();
    }

    // -----------------------------------------------------------------------
    // Breadcrumbs
    // -----------------------------------------------------------------------

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => static::$resource::getPluralModelLabel(), 'url' => null],
        ];
    }

    // -----------------------------------------------------------------------
    // Page key
    // -----------------------------------------------------------------------

    protected static function pageKey(): string
    {
        return 'index';
    }

    // -----------------------------------------------------------------------
    // Helpers used by the view
    // -----------------------------------------------------------------------

    public function getPageTitle(): string
    {
        return static::$resource::getPluralModelLabel();
    }
}
