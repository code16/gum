<?php

namespace Code16\Gum\Tests;

use Code16\Gum\GumServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Orchestra\Testbench\Traits\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(dirname(__DIR__).'/database/factories');

        DB::statement(DB::raw('PRAGMA foreign_keys=1'));
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function getPackageProviders($app)
    {
        return [GumServiceProvider::class];
    }
}
