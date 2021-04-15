<?php

namespace Code16\Gum\Tests\Feature\Utils;

use Code16\Gum\Sharp\Sidepanels\SidepanelSharpForm;
use Code16\Gum\Sharp\Tiles\TileblockSharpForm;

trait WithSharpFaker
{
    protected function fakeSidepanelSharpForm($class)
    {
        app()->bind(
            SidepanelSharpForm::class,
            function ($app) use ($class) {
                return $class;
            }
        );
    }

    protected function fakeTileblockSharpForm($class)
    {
        app()->bind(
            TileblockSharpForm::class,
            function ($app) use ($class) {
                return $class;
            }
        );
    }
}