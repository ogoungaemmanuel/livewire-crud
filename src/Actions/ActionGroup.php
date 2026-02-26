<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

/**
 * ActionGroup — wraps multiple actions inside a Bootstrap 5 dropdown button.
 *
 * ActionGroup, this renders a single "⋮" or
 * labelled dropdown that contains all child actions as menu items.
 *
 * Usage:
 *   ActionGroup::make([
 *       ViewAction::make(),
 *       EditAction::make(),
 *       DeleteAction::make(),
 *   ])->tooltip('More actions')
 *
 *   // Custom trigger button
 *   ActionGroup::make([...])
 *       ->icon('bi-three-dots-vertical')
 *       ->label('Actions')
 *       ->color('secondary')
 */
class ActionGroup extends Action
{
    /** @var Action[] */
    protected array $actions    = [];
    protected string $dropdownAlign = 'end';  // 'start' | 'end'
    protected bool   $asIcons   = false;

    final public function __construct(string $name = 'action-group')
    {
        parent::__construct($name);
        $this->icon    = 'bi-three-dots-vertical';
        $this->color   = 'secondary';
        $this->outlined = true;
        $this->label   = '';
    }

    /**
     * Create a new ActionGroup.
     * Chain ->actions([...]) to add child actions.
     *
     *   ActionGroup::make()
     *       ->actions([ViewAction::make(), EditAction::make()])
     *       ->tooltip('More actions')
     */
    public static function make(string $name = 'action-group'): static
    {
        return new static($name);
    }

    // __ fluent __

    public function dropdownAlignStart(): static   { $this->dropdownAlign = 'start'; return $this; }
    public function dropdownAlignEnd(): static     { $this->dropdownAlign = 'end';   return $this; }

    /**
     * Render icon-only mode — each action becomes icon-only inside a
     * .btn-group rather than a dropdown.
     */
    public function asIcons(): static             { $this->asIcons = true; return $this; }

    /**
     * @param Action[] $actions
     */
    public function actions(array $actions): static
    {
        $this->actions = array_merge($this->actions, $actions);
        return $this;
    }

    // __ rendering __

    public function renderButton(string $rowVar = '$row'): string
    {
        if ($this->asIcons) {
            return $this->renderIconGroup($rowVar);
        }

        $btnClass = $this->btnClass();
        $iconHtml = $this->icon ? "<i class=\"bi {$this->icon}\"></i>" : '';
        $labelHtml = $this->label ? " {$this->label}" : '';
        $dropAlign = $this->dropdownAlign === 'start' ? '' : ' dropdown-menu-end';
        $tooltip   = $this->tooltipAttr();

        $items = '';
        foreach ($this->actions as $action) {
            if ($action->isHidden()) {
                continue;
            }
            $items .= $this->renderDropdownItem($action, $rowVar);
        }

        return <<<HTML
<div class="dropdown">
    <button type="button" class="{$btnClass} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"{$tooltip}>
        {$iconHtml}{$labelHtml}
    </button>
    <ul class="dropdown-menu{$dropAlign}">
        {$items}
    </ul>
</div>
HTML;
    }

    protected function renderDropdownItem(Action $action, string $rowVar): string
    {
        // Extract a simplified wire:click from the child's renderButton output
        $html = $action->renderButton($rowVar);

        // Wrap the inner button as a dropdown-item <li>
        $label   = htmlspecialchars($action->getName());
        $icon    = '';

        // Re-use the action's rendered output wrapped as a dropdown-item
        // We inject dropdown-item class by replacing button class signature
        $dropHtml = preg_replace('/class="btn[^"]*"/', 'class="dropdown-item"', $html, 1);

        return "<li>{$dropHtml}</li>\n";
    }

    protected function renderIconGroup(string $rowVar): string
    {
        $items = '';
        foreach ($this->actions as $action) {
            if (!$action->isHidden()) {
                $items .= $action->renderButton($rowVar) . "\n";
            }
        }
        return "<div class=\"btn-group btn-group-sm\" role=\"group\">{$items}</div>";
    }

    public function toCode(): string
    {
        $inner = implode(",\n        ", array_map(
            static fn (Action $a): string => $a->toCode(),
            $this->actions
        ));
        return "ActionGroup::make()\n    ->actions([\n        {$inner}\n    ])" . $this->commonChain();
    }
}
