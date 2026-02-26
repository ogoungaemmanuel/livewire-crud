<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource\Page;

use Xslainadmin\LivewireCrud\Concerns\HasForms;
use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;

/**
 * CreateRecord page — dedicated create form page for a Resource.
 *
 * Usage:
 *
 *   class CreateProduct extends CreateRecord
 *   {
 *       protected static string $resource = ProductResource::class;
 *
 *       public function create(): void
 *       {
 *           $this->validate($this->formRules());
 *           Product::create($this->getFormData());
 *           $this->redirect(ListProducts::getUrl());
 *       }
 *   }
 */
abstract class CreateRecord extends Page
{
    use InteractsWithResource;
    use HasForms;

    // -----------------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------------

    public function mount(): void
    {
        abort_unless(static::$resource::canCreate(), 403);
        $this->initFormMode();
        $this->formMode = 'create';
    }

    // -----------------------------------------------------------------------
    // Breadcrumbs
    // -----------------------------------------------------------------------

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => static::$resource::getPluralModelLabel(), 'url' => static::$resource::getUrl('index')],
            ['label' => 'Create ' . static::$resource::getModelLabel(), 'url' => null],
        ];
    }

    // -----------------------------------------------------------------------
    // Page key
    // -----------------------------------------------------------------------

    protected static function pageKey(): string
    {
        return 'create';
    }

    // -----------------------------------------------------------------------
    // Persistence
    // -----------------------------------------------------------------------

    /**
     * Override in the concrete class to handle form submission.
     */
    abstract public function create(): void;
}
