<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Triggers an export (Excel / CSV / PDF).
 *
 * Usage:
 *   ExportAction::make()
 *   ExportAction::make()->format('pdf')->label('Export PDF')
 */
class ExportAction extends Action
{
    protected string $format = 'xlsx';   // xlsx | csv | pdf

    public static function make(string $name = 'export'): static
    {
        $instance = new static($name);
        $instance->color  = 'success';
        $instance->icon   = 'bi-file-earmark-arrow-down';
        $instance->label  = 'Export';
        return $instance;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label    = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        $tooltip  = $this->tooltipAttr();
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"export('{$this->format}')\"{$tooltip}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        $chain = 'ExportAction::make()';
        if ($this->format !== 'xlsx') { $chain .= "->format('{$this->format}')"; }
        return $chain . $this->commonChain();
    }
}
