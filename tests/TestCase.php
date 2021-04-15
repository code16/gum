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

        $this->loadLaravelMigrations();
        $this->withFactories(dirname(__DIR__).'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [GumServiceProvider::class, SharpServiceProvider::class, ScoutServiceProvider::class];
    }
}
