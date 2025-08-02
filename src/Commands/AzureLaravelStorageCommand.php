<?php

namespace Ashraf Amir\AzureLaravelStorage\Commands;

use Illuminate\Console\Command;

class AzureLaravelStorageCommand extends Command
{
    public $signature = 'azure-laravel-storage';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
