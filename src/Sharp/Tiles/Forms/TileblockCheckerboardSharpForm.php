<?php

namespace Code16\Gum\Sharp\Tiles\Forms;

class TileblockCheckerboardSharpForm extends TileblockSharpForm
{

    /**
     * @return array
     */
    protected function tileFields(): array
    {
        return [
            "visual",
            "title",
        ];
    }

    /**
     * @return int
     */
    protected function maxTilesCount(): int
    {
        return 3;
    }

    protected function layoutVariants(): array
    {
        return [
            "large" => "Grand"
        ];
    }

    /**
     * @return string
     */
    protected function layoutKey(): string
    {
        return "checkerboard";
    }

    /**
     * @return string
     */
    protected function layoutLabel(): string
    {
        return "Damier";
    }
}