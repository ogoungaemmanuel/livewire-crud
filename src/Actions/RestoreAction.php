<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Restores a soft-deleted record.
 *
 * Only rendered when the model uses SoftDeletes and the record has
 * a non-null deleted_at value (enforced by the visible() condition).
 *
 * Usage:
 *   RestoreAction::make()
 *   RestoreAction::make()->requiresConfirmation()
 */
class RestoreAction extends Action
{
    public static function make(string $name = 'restore'): static
    {
        $instance = new static($name);
        $instance->color     = 'success';
        $instance->icon      = 'bi-arrow-counterclockwise';
        $instance->label     = 'Restore';
        $instance->requiresConfirmation = true;
        $instance->modalHeading = 'Restore record?';
        $instance->modalBody    = 'This will un-delete the record and make it active again.';
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

        // Only render when deleted_at is set
        return <<<HTML
@if({$rowVar}->trashed())
<button type="button" class="{$btnClass}" wire:click="restore({{ \${$idAccess} }})" {$confirm}{$tooltip}>
    {$iconHtml}{$label}
</button>
@endif
HTML;
    }

    public function toCode(): string
    {
        return 'RestoreAction::make()' . $this->commonChain();
    }
}
