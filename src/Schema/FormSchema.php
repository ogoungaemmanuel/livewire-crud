<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Schema;

use Xslainadmin\LivewireCrud\Fields\Field;
use Xslainadmin\LivewireCrud\Schema\Section;
use Xslainadmin\LivewireCrud\Schema\Tabs;

/**
 * Holds an ordered array of Field / Section / Tabs objects and renders a Bootstrap 5 form grid.
 *
 * Usage in a Resource:
 *   public static function form(FormSchema $schema): FormSchema
 *   {
 *       return $schema->schema([
 *           TextInput::make('name')->required()->columnSpan('1/2'),
 *           TextInput::make('email')->email()->required()->columnSpan('1/2'),
 *           Textarea::make('bio')->columnSpanFull(),
 *       ])->columns(2);
 *   }
 */
class FormSchema
{
    /** @var Field[] */
    protected array $fields  = [];
    protected int   $columns = 2;   // default Bootstrap grid columns (1–4)
    protected bool  $inCard  = true;

    public static function make(): static
    {
        return new static();
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    /** @param  array<Field|Section|Tabs> $fields */
    public function schema(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    public function columns(int $columns): static
    {
        $this->columns = max(1, min(4, $columns));
        return $this;
    }

    public function withoutCard(): static
    {
        $this->inCard = false;
        return $this;
    }

    /* ── accessors ───────────────────────────────────────────────────────── */

    /** @return array<Field|Section|Tabs> */
    public function getFields(): array { return $this->fields; }

    public function getColumns(): int  { return $this->columns; }

    /* ── rendering ───────────────────────────────────────────────────────── */

    /**
     * Render all fields wrapped in a Bootstrap 5 row.
     */
    public function render(): string
    {
        $inner = '';
        foreach ($this->fields as $item) {
            // Section and Tabs have their own render(); Fields check isVisible()
            if ($item instanceof Section || $item instanceof Tabs) {
                $inner .= $item->render() . "\n";
            } elseif ($item instanceof Field) {
                if ($item->isVisible()) {
                    $inner .= $item->render() . "\n";
                }
            } else {
                // Fallback: anything with a render() method
                $inner .= $item->render() . "\n";
            }
        }

        return "<div class=\"row g-3\">\n{$inner}</div>";
    }

    /* ── code generation (for Resource.stub) ────────────────────────────── */

    public function toCode(int $indent = 12): string
    {
        $pad = str_repeat(' ', $indent);
        $lines = [];
        foreach ($this->fields as $field) {
            $lines[] = $pad . $field->toCode() . ',';
        }
        return implode("\n", $lines);
    }
}
