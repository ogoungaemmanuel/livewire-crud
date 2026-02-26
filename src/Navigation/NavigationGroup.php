<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Navigation;

/**
 * A collapsible navigation group (section) containing NavigationItems.
 *
 * Usage:
 *   NavigationGroup::make('Administration')
 *       ->icon('bi-shield-lock')
 *       ->items([
 *           NavigationItem::make('Users')->url('/admin/users')->icon('bi-people'),
 *           NavigationItem::make('Roles')->url('/admin/roles')->icon('bi-person-badge'),
 *       ])
 */
class NavigationGroup
{
    protected string $label;
    protected string $icon       = 'bi-folder';
    protected int    $sort       = 0;
    protected bool   $collapsed  = false;

    /** @var NavigationItem[] */
    protected array $items = [];

    final public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    public function icon(string $icon): static       { $this->icon      = $icon;  return $this; }
    public function sort(int $sort): static          { $this->sort      = $sort;  return $this; }
    public function collapsed(): static              { $this->collapsed = true;   return $this; }

    /** @param NavigationItem[] $items */
    public function items(array $items): static      { $this->items = $items; return $this; }

    public function addItem(NavigationItem $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /* ── accessors ───────────────────────────────────────────────────────── */

    public function getLabel(): string     { return $this->label; }
    public function getIcon(): string      { return $this->icon; }
    public function getSort(): int         { return $this->sort; }

    /** @return NavigationItem[] */
    public function getItems(): array
    {
        $items = $this->items;
        usort($items, fn ($a, $b) => $a->getSort() <=> $b->getSort());
        return $items;
    }

    /* ── active state ────────────────────────────────────────────────────── */

    public function hasActiveItem(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isActive()) {
                return true;
            }
        }
        return false;
    }

    /* ── rendering ───────────────────────────────────────────────────────── */

    public function render(): string
    {
        $id       = 'nav-group-' . preg_replace('/\W+/', '-', strtolower($this->label));
        $label    = htmlspecialchars($this->label);
        $icon     = $this->icon;
        $expanded = $this->hasActiveItem() && !$this->collapsed ? 'true' : 'false';
        $show     = $expanded === 'true' ? ' show' : '';

        $inner = '';
        foreach ($this->getItems() as $item) {
            $inner .= '        ' . $item->render() . "\n";
        }

        return <<<HTML
<div class="nav-group mb-1">
    <a class="nav-link d-flex align-items-center gap-1 fw-semibold"
       data-bs-toggle="collapse" href="#{$id}" aria-expanded="{$expanded}">
        <i class="bi {$icon}"></i> {$label}
        <i class="bi bi-chevron-down ms-auto small transition-rotate"></i>
    </a>
    <div class="collapse{$show}" id="{$id}">
        <div class="ps-3 border-start ms-2">
{$inner}        </div>
    </div>
</div>
HTML;
    }
}
