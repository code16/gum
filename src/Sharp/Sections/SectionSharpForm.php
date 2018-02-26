<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tag;
use Code16\Gum\Sharp\Utils\SharpFormWithStyleKey;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormCheckField;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTagsField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpFormContext;

class SectionSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, WithSharpFormContext, SharpFormWithStyleKey;

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
            SharpFormMarkdownField::make("heading_text")
                ->setLabel("Chapeau")
                ->setToolbar([
                    SharpFormMarkdownField::B, SharpFormMarkdownField::I,
                    SharpFormMarkdownField::SEPARATOR,
                    SharpFormMarkdownField::A,
                ])
        )->addField(
            SharpFormTextField::make("slug")
                ->setLabel("URL")
                ->setHelpMessage("Il s'agit de l'URL (slug) de la section ; laissez ce champ vide pour remplissage automatique à partir du titre. Ne peut contenir que des lettres, des chiffres et des tirets.Attention, si vous modifiez cette valeur, les URLs du site seront modifiées.")
        )->addField(
            SharpFormCheckField::make("has_news", "Propose des actualités")
        )->addField(
            SharpFormTagsField::make("tags", Tag::orderBy("name")->pluck("name", "id")->toArray())
                ->addConditionalDisplay("has_news")
                ->setLabel("Tags concernés")
        );

        if($this->hasStylesDefined()) {
            $this->addField(
                SharpFormSelectField::make("style_key", $this->stylesDefined())
                    ->setLabel("Thème")
                    ->setDisplayAsDropdown()
            );
        }
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

            if($this->hasStylesDefined()) {
                $column->withSingleField("style_key");
            }

        })->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("heading_text")
                ->withSingleField("has_news")
                ->withSingleField("tags");
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
        return $this->transform(Section::with("tags")->findOrFail($id));
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $section = $id
            ? Section::findOrFail($id)
            : new Section(["domain" => SharpGumSessionValue::getDomain()]);

        if(!trim($data["slug"])) {
            $data["slug"] = str_slug($data["title"]);
        }

        $this->save($section, $data);

        return $section->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Section::findOrFail($id)->delete();
    }
}