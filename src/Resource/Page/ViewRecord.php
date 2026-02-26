<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource\Page;

use Illuminate\Database\Eloquent\Model;
use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;

/**
 * ViewRecord page — read-only detail view for a single Resource record.
 *
 * Usage:
 *
 *   class ViewProduct extends ViewRecord
 *   {
 *       protected static string $resource = ProductResource::class;
 *   }
 */
class ViewRecord extends Page
{
    use InteractsWithResource;

    /** @var int|string The record's primary key, bound from the route. */
    public int|string $recordId;

    protected ?Model $record = null;

    // -----------------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------------

    public function mount(int|string $recordId): void
    {
        $this->recordId = $recordId;
        $this->record   = static::$resource::getModelClass()::findOrFail($recordId);

        abort_unless(static::$resource::canView($this->record), 403);
    }

    // -----------------------------------------------------------------------
    // Getters
    // -----------------------------------------------------------------------

    public function getRecord(): Model
    {
        if ($this->record === null) {
            $this->record = static::$resource::getModelClass()::findOrFail($this->recordId);
        }
        return $this->record;
    }

    // -----------------------------------------------------------------------
    // Breadcrumbs
    // -----------------------------------------------------------------------

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => static::$resource::getPluralModelLabel(), 'url' => static::$resource::getUrl('index')],
            ['label' => '#' . $this->recordId, 'url' => null],
        ];
    }

    // -----------------------------------------------------------------------
    // Page key
    // -----------------------------------------------------------------------

    protected static function pageKey(): string
    {
        return 'view';
    }

    // -----------------------------------------------------------------------
    // Render
    // -----------------------------------------------------------------------

    public function render()
    {
        return view('livewire.view-record', [
            'record'  => $this->getRecord(),
            'columns' => static::$resource::getTable()->getColumns(),
        ]);
    }
}
