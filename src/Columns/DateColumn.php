<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Date/time column — formats a Carbon timestamp for display.
 *
 * Usage:
 *   DateColumn::make('created_at')->format('d M Y')->since()
 *   DateColumn::make('published_at')->format('M d, Y h:i A')
 */
class DateColumn extends Column
{
    protected string $format    = 'd M Y';
    protected bool   $since     = false;
    protected bool   $time      = false;
    protected ?string $timezone = null;

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /** Display as human diff ("3 days ago"). */
    public function since(bool $since = true): static
    {
        $this->since = $since;
        return $this;
    }

    /** Include time in the default format. */
    public function dateTime(): static
    {
        $this->format = 'd M Y H:i';
        $this->time   = true;
        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr = "{$rowVar}->{$this->name}";

        if ($this->since) {
            $rendered = "optional({$expr})?->diffForHumans()";
        } else {
            $formatted = addslashes($this->format);
            $rendered  = "optional({$expr})?->format('{$formatted}')";
        }

        $default = $this->default ?? '—';

        return "<td class=\"text-nowrap\">{{ {$rendered} ?? '{$default}' }}</td>";
    }

    public function toCode(): string
    {
        $chain = "DateColumn::make('{$this->name}')";
        if ($this->since)           { $chain .= '->since()'; }
        elseif ($this->time)        { $chain .= '->dateTime()'; }
        elseif ($this->format !== 'd M Y') {
            $chain .= "->format('" . addslashes($this->format) . "')";
        }
        if ($this->timezone) { $chain .= "->timezone('" . $this->timezone . "')"; }
        return $chain . $this->commonChain();
    }
}
