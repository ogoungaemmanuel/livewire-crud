<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Text input field — handles varchar, char, url, email, password, tel, number.
 *
 * Usage:
 *   TextInput::make('name')->required()->maxLength(255)
 *   TextInput::make('email')->email()->required()
 *   TextInput::make('price')->numeric()->prefix('$')
 */
class TextInput extends Field
{
    protected string  $inputType  = 'text';
    protected ?int    $maxLength  = null;
    protected ?int    $minLength  = null;
    protected ?string $prefix     = null;
    protected ?string $suffix     = null;
    protected bool    $autocomplete = true;

    public function email(): static        { $this->inputType = 'email'; $this->rules['email'] = 'email'; return $this; }
    public function password(): static     { $this->inputType = 'password'; return $this; }
    public function url(): static          { $this->inputType = 'url'; $this->rules['url'] = 'url'; return $this; }
    public function tel(): static          { $this->inputType = 'tel'; return $this; }
    public function numeric(): static      { $this->inputType = 'number'; $this->rules['numeric'] = 'numeric'; return $this; }
    public function integer(): static      { $this->inputType = 'number'; $this->rules['integer'] = 'integer'; return $this; }
    public function alphaNum(): static     { $this->rules['alpha_num'] = 'alpha_num'; return $this; }

    public function maxLength(int $max): static
    {
        $this->maxLength          = $max;
        $this->rules['max']       = "max:{$max}";
        return $this;
    }

    public function minLength(int $min): static
    {
        $this->minLength      = $min;
        $this->rules['min']   = "min:{$min}";
        return $this;
    }

    public function prefix(string $prefix): static { $this->prefix = $prefix; return $this; }
    public function suffix(string $suffix): static { $this->suffix = $suffix; return $this; }
    public function disableAutocomplete(): static  { $this->autocomplete = false; return $this; }

    public function render(): string
    {
        $label       = htmlspecialchars($this->getLabel());
        $required    = $this->required ? '<span class="text-danger">*</span>' : '';
        $attrs       = $this->commonAttrs($this->inputType);
        $hint        = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $max         = $this->maxLength ? " maxlength=\"{$this->maxLength}\"" : '';
        $min         = $this->minLength ? " minlength=\"{$this->minLength}\"" : '';
        $autocomplete = $this->autocomplete ? '' : ' autocomplete="off"';
        $error       = $this->errorBlock();
        $spanClass   = $this->spanClass();

        $prefixHtml  = $this->prefix ? "<span class=\"input-group-text\">" . htmlspecialchars($this->prefix) . "</span>" : '';
        $suffixHtml  = $this->suffix ? "<span class=\"input-group-text\">" . htmlspecialchars($this->suffix) . "</span>" : '';
        $wrapOpen    = ($this->prefix || $this->suffix) ? '<div class="input-group">' : '';
        $wrapClose   = ($this->prefix || $this->suffix) ? '</div>' : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$this->id}" class="form-label">{$label}{$required}{$hint}</label>
    {$wrapOpen}{$prefixHtml}<input {$attrs}{$max}{$min}{$autocomplete}>{$suffixHtml}{$wrapClose}
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "TextInput::make('{$this->name}')";
        if ($this->inputType === 'email')    { $chain .= '->email()'; }
        if ($this->inputType === 'password') { $chain .= '->password()'; }
        if ($this->inputType === 'url')      { $chain .= '->url()'; }
        if ($this->inputType === 'tel')      { $chain .= '->tel()'; }
        if ($this->inputType === 'number')   { $chain .= '->numeric()'; }
        if ($this->maxLength !== null)       { $chain .= "->maxLength({$this->maxLength})"; }
        if ($this->prefix)                   { $chain .= "->prefix('" . addslashes($this->prefix) . "')"; }
        if ($this->suffix)                   { $chain .= "->suffix('" . addslashes($this->suffix) . "')"; }
        return $chain . $this->commonChain();
    }
}
