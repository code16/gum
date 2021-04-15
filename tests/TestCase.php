<?php

namespace Code16\Gum\Tests;

use Code16\Gum\GumServiceProvider;
use Code16\Sharp\SharpServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'sqlite']);
        $this->withFactories(dirname(__DIR__).'/database/factories');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [GumServiceProvider::class, SharpServiceProvider::class, ScoutServiceProvider::class];
    }
}
