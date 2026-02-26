<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Widgets;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Abstract base for table-in-a-widget dashboard components.
 *
 * TableWidget. Extend this class and implement
 * getData() to return an Eloquent query or a LengthAwarePaginator.
 * Define columns the same way as in a full Resource.
 *
 * Usage:
 *
 *   class LatestOrdersWidget extends TableWidget
 *   {
 *       protected string $heading = 'Latest Orders';
 *       protected int    $columnSpan = 4;   // full-width
 *       protected int    $defaultPaginationPageSize = 5;
 *
 *       protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
 *       {
 *           return Order::query()->latest()->limit(20);
 *       }
 *
 *       protected function getTableColumns(): array
 *       {
 *           return [
 *               TextColumn::make('id')->label('#'),
 *               TextColumn::make('customer.name')->label('Customer'),
 *               BadgeColumn::make('status'),
 *               TextColumn::make('total')->money(),
 *           ];
 *       }
 *   }
 */
abstract class TableWidget extends Widget
{
    protected int $defaultPaginationPageSize = 5;
    public int $tableWidgetPage = 1;

    // __ Abstract interface __

    /**
     * Return an Eloquent Builder for the widget table data.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function getTableQuery();

    /**
     * Return an array of Column instances for this widget.
     *
     * @return \Xslainadmin\LivewireCrud\Columns\Column[]
     */
    abstract protected function getTableColumns(): array;

    // __ Pagination __

    protected function getTableData(): LengthAwarePaginator
    {
        return $this->getTableQuery()
                    ->paginate($this->defaultPaginationPageSize, ['*'], 'tableWidgetPage', $this->tableWidgetPage);
    }

    // __ Rendering __

    public function render(): string
    {
        $columns = $this->getTableColumns();
        $records = $this->getTableData();
        $poll    = $this->getPollAttribute();

        // Build table header
        $thead = '';
        foreach ($columns as $col) {
            if (!$col->isHidden()) {
                $thead .= $col->renderHeader();
            }
        }

        // Build table body
        $tbody = '';
        foreach ($records as $row) {
            $cells = '';
            foreach ($columns as $col) {
                if (!$col->isHidden()) {
                    // Render and bind the row variable name
                    $cells .= str_replace('$row', '$row', $col->renderCell('$row'));
                }
            }
            $tbody .= "@php \$row = \$loop->current; @endphp<tr>{$cells}</tr>";
        }

        // Pagination
        $pagination = $records->hasPages()
            ? '{{ $records->links() }}'
            : '';

        $heading     = htmlspecialchars($this->heading);
        $description = $this->description
            ? "<p class=\"card-text text-muted small mb-2\">{$this->description}</p>"
            : '';

        return <<<BLADE
<div class="card mb-4"{$poll}>
    <div class="card-header">
        <h6 class="mb-0">{$heading}</h6>
    </div>
    <div class="card-body p-0">
        {$description}
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>{$thead}</tr>
                </thead>
                <tbody>
                    @foreach(\$records as \$row)
                    <tr>
                        @foreach(\$columns as \$col)
                        {!! \$col->renderCell('\\\$row') !!}
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(\$records->hasPages())
        <div class="p-3">{!! \$records->links() !!}</div>
        @endif
    </div>
</div>
BLADE;
    }
}
