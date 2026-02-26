<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Navigation;

/**
 * Represents a single item in the sidebar navigation.
 *
 * Usage:
 *   NavigationItem::make('Users')
 *       ->url('/admin/users')
 *       ->icon('bi-people')
 *       ->group('Administration')
 *       ->badge('42', 'danger')
 *       ->sort(10)
 */
class NavigationItem
{
    protected string  $label;
    protected string  $url         = '#';
    protected string  $icon        = 'bi-circle';
    protected ?string $group       = null;
    protected int     $sort        = 0;
    protected ?string $badge       = null;
    protected string  $badgeColor  = 'danger';
    protected bool    $openInNewTab = false;
    protected ?string $activePattern = null;

    final public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    public function url(string $url): static              { $this->url          = $url;   return $this; }
    public function icon(string $icon): static            { $this->icon         = $icon;  return $this; }
    public function group(?string $group): static         { $this->group        = $group; return $this; }
    public function sort(int $sort): static               { $this->sort         = $sort;  return $this; }
    public function openInNewTab(): static                { $this->openInNewTab = true;   return $this; }
    public function activePattern(string $p): static      { $this->activePattern = $p;   return $this; }
    public function badge(?string $badge, string $color = 'danger'): static
    {
        $this->badge      = $badge;
        $this->badgeColor = $color;
        return $this;
    }

    /* ── accessors ───────────────────────────────────────────────────────── */

    public function getLabel(): string   { return $this->label; }
    public function getUrl(): string     { return $this->url; }
    public function getIcon(): string    { return $this->icon; }
    public function getGroup(): ?string  { return $this->group; }
    public function getSort(): int       { return $this->sort; }
    public function getBadge(): ?string  { return $this->badge; }
    public function getBadgeColor(): string { return $this->badgeColor; }

    /* ── active state ────────────────────────────────────────────────────── */

    public function isActive(): bool
    {
        $current = request()->path();
        $pattern = $this->activePattern ?? ltrim($this->url, '/') . '*';
        return fnmatch($pattern, $current);
    }

    /* ── rendering ───────────────────────────────────────────────────────── */

    public function render(): string
    {
        $active  = $this->isActive() ? ' active' : '';
        $target  = $this->openInNewTab ? ' target="_blank" rel="noopener"' : '';
        $label   = htmlspecialchars($this->label);
        $icon    = $this->icon;
        $url     = htmlspecialchars($this->url);
        $badge   = $this->badge
            ? "<span class=\"badge text-bg-{$this->badgeColor} ms-auto\">{$this->badge}</span>"
            : '';

        return <<<HTML
<a href="{$url}" class="nav-link{$active}" {$target}>
    <i class="bi {$icon} me-1"></i> {$label} {$badge}
</a>
HTML;
    }
}
