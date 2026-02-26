<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Schema;

use Xslainadmin\LivewireCrud\Fields\Field;
use Xslainadmin\LivewireCrud\Schema\Section;

/**
 * Tab — a single tab pane inside a Tabs layout component.
 *
 * Usage:
 *   Tab::make('General')
 *       ->icon('bi-info-circle')
 *       ->schema([
 *           TextInput::make('name')->required(),
 *           Textarea::make('description'),
 *       ])
 */
class Tab
{
    protected string  $label;
    protected ?string $icon  = null;
    protected ?string $badge = null;

    /** @var array<Field|Section> */
    protected array $fields = [];

    final public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    // __ fluent __

    /** @param array<Field|Section|Tab> $fields */
    public function schema(array $fields): static   { $this->fields = $fields; return $this; }
    public function icon(string $icon): static      { $this->icon   = $icon;   return $this; }
    public function badge(string $badge): static    { $this->badge  = $badge;  return $this; }

    // __ accessors __

    public function getLabel(): string  { return $this->label; }
    public function getId(): string     { return 'tab-' . preg_replace('/\W+/', '-', strtolower($this->label)); }

    /** @return array<Field|Section> */
    public function getFields(): array  { return $this->fields; }

    // __ rendering __

    /**
     * Render the tab trigger <li> element.
     */
    public function renderTrigger(bool $active = false): string
    {
        $id      = $this->getId();
        $label   = htmlspecialchars($this->label);
        $icon    = $this->icon ? "<i class=\"bi {$this->icon} me-1\"></i>" : '';
        $badge   = $this->badge ? " <span class=\"badge text-bg-secondary\">{$this->badge}</span>" : '';
        $active_ = $active ? ' active' : '';
        $sel     = $active ? ' aria-selected="true"' : ' aria-selected="false"';

        return <<<HTML
<li class="nav-item" role="presentation">
    <button class="nav-link{$active_}" id="{$id}-tab" data-bs-toggle="tab"
            data-bs-target="#{$id}" type="button" role="tab"{$sel}>
        {$icon}{$label}{$badge}
    </button>
</li>
HTML;
    }

    /**
     * Render the tab pane <div>.
     */
    public function renderPane(bool $active = false): string
    {
        $id      = $this->getId();
        $active_ = $active ? ' show active' : '';
        $inner   = '';
        foreach ($this->fields as $field) {
            $inner .= method_exists($field, 'render') ? $field->render() . "\n" : '';
        }
        return <<<HTML
<div class="tab-pane fade{$active_}" id="{$id}" role="tabpanel" aria-labelledby="{$id}-tab">
    <div class="row g-3 pt-3">
        {$inner}
    </div>
</div>
HTML;
    }

    // __ code generation __

    public function toCode(int $indent = 12): string
    {
        $pad    = str_repeat(' ', $indent);
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $pad . $field->toCode() . ',';
        }
        $inner = implode("\n", $fields);
        $chain = "Tab::make('" . addslashes($this->label) . "')";
        if ($this->icon) { $chain .= "->icon('{$this->icon}')"; }
        $chain .= "->schema([\n{$inner}\n" . str_repeat(' ', $indent - 4) . '])';
        return $chain;
    }
}
