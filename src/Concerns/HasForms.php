<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Concerns;

/**
 * Livewire trait — centralises form state management for create / edit flows.
 *
 * Provides:
 *  - Typed form-mode tracking (create | edit | view)
 *  - Active record ID tracking
 *  - Validation helpers delegated to the Resource's FormSchema rules
 *  - `fillForm()` / `getFormData()` helpers to hydrate/extract field values
 *
 * Usage:
 *
 *   use Xslainadmin\LivewireCrud\Concerns\HasForms;
 *   use Xslainadmin\LivewireCrud\Concerns\InteractsWithResource;
 *
 *   class Users extends Component
 *   {
 *       use InteractsWithResource, HasForms;
 *
 *       public function mount(): void { $this->initFormMode(); }
 *   }
 */
trait HasForms
{
    // __ State __

    /** 'create' | 'edit' | 'view' | null */
    public ?string $formMode   = null;
    public ?int    $formRecord = null;

    // __ Lifecycle __

    public function initFormMode(): void
    {
        $this->formMode   = null;
        $this->formRecord = null;
    }

    // __ Modal open helpers __

    public function openCreateModal(): void
    {
        $this->formMode   = 'create';
        $this->formRecord = null;
        $this->resetFormFields();
        $this->dispatch('open-modal', id: 'crud-form');
    }

    public function openEditModal(int|string $id): void
    {
        $this->formMode   = 'edit';
        $this->formRecord = (int) $id;
        $this->hydrateFormFrom($id);
        $this->dispatch('open-modal', id: 'crud-form');
    }

    public function openViewModal(int|string $id): void
    {
        $this->formMode   = 'view';
        $this->formRecord = (int) $id;
        $this->hydrateFormFrom($id);
        $this->dispatch('open-modal', id: 'crud-view');
    }

    // __ Form hydration __

    /**
     * Set Livewire public properties from the given record's attributes.
     * Only populates properties that exist on the component and are declared
     * in the Resource's FormSchema.
     */
    protected function hydrateFormFrom(int|string $id): void
    {
        if (!isset(static::$resource)) {
            return;
        }

        $model  = static::$resource::getModelClass();
        $record = $model::findOrFail($id);
        $fields = static::$resource::getForm()->getFields();

        foreach ($fields as $field) {
            $name = $field->getName();
            if (property_exists($this, $name)) {
                $this->{$name} = $record->{$name};
            }
        }
    }

    /**
     * Collect all form-field values into an associative array.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        if (!isset(static::$resource)) {
            return [];
        }

        $data   = [];
        $fields = static::$resource::getForm()->getFields();
        foreach ($fields as $field) {
            $name       = $field->getName();
            $data[$name] = property_exists($this, $name) ? $this->{$name} : null;
        }
        return $data;
    }

    /**
     * Reset all form fields to empty/default values.
     */
    protected function resetFormFields(): void
    {
        if (!isset(static::$resource)) {
            return;
        }

        $fields = static::$resource::getForm()->getFields();
        foreach ($fields as $field) {
            $name = $field->getName();
            if (property_exists($this, $name)) {
                $this->{$name} = '';
            }
        }
    }

    // __ Validation helpers __

    /**
     * Build the Livewire validation rules array from the Resource's
     * FormSchema field rules.
     *
     * @return array<string, string>
     */
    protected function formRules(): array
    {
        if (!isset(static::$resource)) {
            return [];
        }

        $rules  = [];
        foreach (static::$resource::getForm()->getFields() as $field) {
            $fieldRules = $field->getRules();
            if ($fieldRules) {
                $rules[$field->getName()] = implode('|', $fieldRules);
            }
        }
        return $rules;
    }

    /** Whether the form is currently in create mode. */
    public function isCreating(): bool { return $this->formMode === 'create'; }

    /** Whether the form is currently in edit mode. */
    public function isEditing(): bool  { return $this->formMode === 'edit'; }

    /** Whether the form is currently in view (read-only) mode. */
    public function isViewing(): bool  { return $this->formMode === 'view'; }
}
