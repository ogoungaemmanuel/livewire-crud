<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Opens the create/add modal.
 *
 * Usage:
 *   CreateAction::make()
 *   CreateAction::make()->label('Add User')->icon('bi-person-plus')
 */
class CreateAction extends Action
{
    protected string  $color    = 'primary';
    protected ?string $icon     = 'bi-plus-lg';
    protected ?string $wireClick = 'openCreateModal';

    public static function make(string $name = 'create'): static
    {
        $instance = new static($name);
        $instance->color     = 'primary';
        $instance->icon      = 'bi-plus-lg';
        $instance->wireClick = 'openCreateModal';
        return $instance;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label   = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        return "<button type=\"button\" class=\"{$btnClass}\" wire:click=\"openCreateModal\"{$this->tooltipAttr()}>{$iconHtml}{$label}</button>";
    }

    public function toCode(): string
    {
        return 'CreateAction::make()' . $this->commonChain();
    }
}
