<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ModuleFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'module';
    }
}
