<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Sharp\Menus\MenuSharpForm;
use Code16\Gum\Sharp\Menus\MenuSharpList;
use Code16\Gum\Sharp\News\NewsSharpForm;
use Code16\Gum\Sharp\News\NewsSharpList;
use Code16\Gum\Sharp\News\NewsSharpValidator;
use Code16\Gum\Sharp\Pages\PageInPagegroupSharpEmbeddedList;
use Code16\Gum\Sharp\Pages\PageSharpForm;
use Code16\Gum\Sharp\Pages\PageSharpShow;
use Code16\Gum\Sharp\Pages\PageSharpValidator;
use Code16\Gum\Sharp\Sidepanels\SidepanelSharpForm;
use Code16\Gum\Sharp\Sidepanels\SidepanelSharpList;
use Code16\Gum\Sharp\Tiles\TileblockSharpForm;
use Code16\Gum\Sharp\Tiles\TileblockSharpList;
use Code16\Gum\Sharp\Tiles\TileblockSharpValidator;
use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

class GumSharpTestCase extends TestCase
{
    use SharpAssertions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(\factory(UserModel::class)->create());
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
                    "menus" => [
                        "form" => MenuSharpForm::class,
                        "list" => MenuSharpList::class
                    ],
                    "news" => [
                        "list" => NewsSharpList::class,
                        "form" => NewsSharpForm::class,
                        "validator" => NewsSharpValidator::class
                    ],
                    "sidepanels" => [
                        "list" => SidepanelSharpList::class,
                        "form" => SidepanelSharpForm::class
                    ],
                    "pages" => [
                        "form" => PageSharpForm::class,
                        "list" => PageInPagegroupSharpEmbeddedList::class,
                        "show" => PageSharpShow::class,
                        "validator" => PageSharpValidator::class
                    ],
                    "tileblocks" => [
                        "list" => TileblockSharpList::class,
                        "form" => TileblockSharpForm::class,
                        "validator" => TileblockSharpValidator::class
                    ]
                ]
            ]
        ]);

        app(Factory::class)->define(UserModel::class, function (Generator $faker) {
            return [
                'email' => $faker->unique()->safeEmail,
                'name' => $faker->name,
                'password' => bcrypt('secret'),
                'remember_token' => Str::random(10),
            ];
        });
    }
}