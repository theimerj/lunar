<?php

namespace Lunar\Tests\Core;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\Facades\ModelManifest;
use Lunar\Facades\Taxes;
use Lunar\LunarServiceProvider;
use Lunar\Tests\Core\Stubs\TestTaxDriver;
use Lunar\Tests\Core\Stubs\TestUrlGenerator;
use Lunar\Tests\Core\Stubs\User;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\StructureDiscoverer\Discover;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('lunar.urls.generator', TestUrlGenerator::class);
        Config::set('lunar.taxes.driver', 'test');
        Config::set('lunar.media.collection', 'images');

        Taxes::extend('test', function ($app) {
            return $app->make(TestTaxDriver::class);
        });

        activity()->disableLogging();

        // Freeze time to avoid timestamp errors
        $this->freezeTime();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->replaceModelsForTesting();

        ModelManifest::morphMap();
    }

    /**
     * Replace Lunar models with test models for testing
     * functionality with model extending.
     */
    protected function replaceModelsForTesting(): void
    {
        $modelClasses = Discover::in(__DIR__.'/Stubs/Models')
            ->classes()
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = ModelManifest::guessContractClass($modelClass);
            ModelManifest::replace($interfaceClass, $modelClass);
        }
    }
}
