<?php

namespace Code16\Gum\Tests;

use Code16\Gum\GumServiceProvider;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
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
        $app['config']->set('database.default', 'testing');

        DB::statement(DB::raw('PRAGMA foreign_keys=ON'));
    }

    protected function getPackageProviders($app)
    {
        return [GumServiceProvider::class, ScoutServiceProvider::class];
    }
}
