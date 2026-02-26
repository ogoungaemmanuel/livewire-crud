<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Triggers an import modal (Excel / CSV).
 */
class ImportAction extends Action
{
    public static function make(string $name = 'import'): static
    {
        $instance = new static($name);
        $instance->color  = 'secondary';
        $instance->icon   = 'bi-file-earmark-arrow-up';
        $instance->label  = 'Import';
        return $instance;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label    = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        $tooltip  = $this->tooltipAttr();
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"openImportModal()\"{$tooltip}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        return 'ImportAction::make()' . $this->commonChain();
    }
}
