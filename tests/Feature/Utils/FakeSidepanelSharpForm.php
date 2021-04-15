<?php

namespace Code16\Gum\Tests\Feature\Utils;

use Code16\Gum\Sharp\Sidepanels\SidepanelSharpForm;

class FakeSidepanelSharpForm extends SidepanelSharpForm
{

    function create(): array
    {
        return parent::create();
    }

    function update($id, array $data)
    {
        return parent::update($id, $data);
    }

    function delete($id): void
    {
        parent::delete($id);
    }

    function buildFormFields(): void
    {
        parent::buildFormFields();
    }

    function buildFormLayout(): void
    {
        parent::buildFormLayout();
    }

    function find($id): array
    {
        return parent::find($id);
    }

    protected function layoutKey(): string
    {
        return "key";
    }

    protected function layoutLabel(): string
    {
        return "label";
    }
}
