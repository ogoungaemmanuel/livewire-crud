<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource\Page;

use Illuminate\Database\Eloquent\Model;
use Xslainadmin\LivewireCrud\Concerns\HasForms;
use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;

/**
 * EditRecord page — dedicated edit form page for a Resource.
 *
 * Usage:
 *
 *   class EditProduct extends EditRecord
 *   {
 *       protected static string $resource = ProductResource::class;
 *
 *       public function save(): void
 *       {
 *           $this->validate($this->formRules());
 *           $this->getRecord()->update($this->getFormData());
 *           $this->redirect(ListProducts::getUrl());
 *       }
 *   }
 */
abstract class EditRecord extends Page
{
    use InteractsWithResource;
    use HasForms;

    /** @var int|string The record's primary key, bound from the route. */
    public int|string $recordId;

    /** @var Model|null The hydrated record. */
    protected ?Model $record = null;

    // -----------------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------------

    public function mount(int|string $recordId): void
    {
        $this->recordId = $recordId;
        $this->record   = static::$resource::getModelClass()::findOrFail($recordId);

        abort_unless(static::$resource::canEdit($this->record), 403);

        $this->formMode   = 'edit';
        $this->formRecord = (int) $recordId;
        $this->hydrateFormFrom($recordId);
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
            ['label' => 'Edit #' . $this->recordId, 'url' => null],
        ];
    }

    // -----------------------------------------------------------------------
    // Page key
    // -----------------------------------------------------------------------

    protected static function pageKey(): string
    {
        return 'edit';
    }

    // -----------------------------------------------------------------------
    // Persistence
    // -----------------------------------------------------------------------

    abstract public function save(): void;
}
