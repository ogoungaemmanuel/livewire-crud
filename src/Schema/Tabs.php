<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Schema;

/**
 * Tabs — wraps multiple Tab panes in a Bootstrap 5 tab nav.
 *
 * Tabs layout component. Renders as Bootstrap 5
 * nav-tabs above the form fields, switching visibility via BS5 tab JS.
 *
 * Usage (inside FormSchema::schema()):
 *   Tabs::make('Form Tabs')
 *       ->tabs([
 *           Tab::make('General')
 *               ->icon('bi-info-circle')
 *               ->schema([TextInput::make('name')]),
 *           Tab::make('Advanced')
 *               ->schema([Toggle::make('is_featured')]),
 *       ])
 *       ->contained()
 */
class Tabs
{
    protected string $id;
    protected bool   $contained   = false;  // wrapped in .card
    protected bool   $pills       = false;  // use .nav-pills instead of .nav-tabs
    protected int    $activeIndex = 0;

    /** @var Tab[] */
    protected array $tabs = [];

    final public function __construct(string $id = 'tabs')
    {
        $this->id = 'tabs-' . preg_replace('/\W+/', '-', strtolower($id));
    }

    public static function make(string $id = 'tabs'): static
    {
        return new static($id);
    }

    // __ fluent __

    /** @param Tab[] $tabs */
    public function tabs(array $tabs): static          { $this->tabs       = $tabs;  return $this; }
    public function contained(bool $val = true): static { $this->contained  = $val;  return $this; }
    public function pills(bool $val = true): static     { $this->pills      = $val;  return $this; }
    public function activeTab(int $index): static       { $this->activeIndex = $index; return $this; }

    // __ accessors __

    /** @return Tab[] */
    public function getTabs(): array { return $this->tabs; }

    // __ rendering __

    public function render(): string
    {
        $navClass = $this->pills ? 'nav-pills' : 'nav-tabs';

        // Build nav triggers
        $triggers = '';
        foreach ($this->tabs as $i => $tab) {
            $triggers .= $tab->renderTrigger($i === $this->activeIndex) . "\n";
        }

        // Build pane content
        $panes = '';
        foreach ($this->tabs as $i => $tab) {
            $panes .= $tab->renderPane($i === $this->activeIndex) . "\n";
        }

        $inner = <<<HTML
<ul class="nav {$navClass} mb-3" role="tablist">
    {$triggers}
</ul>
<div class="tab-content">
    {$panes}
</div>
HTML;

        if ($this->contained) {
            return <<<HTML
<div class="card mb-4">
    <div class="card-body">
        {$inner}
    </div>
</div>
HTML;
        }

        return $inner;
    }

    // __ code generation __

    public function toCode(int $indent = 8): string
    {
        $pad    = str_repeat(' ', $indent + 4);
        $tabsCode = [];
        foreach ($this->tabs as $tab) {
            $tabsCode[] = $pad . $tab->toCode($indent + 8) . ',';
        }
        $inner = implode("\n", $tabsCode);
        $chain = "Tabs::make()";
        if ($this->pills)     { $chain .= '->pills()'; }
        if ($this->contained) { $chain .= '->contained()'; }
        $chain .= "->tabs([\n{$inner}\n" . str_repeat(' ', $indent) . '])';
        return $chain;
    }
}
