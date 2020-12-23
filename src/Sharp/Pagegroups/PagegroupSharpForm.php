<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\Pagegroup;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Illuminate\Support\Str;

class PagegroupSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    function buildFormFields(): void
    {
        $this->addField(
            SharpFormTextareaField::make("title")
                ->setLabel("Titre")
                ->setRowCount(2)
        )->addField(
            SharpFormTextField::make("short_title")
                ->setLabel("Titre menu")
                ->setHelpMessage("Utilisé dans les menus / fils d'ariane. Facultatif.")
        )->addField(
            SharpFormTextField::make("slug")
                ->setLabel("URL")
                ->setHelpMessage("Il s'agit de l'URL (slug) du groupe de pages ; laissez ce champ vide pour remplissage automatique à partir du titre. Ne peut contenir que des lettres, des chiffres et des tirets.Attention, si vous modifiez cette valeur, les URLs du site seront modifiées.")
        )->addField(
            SharpFormListField::make("pages")
                ->setLabel("Pages")
                ->setSortable(true)
                ->setOrderAttribute("pagegroup_order")
                ->setAddable(false)
                ->setRemovable(false)
                ->addItemField(
                    SharpFormTextField::make("title")
                        ->setReadOnly(true)
                )
        );
    }

    function buildFormLayout(): void
    {
        $this->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("title")
                ->withSingleField("short_title")
                ->withSingleField("slug");

        })->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("pages", function(FormLayoutColumn $item) {
                    $item->withSingleField("title");
                });
        });
    }

    function find($id): array
    {
        return $this
            ->transform(Pagegroup::with("pages")->findOrFail($id));
    }

    function update($id, array $data)
    {
        $pageGroup = $id ? Pagegroup::findOrFail($id) : new Pagegroup();

        if(!trim($data["slug"])) {
            $data["slug"] = Str::slug($data["title"]);
        }

        $this->save($pageGroup, $data);

        return $pageGroup->id;
    }

    function delete($id): void
    {
        Pagegroup::findOrFail($id)->delete();
    }
}