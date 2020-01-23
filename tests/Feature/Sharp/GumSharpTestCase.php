<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class GumSharpTestCase extends TestCase
{
    use RefreshDatabase, SharpAssertions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(factory(UserModel::class)->create());
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

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
                        "list" => \Code16\Gum\Sharp\Tiles\TileblockSharpList::class,
                        "forms" => [
                        ]
                    ],
                    "sidepanels" => [
                        "list" => \Code16\Gum\Sharp\Sidepanels\AcaciaPageSidepanelSharpList::class,
                        "forms" => [
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
                'remember_token' => Str::random(10),
            ];
        });
    }
}