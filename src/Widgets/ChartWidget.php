<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Widgets;

/**
 * Abstract Livewire class for Chart.js-powered dashboard widgets.
 *
 * Extend this in your module, implement getData() and configure optional
 * properties, then drop the component in a dashboard Blade view.
 *
 * Usage:
 *
 *   class UserRegistrationsChart extends ChartWidget
 *   {
 *       protected string $heading = 'User Registrations';
 *       protected string $chartType = 'bar';
 *
 *       protected function getData(): array
 *       {
 *           $months = collect(range(1, 12))->map(fn ($m) => User::whereMonth('created_at', $m)->count())->all();
 *           return [
 *               'labels' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
 *               'datasets' => [[
 *                   'label' => 'Registrations',
 *                   'data'  => $months,
 *                   'backgroundColor' => 'rgba(13,110,253,.3)',
 *                   'borderColor'     => 'rgba(13,110,253,1)',
 *               ]],
 *           ];
 *       }
 *   }
 */
abstract class ChartWidget extends \Livewire\Component
{
    /** Chart heading displayed above the chart. */
    protected string  $heading         = '';

    /** Chart.js chart type: 'bar' | 'line' | 'pie' | 'doughnut' | 'polarArea' | 'radar' */
    protected string  $chartType       = 'line';

    /** Height of the canvas in pixels (or null to let Chart.js decide). */
    protected ?int    $chartHeight     = 300;

    /** Auto-refresh interval in seconds (0 = disabled). */
    public int        $refreshInterval = 0;

    /** Optional description / subtitle. */
    protected ?string $description     = null;

    /**
     * Return Chart.js-compatible data array:
     *   ['labels' => [...], 'datasets' => [[...]]]
     *
     * @return array{labels: list<string>, datasets: list<array>}
     */
    abstract protected function getData(): array;

    /**
     * Override to return Chart.js options array (merged with defaults).
     */
    protected function getOptions(): array
    {
        return [
            'responsive'          => true,
            'maintainAspectRatio' => false,
        ];
    }

    /**
     * Called by the Livewire component to expose chart data.
     * Override getData() and getOptions() in the concrete class.
     */
    public function getChartData(): array
    {
        return [
            'type'    => $this->chartType,
            'data'    => $this->getData(),
            'options' => $this->getOptions(),
        ];
    }

    public function render(): string
    {
        $heading     = htmlspecialchars($this->heading);
        $description = $this->description
            ? "<p class=\"card-text text-muted small\">{$this->description}</p>"
            : '';
        $heightStyle = $this->chartHeight ? "height:{$this->chartHeight}px;" : '';
        $canvasId    = 'chart-' . md5(static::class);
        $polling     = $this->refreshInterval > 0 ? " wire:poll.{$this->refreshInterval}s=\"refreshChart\"" : '';
        $chartData   = htmlspecialchars(json_encode($this->getChartData()), ENT_QUOTES);

        return <<<HTML
<div class="card mb-4"{$polling}
     x-data="{ init() { const ctx = document.getElementById('{$canvasId}'); new Chart(ctx, JSON.parse(this.\$el.dataset.chart)); } }"
     data-chart="{$chartData}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{$heading}</h6>
    </div>
    <div class="card-body">
        {$description}
        <div style="{$heightStyle}">
            <canvas id="{$canvasId}"></canvas>
        </div>
    </div>
</div>
HTML;
    }
}
