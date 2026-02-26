<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Schema;

use Xslainadmin\LivewireCrud\Fields\Field;

/**
 * Section — a named, optionally collapsible group of form fields.
 *
 * Section layout component. Sections render as
 * Bootstrap 5 cards with a header and body that can be toggled via Alpine.js.
 *
 * Usage (inside FormSchema::schema()):
 *   Section::make('Billing Details')
 *       ->icon('bi-credit-card')
 *       ->collapsible()
 *       ->schema([
 *           TextInput::make('billing_name')->required()->columnSpan('1/2'),
 *           TextInput::make('billing_address')->required()->columnSpan('1/2'),
 *       ])
 *       ->columns(2)
 */
class Section
{
    protected string  $heading;
    protected ?string $description = null;
    protected ?string $icon        = null;
    protected bool    $collapsible = false;
    protected bool    $collapsed   = false;
    protected int     $columns     = 2;
    protected bool    $aside       = false;   // two-column: label left, fields right

    /** @var array<Field|Section> */
    protected array $fields = [];

    final public function __construct(string $heading)
    {
        $this->heading = $heading;
    }

    public static function make(string $heading): static
    {
        return new static($heading);
    }

    // __ fluent __

    /** @param array<Field|Section> $fields */
    public function schema(array $fields): static       { $this->fields      = $fields; return $this; }
    public function columns(int $columns): static       { $this->columns     = $columns; return $this; }
    public function description(string $desc): static   { $this->description = $desc;   return $this; }
    public function icon(string $icon): static          { $this->icon        = $icon;   return $this; }
    public function collapsible(bool $val = true): static { $this->collapsible = $val;  return $this; }
    public function collapsed(bool $val = true): static { $this->collapsible = true; $this->collapsed = $val; return $this; }
    public function aside(bool $val = true): static     { $this->aside       = $val;   return $this; }

    // __ accessors __

    public function getHeading(): string   { return $this->heading; }

    /** @return array<Field|Section> */
    public function getFields(): array     { return $this->fields; }

    // __ rendering __

    public function render(): string
    {
        $id       = 'section-' . preg_replace('/\W+/', '-', strtolower($this->heading));
        $heading  = htmlspecialchars($this->heading);
        $iconHtml = $this->icon ? "<i class=\"bi {$this->icon} me-2\"></i>" : '';
        $descHtml = $this->description
            ? "<p class=\"text-muted small mb-3\">{$this->description}</p>"
            : '';

        // Build field grid
        $inner = '';
        foreach ($this->fields as $field) {
            $inner .= method_exists($field, 'render') ? $field->render() . "\n" : '';
        }

        if ($this->collapsible) {
            $show    = $this->collapsed ? '' : ' show';
            $expanded = $this->collapsed ? 'false' : 'true';
            $toggleBtn = <<<HTML
<button type="button" class="btn btn-link btn-sm p-0 text-inherit"
        data-bs-toggle="collapse" data-bs-target="#{$id}" aria-expanded="{$expanded}">
    <i class="bi bi-chevron-down small transition-rotate"></i>
</button>
HTML;
            return <<<HTML
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{$iconHtml}{$heading}</h6>
        {$toggleBtn}
    </div>
    <div class="collapse{$show}" id="{$id}">
        <div class="card-body">
            {$descHtml}
            <div class="row g-3">{$inner}</div>
        </div>
    </div>
</div>
HTML;
        }

        return <<<HTML
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">{$iconHtml}{$heading}</h6>
    </div>
    <div class="card-body">
        {$descHtml}
        <div class="row g-3">{$inner}</div>
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
        $inner  = implode("\n", $fields);
        $chain  = "Section::make('" . addslashes($this->heading) . "')";
        if ($this->icon)        { $chain .= "->icon('{$this->icon}')"; }
        if ($this->description) { $chain .= "->description('" . addslashes($this->description) . "')"; }
        if ($this->collapsible) { $chain .= '->collapsible()'; }
        if ($this->collapsed)   { $chain .= '->collapsed()'; }
        if ($this->columns !== 2) { $chain .= "->columns({$this->columns})"; }
        $chain .= "->schema([\n{$inner}\n" . str_repeat(' ', $indent - 4) . '])';
        return $chain;
    }
}
