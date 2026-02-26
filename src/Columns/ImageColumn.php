<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Image column — renders a thumbnail from a stored path or URL.
 *
 * Usage:
 *   ImageColumn::make('avatar')->circular()->size(40)
 *   ImageColumn::make('photo')->disk('public')->directory('users')
 */
class ImageColumn extends Column
{
    protected int     $size      = 40;
    protected bool    $circular  = false;
    protected ?string $disk      = 'public';
    protected ?string $directory = null;
    protected ?string $fallback  = null;

    public function size(int $size): static          { $this->size      = $size;      return $this; }
    public function circular(bool $c = true): static { $this->circular  = $c;         return $this; }
    public function disk(string $disk): static       { $this->disk      = $disk;      return $this; }
    public function directory(string $dir): static   { $this->directory = $dir;       return $this; }
    public function fallback(string $url): static    { $this->fallback  = $url;       return $this; }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr       = "{$rowVar}->{$this->name}";
        $size       = $this->size;
        $radius     = $this->circular ? 'border-radius:50%;' : 'border-radius:4px;';
        $fallback   = $this->fallback ?? "https://ui-avatars.com/api/?name=&background=0d6efd&color=fff&size={$size}";

        if ($this->directory) {
            $src = "asset('" . rtrim($this->directory, '/') . "/' . {$expr})";
        } else {
            $src = "Storage::disk('{$this->disk}')->url({$expr} ?? '')";
        }

        return <<<BLADE
<td class="text-center">
    @if({$expr})
        <img src="{{ {$src} }}" width="{$size}" height="{$size}" style="object-fit:cover;{$radius}" loading="lazy">
    @else
        <img src="{$fallback}" width="{$size}" height="{$size}" style="object-fit:cover;{$radius}">
    @endif
</td>
BLADE;
    }

    public function toCode(): string
    {
        $chain = "ImageColumn::make('{$this->name}')";
        if ($this->circular) { $chain .= '->circular()'; }
        if ($this->size !== 40) { $chain .= "->size({$this->size})"; }
        if ($this->disk && $this->disk !== 'public') { $chain .= "->disk('{$this->disk}')"; }
        if ($this->directory) { $chain .= "->directory('{$this->directory}')"; }
        return $chain . $this->commonChain();
    }
}
