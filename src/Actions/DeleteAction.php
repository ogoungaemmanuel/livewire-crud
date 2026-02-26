<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Opens the delete confirmation modal for a specific row.
 */
class DeleteAction extends Action
{
    public static function make(string $name = 'delete'): static
    {
        $instance = new static($name);
        $instance->color     = 'danger';
        $instance->icon      = 'bi-trash';
        $instance->label     = 'Delete';
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
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"openDeleteModal({{ \${$idAccess} }})\"{$confirm}{$tooltip}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        return 'DeleteAction::make()' . $this->commonChain();
    }
}
