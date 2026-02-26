<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

use Xslainadmin\LivewireCrud\Contracts\FieldContract;

/**
 * Base form-field class  fluent form field builder.
 *
 * Fields describe how an attribute is edited inside the generated form
 * and power the auto-generated Resource class.
 */
abstract class Field implements FieldContract
{
    protected string  $name;
    protected ?string $label        = null;
    protected ?string $hint         = null;
    protected ?string $placeholder  = null;
    protected ?string $helperText   = null;
    protected bool    $required     = false;
    protected bool    $disabled     = false;
    protected bool    $readOnly     = false;
    protected bool    $autofocus    = false;
    protected mixed   $default      = null;
    protected ?string $columnSpan   = null;  // 'full' | '1/2' | '1/3' | '2/3'
    protected ?string $id           = null;
    protected ?string $extraClass   = null;

    /** @var array<string, string|int> */
    protected array $rules          = [];

    // ── Enterprise additions ──────────────────────────────────────────────
    /** When true, uses wire:model.live instead of wire:model */
    protected bool $isLive          = false;
    /** Livewire method name to call when the field value changes */
    protected ?string $updatedMethod = null;
    /** Callable or bool that controls whether this field is rendered */
    protected bool|\Closure $visible = true;

    protected function __construct(string $name)
    {
        $this->name        = $name;
        $this->label       = str_replace('_', ' ', ucwords($name, '_'));
        $this->placeholder = str_replace('_', ' ', ucwords($name, '_'));
        $this->id          = $name;
    }

    // ── Factory ──────────────────────────────────────────────────────────

    public static function make(string $name): static
    {
        return new static($name);
    }

    // ── Fluent setters ────────────────────────────────────────────────────

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function hint(string $hint): static
    {
        $this->hint = $hint;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function helperText(string $text): static
    {
        $this->helperText = $text;
        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;
        if ($required && !isset($this->rules['required'])) {
            $this->rules['required'] = 'required';
        }
        return $this;
    }

    public function nullable(): static
    {
        $this->required = false;
        unset($this->rules['required']);
        $this->rules['nullable'] = 'nullable';
        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function readOnly(bool $readOnly = true): static
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    public function autofocus(): static
    {
        $this->autofocus = true;
        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function columnSpan(string $span): static
    {
        $this->columnSpan = $span;
        return $this;
    }

    public function columnSpanFull(): static
    {
        $this->columnSpan = 'full';
        return $this;
    }

    public function id(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function extraClass(string $class): static
    {
        $this->extraClass = $class;
        return $this;
    }

    public function rule(string $rule): static
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * Make the field reactive — uses wire:model.live so updates fire on every
     * keystroke / change without requiring a form submit.
     */
    public function reactive(): static
    {
        $this->isLive = true;
        return $this;
    }

    /** Alias for reactive() — mirrors FilamentPHP's ->live() API. */
    public function live(): static
    {
        return $this->reactive();
    }

    /**
     * Register a Livewire method to call when this field's value changes.
     * Implies reactive() (wire:model.live).
     */
    public function afterStateUpdated(string $method): static
    {
        $this->updatedMethod = $method;
        $this->isLive        = true;
        return $this;
    }

    /**
     * Conditionally show this field.
     * Accepts a bool or a no-argument closure returning bool.
     */
    public function when(bool|\Closure $condition): static
    {
        $this->visible = $condition;
        return $this;
    }

    /** Determine if the field should be rendered. */
    public function isVisible(): bool
    {
        if ($this->visible instanceof \Closure) {
            return (bool) ($this->visible)();
        }
        return $this->visible;
    }

    // ── Getters ──────────────────────────────────────────────────────────

    public function getName(): string  { return $this->name; }
    public function getLabel(): string { return $this->label ?? $this->name; }
    public function isRequired(): bool { return $this->required; }

    /** @return array<string, string|int> */
    public function getRules(): array  { return $this->rules; }

    // ── Rendering ────────────────────────────────────────────────────────

    /**
     * Render the Bootstrap 5 form group HTML.
     */
    abstract public function render(): string;

    /**
     * Emit the PHP fluent builder call for the generated Resource class.
     */
    abstract public function toCode(): string;

    /** Build the error / helper text HTML block. */
    protected function errorBlock(): string
    {
        $name = $this->name;
        return "@error('{$name}')<div class=\"invalid-feedback\">{{ \$message }}</div>@enderror"
            . ($this->helperText ? "<div class=\"form-text\">{$this->helperText}</div>" : '');
    }

    /** Build the column-span wrapper class. */
    protected function spanClass(): string
    {
        return match ($this->columnSpan) {
            'full'  => 'col-12',
            '1/2'   => 'col-md-6',
            '1/3'   => 'col-md-4',
            '2/3'   => 'col-md-8',
            default => 'col-12',
        };
    }

    /** Build the common attributes for an input element. */
    protected function commonAttrs(string $type = 'text'): string
    {
        $wireModel = $this->isLive ? 'wire:model.live' : 'wire:model';
        $attrs  = "{$wireModel}=\"{$this->name}\"";
        if ($this->updatedMethod) {
            $attrs .= " wire:change=\"{$this->updatedMethod}\"";
        }
        $attrs .= " type=\"{$type}\"";
        $attrs .= " id=\"{$this->id}\"";
        $attrs .= " class=\"form-control" . ($this->extraClass ? ' ' . $this->extraClass : '') . " @error('{$this->name}') is-invalid @enderror\"";
        if ($this->placeholder) {
            $attrs .= " placeholder=\"" . htmlspecialchars($this->placeholder) . "\"";
        }
        if ($this->required)  { $attrs .= ' required'; }
        if ($this->disabled)  { $attrs .= ' disabled'; }
        if ($this->readOnly)  { $attrs .= ' readonly'; }
        if ($this->autofocus) { $attrs .= ' autofocus'; }
        return $attrs;
    }

    /** Build common fluent chain suffix for toCode(). */
    protected function commonChain(): string
    {
        $chain = '';
        $autoLabel = str_replace('_', ' ', ucwords($this->name, '_'));
        if ($this->label && $this->label !== $autoLabel) {
            $chain .= "->label('" . addslashes($this->label) . "')";
        }
        if ($this->required)   { $chain .= '->required()'; }
        if ($this->disabled)   { $chain .= '->disabled()'; }
        if ($this->readOnly)   { $chain .= '->readOnly()'; }
        if ($this->placeholder && $this->placeholder !== $this->label) {
            $chain .= "->placeholder('" . addslashes($this->placeholder) . "')";
        }
        if ($this->hint)       { $chain .= "->hint('" . addslashes($this->hint) . "')"; }
        if ($this->helperText) { $chain .= "->helperText('" . addslashes($this->helperText) . "')"; }
        if ($this->columnSpan) { $chain .= "->columnSpan('{$this->columnSpan}')"; }
        return $chain;
    }
}
