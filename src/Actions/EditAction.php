<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Opens the edit modal for a specific row.
 */
class EditAction extends Action
{
    public static function make(string $name = 'edit'): static
    {
        $instance = new static($name);
        $instance->color     = 'warning';
        $instance->icon      = 'bi-pencil';
        $instance->label     = 'Edit';
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
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"openEditModal({{ \${$idAccess} }})\"{$confirm}{$tooltip}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        return 'EditAction::make()' . $this->commonChain();
    }
}
