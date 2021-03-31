<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\News;
use Code16\Sharp\EntityList\Commands\EntityState;

class NewsVisibilityStateHandler extends EntityState
{

    protected function buildStates(): void
    {
        $this->addState("OFFLINE", "Masqué", "#8C9BA5")
            ->addState("ONLINE", "En ligne", config("sharp.theme.primary_color"));
    }

    protected function updateState($instanceId, $stateId): array
    {
        News::findOrFail($instanceId)->update([
            "visibility" => $stateId
        ]);

        return $this->refresh($instanceId);
    }
}