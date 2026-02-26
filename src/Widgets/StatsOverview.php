<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Widgets;

/**
 * A single stat card shown inside a StatsOverview widget.
 *
 * Usage:
 *   Stat::make('Total Users', User::count())
 *       ->description('Active accounts')
 *       ->icon('bi-people')
 *       ->color('primary')
 *       ->change('+12%')
 *       ->changePositive()
 */
class Stat
{
    protected string $label;
    protected string $value;
    protected ?string $description   = null;
    protected string  $icon          = 'bi-bar-chart';
    protected string  $color         = 'primary';
    protected ?string $change        = null;
    protected ?bool   $changePositive = null;
    protected ?string $url           = null;

    final public function __construct(string $label, $value)
    {
        $this->label = $label;
        $this->value = (string) $value;
    }

    public static function make(string $label, $value): static
    {
        return new static($label, $value);
    }

    public function description(string $desc): static          { $this->description    = $desc;  return $this; }
    public function icon(string $icon): static                 { $this->icon           = $icon;  return $this; }
    public function color(string $color): static               { $this->color          = $color; return $this; }
    public function change(string $change): static             { $this->change         = $change; return $this; }
    public function changePositive(): static                   { $this->changePositive = true;  return $this; }
    public function changeNegative(): static                   { $this->changePositive = false; return $this; }
    public function url(string $url): static                   { $this->url            = $url;   return $this; }

    public function render(): string
    {
        $label = htmlspecialchars($this->label);
        $value = htmlspecialchars($this->value);
        $icon  = $this->icon;
        $color = $this->color;
        $desc  = $this->description ? "<p class=\"card-text text-muted small mb-0\">{$this->description}</p>" : '';

        $changeBadge = '';
        if ($this->change !== null) {
            $badgeColor = $this->changePositive === true ? 'success' : ($this->changePositive === false ? 'danger' : 'secondary');
            $arrowIcon  = $this->changePositive === true ? 'bi-arrow-up-short' : ($this->changePositive === false ? 'bi-arrow-down-short' : '');
            $changeBadge = "<span class=\"badge text-bg-{$badgeColor} ms-2\"><i class=\"bi {$arrowIcon}\"></i>{$this->change}</span>";
        }

        $wrapStart = $this->url ? "<a href=\"{$this->url}\" class=\"text-decoration-none\">" : '';
        $wrapEnd   = $this->url ? '</a>' : '';

        return <<<HTML
<div class="col">
    {$wrapStart}
    <div class="card border-{$color} h-100">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-{$color} bg-opacity-10 p-3">
                    <i class="bi {$icon} text-{$color} fs-4"></i>
                </div>
                <div>
                    <p class="card-text text-muted small text-uppercase fw-semibold mb-0">{$label}</p>
                    <h4 class="card-title mb-0">{$value}{$changeBadge}</h4>
                    {$desc}
                </div>
            </div>
        </div>
    </div>
    {$wrapEnd}
</div>
HTML;
    }
}

/**
 * Abstract Livewire class for a stats overview dashboard widget.
 *
 * Extend this in your module and implement stats():
 *
 *   class UserStatsOverview extends StatsOverview
 *   {
 *       protected function stats(): array
 *       {
 *           return [
 *               Stat::make('Total Users', User::count())->icon('bi-people')->color('primary'),
 *               Stat::make('Active',      User::where('is_active', true)->count())->color('success'),
 *               Stat::make('Admins',      User::where('role', 'admin')->count())->color('warning'),
 *           ];
 *       }
 *   }
 */
abstract class StatsOverview extends \Livewire\Component
{
    public int $refreshInterval = 0;  // seconds; 0 = disabled

    /** @return Stat[] */
    abstract protected function stats(): array;

    public function render(): string
    {
        $stats   = $this->stats();
        $columns = count($stats);
        $colCls  = match (true) {
            $columns <= 2 => 'row-cols-1 row-cols-md-2',
            $columns === 3 => 'row-cols-1 row-cols-md-3',
            default        => 'row-cols-2 row-cols-md-4',
        };

        $cardsHtml = '';
        foreach ($stats as $stat) {
            $cardsHtml .= $stat->render() . "\n";
        }

        $polling = $this->refreshInterval > 0
            ? " wire:poll.{$this->refreshInterval}s"
            : '';

        return <<<HTML
<div class="row {$colCls} g-3 mb-4"{$polling}>
{$cardsHtml}</div>
HTML;
    }
}
