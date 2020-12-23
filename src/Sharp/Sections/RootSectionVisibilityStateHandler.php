<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Sharp\EntityList\Commands\EntityState;
use Code16\Sharp\Exceptions\EntityList\SharpInvalidEntityStateException;

class RootSectionVisibilityStateHandler extends EntityState
{

    protected function buildStates(): void
    {
        $this->addState("OFFLINE", "Masqué", static::DARKGRAY_COLOR)
            ->addState("ONLINE", "En ligne", static::PRIMARY_COLOR);
    }

    protected function updateState($instanceId, $stateId): array
    {
        $section = Section::findOrFail($instanceId);

        if($section->isHome()) {
            throw new SharpInvalidEntityStateException("La page d'accueil ne peut pas être masquée.");
        }

        $section->url->update([
            "visibility" => $stateId
        ]);

        return $this->refresh($instanceId);
    }
}