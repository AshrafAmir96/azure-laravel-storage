<?php

namespace Owlfice\AzureLaravelStorage;

use Owlfice\AzureLaravelStorage\Commands\AzureLaravelStorageCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AzureLaravelStorageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('azure-laravel-storage')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_azure_laravel_storage_table')
            ->hasCommand(AzureLaravelStorageCommand::class);
    }
}
