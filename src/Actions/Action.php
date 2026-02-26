<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Actions;

use Xslainadmin\LivewireCrud\Contracts\ActionContract;

/**
 * Abstract base for all CRUD actions.
 *
 * Inspired by FilamentPHP's Action API — provides a fluent builder that can
 * generate both:
 *  - Blade HTML for the rendered action button
 *  - PHP code for the generated Resource stub
 */
abstract class Action implements ActionContract
{
    protected string  $name;
    protected ?string $label         = null;
    protected string  $color         = 'primary';
    protected ?string $icon          = null;
    protected bool    $iconBefore    = true;
    protected bool    $requiresConfirmation = false;
    protected ?string $modalHeading  = null;
    protected ?string $modalBody     = null;
    protected ?string $url           = null;
    protected bool    $openUrlInNewTab = false;
    protected bool    $hidden        = false;
    protected bool    $outlined      = false;
    protected string  $size          = 'sm';     // sm | md | lg
    protected ?string $tooltip       = null;
    protected ?string $wireClick     = null;
    protected array   $extra         = [];

    // ── Enterprise additions ──────────────────────────────────────────────
    /** @var callable|null  Called with ($record, auth()->user()): bool */
    protected $authorizeUsing     = null;
    /** @var callable|bool  Controls runtime visibility */
    protected $visibleUsing       = true;
    /** @var string|callable|null  Badge value shown on button */
    protected $badge              = null;
    /** @var bool|callable  Whether the action button is disabled */
    protected $disabledUsing      = false;
    /** @var array<string, string>  Extra HTML attributes on the button */
    protected array $extraAttributes = [];

    final public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    public function label(string $label): static      { $this->label       = $label;  return $this; }
    public function color(string $color): static      { $this->color       = $color;  return $this; }
    public function outlined(): static                { $this->outlined    = true;    return $this; }
    public function icon(string $icon): static        { $this->icon        = $icon;   return $this; }
    public function iconAfterLabel(): static          { $this->iconBefore  = false;   return $this; }
    public function size(string $size): static        { $this->size        = $size;   return $this; }
    public function tooltip(string $tip): static      { $this->tooltip     = $tip;    return $this; }
    public function hidden(bool $val = true): static  { $this->hidden      = $val;    return $this; }
    public function url(string $url, bool $newTab = false): static
    {
        $this->url              = $url;
        $this->openUrlInNewTab  = $newTab;
        return $this;
    }
    public function wireClick(string $method): static { $this->wireClick   = $method; return $this; }

    /** Authorisation callback — receives ($record, auth()->user()) and must return bool. */
    public function authorize(callable $callback): static
    {
        $this->authorizeUsing = $callback;
        return $this;
    }

    /** Control runtime visibility via a boolean or a closure returning bool. */
    public function visible(bool|callable $visible): static
    {
        $this->visibleUsing = $visible;
        return $this;
    }

    /** Show a badge on the button (e.g. count). Accepts a string or a callable($record). */
    public function badge(string|callable $badge): static
    {
        $this->badge = $badge;
        return $this;
    }

    /** Disable the button. Accepts a bool or a callable($record). */
    public function disabled(bool|callable $disabled = true): static
    {
        $this->disabledUsing = $disabled;
        return $this;
    }

    /** Merge arbitrary HTML attributes onto the rendered button element. */
    public function extraAttributes(array $attrs): static
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $attrs);
        return $this;
    }

    /** Evaluate whether the action is currently visible (no record context). */
    public function isVisible(): bool
    {
        if (is_callable($this->visibleUsing)) {
            return (bool) ($this->visibleUsing)();
        }
        return (bool) $this->visibleUsing;
    }

    /** Evaluate whether the action is currently disabled (no record context). */
    public function isDisabled(): bool
    {
        if (is_callable($this->disabledUsing)) {
            return (bool) ($this->disabledUsing)();
        }
        return (bool) $this->disabledUsing;
    }

    /** Build extra-attributes HTML fragment. */
    protected function extraAttrsHtml(): string
    {
        $html = '';
        foreach ($this->extraAttributes as $key => $val) {
            $html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars((string) $val) . '"';
        }
        if ($this->isDisabled()) {
            $html .= ' disabled aria-disabled="true"';
        }
        return $html;
    }
    public function requiresConfirmation(bool $val = true): static
    {
        $this->requiresConfirmation = $val;
        return $this;
    }
    public function modalHeading(string $heading): static { $this->modalHeading = $heading; return $this; }
    public function modalBody(string $body): static       { $this->modalBody    = $body;    return $this; }

    /* ── accessors ───────────────────────────────────────────────────────── */

    public function getName(): string  { return $this->name; }
    public function isHidden(): bool   { return $this->hidden; }

    protected function getLabel(): string
    {
        return $this->label ?? ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /* ── rendering helpers ───────────────────────────────────────────────── */

    protected function btnClass(): string
    {
        $variant = $this->outlined ? "btn-outline-{$this->color}" : "btn-{$this->color}";
        return "btn {$variant} btn-{$this->size}";
    }

    protected function iconHtml(): string
    {
        return $this->icon ? "<i class=\"bi {$this->icon}\"></i> " : '';
    }

    protected function clickAttr(): string
    {
        if ($this->url) {
            $target = $this->openUrlInNewTab ? ' target="_blank"' : '';
            return "href=\"{$this->url}\"{$target}";
        }
        if ($this->wireClick) {
            return "wire:click=\"{$this->wireClick}\"";
        }
        return '';
    }

    protected function confirmAttr(): string
    {
        if (!$this->requiresConfirmation) {
            return '';
        }
        $heading = $this->modalHeading ?? 'Are you sure?';
        $body    = $this->modalBody    ?? 'This action cannot be undone.';
        return " wire:confirm=\"{$heading}\\n{$body}\"";
    }

    protected function tooltipAttr(): string
    {
        return $this->tooltip ? " title=\"{$this->tooltip}\" data-bs-toggle=\"tooltip\"" : '';
    }

    /* ── abstract interface ──────────────────────────────────────────────── */

    /**
     * Render a button element for use in table rows or form toolbars.
     *
     * @param  string $rowVar  The Blade variable representing the current row, e.g. '$row'
     */
    abstract public function renderButton(string $rowVar = '$row'): string;

    /**
     * Emit the PHP fluent-builder code for the generated Resource stub.
     */
    abstract public function toCode(): string;

    /* ── shared chain builder ────────────────────────────────────────────── */

    protected function commonChain(): string
    {
        $chain = '';
        if ($this->label !== null)           { $chain .= "->label('{$this->label}')"; }
        if ($this->color !== 'primary')      { $chain .= "->color('{$this->color}')"; }
        if ($this->icon  !== null)           { $chain .= "->icon('{$this->icon}')"; }
        if ($this->outlined)                 { $chain .= '->outlined()'; }
        if ($this->requiresConfirmation)     { $chain .= '->requiresConfirmation()'; }
        if ($this->modalHeading !== null)    { $chain .= "->modalHeading('{$this->modalHeading}')"; }
        if ($this->hidden)                   { $chain .= '->hidden()'; }
        return $chain;
    }
}
