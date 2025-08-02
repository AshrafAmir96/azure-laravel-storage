<?php

namespace Owlfice\AzureLaravelStorage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Owlfice\AzureLaravelStorage\AzureLaravelStorage
 */
class AzureLaravelStorage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Owlfice\AzureLaravelStorage\AzureLaravelStorage::class;
    }
}
