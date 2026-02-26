<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Schema;

use Xslainadmin\LivewireCrud\Actions\Action;
use Xslainadmin\LivewireCrud\Columns\Column;
use Xslainadmin\LivewireCrud\Filters\Filter;

/**
 * Holds columns, filters, row actions, and bulk actions for a resource table.
 *
 * Usage in a Resource:
 *   public static function table(TableSchema $table): TableSchema
 *   {
 *       return $table
 *           ->columns([TextColumn::make('name')->searchable()->sortable(), ...])
 *           ->filters([SelectFilter::make('status')->options([...])])
 *           ->actions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
 *           ->bulkActions([BulkAction::make('delete')->requiresConfirmation()]);
 *   }
 */
class TableSchema
{
    /** @var Column[] */
    protected array $columns     = [];
    /** @var Filter[] */
    protected array $filters     = [];
    /** @var Action[] */
    protected array $actions     = [];
    /** @var Action[] */
    protected array $bulkActions = [];
    /** @var Action[] */
    protected array $headerActions = [];

    protected bool  $striped     = true;
    protected bool  $hoverable   = true;
    protected bool  $bordered    = false;
    protected bool  $responsive  = true;
    protected ?int  $perPage     = 10;
    protected array $perPageOptions = [10, 25, 50, 100];
    protected string $defaultSort     = 'id';
    protected string $defaultSortDir  = 'desc';

    // ── Enterprise additions ──────────────────────────────────────────────
    /** Allow row drag-to-reorder (requires sortable JS). */
    protected bool $reorderable = false;
    /** Auto-refresh interval in seconds (0 = disabled). */
    protected int $pollSeconds  = 0;
    /** @var callable|null  Closure($record): string — row click URL. */
    protected $recordUrlCallback = null;
    /** Heading shown when the table has no records. */
    protected ?string $emptyStateHeading     = null;
    /** Description shown when the table has no records. */
    protected ?string $emptyStateDescription = null;

    public static function make(): static
    {
        return new static();
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    /** @param Column[] $columns */
    public function columns(array $columns): static { $this->columns = $columns; return $this; }
    /** @param Filter[] $filters */
    public function filters(array $filters): static { $this->filters = $filters; return $this; }
    /** @param Action[] $actions */
    public function actions(array $actions): static { $this->actions = $actions; return $this; }
    /** @param Action[] $actions */
    public function bulkActions(array $actions): static { $this->bulkActions = $actions; return $this; }
    /** @param Action[] $actions  Placed in the toolbar (top-right of table) */
    public function headerActions(array $actions): static { $this->headerActions = $actions; return $this; }

    public function striped(bool $val = true): static    { $this->striped   = $val; return $this; }
    public function hoverable(bool $val = true): static  { $this->hoverable = $val; return $this; }
    public function bordered(bool $val = true): static   { $this->bordered  = $val; return $this; }
    public function defaultSort(string $col, string $dir = 'asc'): static
    {
        $this->defaultSort    = $col;
        $this->defaultSortDir = $dir;
        return $this;
    }
    public function perPage(int $n): static           { $this->perPage = $n; return $this; }
    public function perPageOptions(array $opts): static { $this->perPageOptions = $opts; return $this; }

    /** Enable drag-to-reorder rows. */
    public function reorderable(bool $val = true): static { $this->reorderable = $val; return $this; }

    /** Auto-poll the table every $seconds seconds (0 = disabled). */
    public function poll(int $seconds): static { $this->pollSeconds = $seconds; return $this; }

    /**
     * Make each row clickable.
     * The callback receives the model record and must return a URL string.
     *
     * @param  callable(mixed $record): string $callback
     */
    public function recordUrl(callable $callback): static { $this->recordUrlCallback = $callback; return $this; }

    /** Override the heading shown in the empty-state panel. */
    public function emptyStateHeading(string $heading): static { $this->emptyStateHeading = $heading; return $this; }

    /** Override the description shown in the empty-state panel. */
    public function emptyStateDescription(string $text): static { $this->emptyStateDescription = $text; return $this; }

    /* ── accessors ───────────────────────────────────────────────────────── */

    /** @return Column[] */
    public function getColumns(): array      { return $this->columns; }
    /** @return Filter[] */
    public function getFilters(): array      { return $this->filters; }
    /** @return Action[] */
    public function getActions(): array      { return $this->actions; }
    /** @return Action[] */
    public function getBulkActions(): array  { return $this->bulkActions; }
    /** @return Action[] */
    public function getHeaderActions(): array { return $this->headerActions; }
    public function getDefaultSort(): string { return $this->defaultSort; }
    public function getDefaultSortDir(): string { return $this->defaultSortDir; }
    public function getPerPage(): ?int       { return $this->perPage; }
    public function isReorderable(): bool    { return $this->reorderable; }
    public function getPollSeconds(): int    { return $this->pollSeconds; }
    public function getRecordUrlCallback(): ?callable { return $this->recordUrlCallback; }
    public function getEmptyStateHeading(): string    { return $this->emptyStateHeading ?? 'No records found'; }
    public function getEmptyStateDescription(): string { return $this->emptyStateDescription ?? 'Try changing your filters or adding new records.'; }

    public function getTableClass(): string
    {
        $classes = ['table'];
        if ($this->striped)   { $classes[] = 'table-striped'; }
        if ($this->hoverable) { $classes[] = 'table-hover'; }
        if ($this->bordered)  { $classes[] = 'table-bordered'; }
        return implode(' ', $classes);
    }

    /* ── rendering ───────────────────────────────────────────────────────── */

    /**
     * Render the <thead> row (column headers).
     */
    public function renderHeader(): string
    {
        $html = "<thead class=\"table-dark\">\n<tr>\n";
        if ($this->bulkActions) {
            $html .= "    <th width=\"40\"><input type=\"checkbox\" x-model=\"selectAll\" class=\"form-check-input\"></th>\n";
        }
        foreach ($this->columns as $col) {
            if (!$col->isHidden()) {
                $html .= '    ' . $col->renderHeader() . "\n";
            }
        }
        if ($this->actions) {
            $html .= "    <th class=\"text-end\">Actions</th>\n";
        }
        $html .= "</tr>\n</thead>";
        return $html;
    }

    /**
     * Render the filter toolbar row.
     */
    public function renderFilterToolbar(): string
    {
        if (empty($this->filters)) {
            return '';
        }
        $inner = '';
        foreach ($this->filters as $filter) {
            $inner .= $filter->render() . "\n";
        }
        return "<div class=\"row g-2 align-items-center mb-3\">\n{$inner}</div>";
    }

    /* ── code generation (for Resource.stub) ────────────────────────────── */

    public function columnsToCode(int $indent = 16): string
    {
        $pad   = str_repeat(' ', $indent);
        $lines = [];
        foreach ($this->columns as $col) {
            $lines[] = $pad . $col->toCode() . ',';
        }
        return implode("\n", $lines);
    }

    public function filtersToCode(int $indent = 16): string
    {
        $pad   = str_repeat(' ', $indent);
        $lines = [];
        foreach ($this->filters as $filter) {
            $lines[] = $pad . $filter->toCode() . ',';
        }
        return implode("\n", $lines);
    }

    public function actionsToCode(int $indent = 16): string
    {
        $pad   = str_repeat(' ', $indent);
        $lines = [];
        foreach ($this->actions as $action) {
            $lines[] = $pad . $action->toCode() . ',';
        }
        return implode("\n", $lines);
    }

    public function bulkActionsToCode(int $indent = 16): string
    {
        $pad   = str_repeat(' ', $indent);
        $lines = [];
        foreach ($this->bulkActions as $action) {
            $lines[] = $pad . $action->toCode() . ',';
        }
        return implode("\n", $lines);
    }
}
