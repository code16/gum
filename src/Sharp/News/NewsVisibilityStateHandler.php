<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\News;
use Code16\Sharp\EntityList\Commands\EntityState;

class NewsVisibilityStateHandler extends EntityState
{

    /**
     * @return mixed
     */
    protected function buildStates()
    {
        $this->addState("OFFLINE", "Masqué", static::DARKGRAY_COLOR)
            ->addState("ONLINE", "En ligne", static::PRIMARY_COLOR);
    }

    /**
     * @param string $instanceId
     * @param string $stateId
     * @return mixed
     */
    protected function updateState($instanceId, $stateId)
    {
        News::findOrFail($instanceId)->update([
            "visibility" => $stateId
        ]);

        return $this->refresh($instanceId);
    }
}