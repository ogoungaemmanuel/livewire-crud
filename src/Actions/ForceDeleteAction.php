<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Permanently deletes a soft-deleted record (force-delete).
 *
 * Always requires confirmation and is typically placed inside a
 * "Trashed" filter view alongside RestoreAction.
 *
 * Usage:
 *   ForceDeleteAction::make()
 */
class ForceDeleteAction extends Action
{
    public static function make(string $name = 'force-delete'): static
    {
        $instance = new static($name);
        $instance->color            = 'danger';
        $instance->icon             = 'bi-trash3-fill';
        $instance->label            = 'Permanently Delete';
        $instance->requiresConfirmation = true;
        $instance->modalHeading     = 'Permanently delete?';
        $instance->modalBody        = 'This action CANNOT be undone. The record will be gone forever.';
        return $instance;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label    = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        $idAccess = ltrim($rowVar, '$') . '->id';
        $confirm  = $this->confirmAttr();
        $tooltip  = $this->tooltipAttr();

        return <<<HTML
@if({$rowVar}->trashed())
<button type="button" class="{$btnClass}" wire:click="forceDelete({{ \${$idAccess} }})" {$confirm}{$tooltip}>
    {$iconHtml}{$label}
</button>
@endif
HTML;
    }

    public function toCode(): string
    {
        return 'ForceDeleteAction::make()' . $this->commonChain();
    }
}
