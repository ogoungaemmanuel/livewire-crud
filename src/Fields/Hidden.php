<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Hidden field — binds a value to a Livewire property without rendering a
 * visible input. Useful for passing record IDs or computed values.
 *
 * Usage:
 *   Hidden::make('user_id')->default(auth()->id())
 *   Hidden::make('created_by')
 */
class Hidden extends Field
{
    public function render(): string
    {
        $name    = $this->name;
        $default = $this->default !== null ? " value=\"{$this->default}\"" : '';

        return "<input type=\"hidden\" wire:model=\"{$name}\" id=\"{$this->id}\"{$default}>";
    }

    public function toCode(): string
    {
        $chain = "Hidden::make('{$this->name}')";
        if ($this->default !== null) {
            $chain .= "->default('" . addslashes((string) $this->default) . "')";
        }
        return $chain;
    }
}
