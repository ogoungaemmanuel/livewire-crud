<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * Bulk action — operates on all selected rows.
 *
 * Usage:
 *   BulkAction::make('delete')->requiresConfirmation()->icon('bi-trash')->color('danger')
 *   BulkAction::make('export')->label('Export Selected')->icon('bi-download')
 */
class BulkAction extends Action
{
    protected ?string $wireMethod = null;

    public static function make(string $name = 'bulk'): static
    {
        $instance = new static($name);
        $instance->color = 'secondary';
        return $instance;
    }

    /**
     * Set the Livewire method to call with the selected IDs.
     * Defaults to "bulk{Name}" e.g. "bulkDelete".
     */
    public function action(string $method): static
    {
        $this->wireMethod = $method;
        return $this;
    }

    public function renderButton(string $rowVar = '$row'): string
    {
        $label    = $this->getLabel();
        $iconHtml = $this->iconHtml();
        $btnClass = $this->btnClass();
        $method   = $this->wireMethod ?? 'bulk' . ucfirst(str_replace(['-', '_'], '', $this->name));
        $confirm  = $this->confirmAttr();
        $tooltip  = $this->tooltipAttr();

        return <<<HTML
<button type="button" class="{$btnClass}" wire:click="{$method}"{$confirm}{$tooltip}
    x-bind:disabled="selectedIds.length === 0">
    {$iconHtml}{$label} (<span x-text="selectedIds.length"></span>)
</button>
HTML;
    }

    public function toCode(): string
    {
        $chain = "BulkAction::make('{$this->name}')";
        if ($this->wireMethod) {
            $chain .= "->action('{$this->wireMethod}')";
        }
        return $chain . $this->commonChain();
    }
}
