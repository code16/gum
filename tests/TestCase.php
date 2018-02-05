<?php

namespace Code16\Gum\Tests;

use Code16\Gum\GumServiceProvider;
use Code16\Gum\Tests\Feature\Utils\UserModel;
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

        $app['config']->set([
            "sharp" => [
                "entities" => [
                    "root_sections" => [
                        "list" => \Code16\Gum\Sharp\Sections\RootSectionSharpList::class,
                        "form" => \Code16\Gum\Sharp\Sections\RootSectionSharpForm::class,
                        "validator" => \Code16\Gum\Sharp\Sections\SectionSharpValidator::class,
                    ],
                    "sections" => [
                        "list" => \Code16\Gum\Sharp\Sections\SectionSharpList::class,
                        "form" => \Code16\Gum\Sharp\Sections\SectionSharpForm::class,
                        "validator" => \Code16\Gum\Sharp\Sections\SectionSharpValidator::class,
                    ],
                    "pages" => [
                        "list" => \Code16\Gum\Sharp\Pages\PageSharpList::class,
                        "form" => \Code16\Gum\Sharp\Pages\PageSharpForm::class,
                        "validator" => \Code16\Gum\Sharp\Pages\PageSharpValidator::class,
                    ],
                    "pagegroups" => [
                        "list" => \Code16\Gum\Sharp\Pagegroups\PagegroupSharpList::class,
                        "form" => \Code16\Gum\Sharp\Pagegroups\PagegroupSharpForm::class,
                        "validator" => \Code16\Gum\Sharp\Pagegroups\PagegroupSharpValidator::class,
                    ],
                    "tileblocks" => [
                        "list" => \Code16\Gum\Sharp\Tiles\AcaciaTileblockSharpList::class,
                        "forms" => [
                            "checkerboard" => [
                                "form" => \Code16\Gum\Sharp\Tiles\Forms\TileblockCheckerboardSharpForm::class,
                                "validator" => \Code16\Gum\Sharp\Tiles\Forms\TileblockCheckerboardSharpValidator::class,
                            ],
                        ]
                    ],
                    "sidepanels" => [
                        "list" => \Code16\Gum\Sharp\Sidepanels\AcaciaSidepanelSharpList::class,
                        "forms" => [
                            "visual" => [
                                "form" => \Code16\Gum\Sharp\Sidepanels\Forms\SidepanelVisualSharpForm::class,
                                "validator" => \Code16\Gum\Sharp\Sidepanels\Forms\SidePanelVisualSharpValidator::class,
                            ],
                            "download" => [
                                "form" => \Code16\Gum\Sharp\Sidepanels\Forms\SidepanelDownloadSharpForm::class,
                                "validator" => \Code16\Gum\Sharp\Sidepanels\Forms\SidePanelDownloadSharpValidator::class,
                            ],
                        ]
                    ],
                ]
            ]
        ]);

        app(\Illuminate\Database\Eloquent\Factory::class)->define(UserModel::class, function (\Faker\Generator $faker) {
            return [
                'email' => $faker->unique()->safeEmail,
                'name' => $faker->name,
                'password' => bcrypt('secret'),
                'remember_token' => str_random(10),
            ];
        });
    }

    protected function getPackageProviders($app)
    {
        return [GumServiceProvider::class];
    }
}
