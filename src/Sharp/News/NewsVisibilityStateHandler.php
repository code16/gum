<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\News;
use Code16\Sharp\EntityList\Commands\EntityState;

class NewsVisibilityStateHandler extends EntityState
{

    protected function buildStates(): void
    {
        $this->addState("OFFLINE", "Masqué", static::DARKGRAY_COLOR)
            ->addState("ONLINE", "En ligne", static::PRIMARY_COLOR);
    }

    protected function updateState($instanceId, $stateId): array
    {
        News::findOrFail($instanceId)->update([
            "visibility" => $stateId
        ]);

        return $this->refresh($instanceId);
    }
}