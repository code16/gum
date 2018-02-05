<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\Pagegroup;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpFormContext;

class PagegroupSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, WithSharpFormContext;

    /**
     * Build form fields using ->addField()
     *
     * @return void
     */
    function buildFormFields()
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
        );
    }

    /**
     * Build form layout using ->addTab() or ->addColumn()
     *
     * @return void
     */
    function buildFormLayout()
    {
        $this->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("title")
                ->withSingleField("short_title")
                ->withSingleField("slug");
        });
    }

    /**
     * Retrieve a Model for the form and pack all its data as JSON.
     *
     * @param $id
     * @return array
     */
    function find($id): array
    {
        return $this
            ->transform(Pagegroup::findOrFail($id));
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $pagegroup = $id ? Pagegroup::findOrFail($id) : new Pagegroup();

        if(!trim($data["slug"])) {
            $data["slug"] = str_slug($data["title"]);
        }

        $this->save($pagegroup, $data);

        return $pagegroup->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Pagegroup::findOrFail($id)->delete();
    }
}