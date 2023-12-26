<?php

namespace Xslainadmin\LivewireCrud;

use Illuminate\Support\Facades\Facade;

class LivewireCrudFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'livewire-crud';
    }
}
