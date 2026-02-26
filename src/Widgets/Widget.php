<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Widgets;

/**
 * Abstract base class for all dashboard / sidebar widgets.
 *
 * Widget base. Widgets are Livewire components
 * that can be composed on dashboard pages. This base class provides:
 *  - A heading / description
 *  - Optional live-refresh polling
 *  - A column-span API (for Bootstrap grid placement)
 *  - A visibility callback
 *
 * Concrete classes extend either ChartWidget, StatsOverview, TableWidget,
 * or this Widget base directly for bespoke content.
 *
 * Usage:
 *
 *   class RecentActivityWidget extends Widget
 *   {
 *       protected string $heading = 'Recent Activity';
 *       protected int    $columnSpan = 2;
 *
 *       public function render(): \Illuminate\View\View
 *       {
 *           return view('accounting::widgets.recent-activity');
 *       }
 *   }
 */
abstract class Widget extends \Livewire\Component
{
    /** Widget heading displayed in the card header. */
    protected string  $heading = '';

    /** Optional subheading / description. */
    protected ?string $description = null;

    /**
     * Bootstrap grid column span.
     * 1 = col-md-3, 2 = col-md-6, 3 = col-md-9, 4 = col-md-12 (full width).
     */
    protected int $columnSpan = 1;

    /** Auto-refresh interval in seconds. 0 = disabled. */
    public int $refreshInterval = 0;

    /** @var callable|null  Returns bool whether to show this widget. */
    protected static $visibleCallback = null;

    // -----------------------------------------------------------------------
    // Visibility
    // -----------------------------------------------------------------------

    /**
     * Override to control widget visibility based on auth/config.
     */
    public static function canView(): bool
    {
        if (is_callable(static::$visibleCallback)) {
            return (bool) (static::$visibleCallback)();
        }
        return true;
    }

    // -----------------------------------------------------------------------
    // Grid helpers
    // -----------------------------------------------------------------------

    public function getColumnSpanClass(): string
    {
        return match ($this->columnSpan) {
            4       => 'col-12',
            3       => 'col-md-9',
            2       => 'col-md-6',
            default => 'col-md-3',
        };
    }

    // -----------------------------------------------------------------------
    // Polling
    // -----------------------------------------------------------------------

    protected function getPollAttribute(): string
    {
        return $this->refreshInterval > 0
            ? " wire:poll.{$this->refreshInterval}s"
            : '';
    }

    // -----------------------------------------------------------------------
    // Card wrapper helper for subclasses
    // -----------------------------------------------------------------------

    protected function cardWrapper(string $body): string
    {
        $heading     = htmlspecialchars($this->heading);
        $description = $this->description
            ? "<p class=\"card-text text-muted small\">{$this->description}</p>"
            : '';
        $poll        = $this->getPollAttribute();

        return <<<HTML
<div class="card mb-4"{$poll}>
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{$heading}</h6>
    </div>
    <div class="card-body">
        {$description}
        {$body}
    </div>
</div>
HTML;
    }
}
