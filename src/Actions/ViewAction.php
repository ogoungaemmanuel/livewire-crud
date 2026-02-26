<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Opens the show/view modal for a specific row.
 */
class ViewAction extends Action
{
    public static function make(string $name = 'view'): static
    {
        $instance = new static($name);
        $instance->color     = 'info';
        $instance->icon      = 'bi-eye';
        $instance->label     = 'View';
        return $instance;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label    = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        $idAccess = ltrim($rowVar, '$') . '->id';
        $tooltip  = $this->tooltipAttr();
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"openShowModal({{ \${$idAccess} }})\"{$tooltip}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        return 'ViewAction::make()' . $this->commonChain();
    }
}
