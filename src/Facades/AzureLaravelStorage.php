<?php

namespace Ashraf Amir\AzureLaravelStorage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ashraf Amir\AzureLaravelStorage\AzureLaravelStorage
 */
class AzureLaravelStorage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ashraf Amir\AzureLaravelStorage\AzureLaravelStorage::class;
    }
}
