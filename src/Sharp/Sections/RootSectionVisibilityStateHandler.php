<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Sharp\EntityList\Commands\EntityState;

class RootSectionVisibilityStateHandler extends EntityState
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
        Section::findOrFail($instanceId)->url->update([
            "visibility" => $stateId
        ]);

        return $this->refresh($instanceId);
    }
}