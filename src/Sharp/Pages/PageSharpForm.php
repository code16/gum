<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Tag;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormAutocompleteField;
use Code16\Sharp\Form\Fields\SharpFormCheckField;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormTagsField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpContext;
use Illuminate\Support\Str;

class PageSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, WithSharpContext;
    
    protected $allowNews = false;

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
            SharpFormAutocompleteField::make("pagegroup_id", "local")
                ->setLabel("Groupe de pages")
                ->setLocalSearchKeys(["label"])
                ->setListItemInlineTemplate("{{label}}")
                ->setResultItemInlineTemplate("{{label}}")
                ->setLocalValues(Pagegroup::all()->pluck("title", "id")->all())
        )->addField(
            $this->bodyField()
        )->addField(
            $this->headingField()
        )->addField(
            $this->visualField()
        )->addField(
            SharpFormTextField::make("visual:legend")
                ->setPlaceholder("Légende")
        )->addField(
            SharpFormTextField::make("slug")
                ->setLabel("URL")
                ->setHelpMessage("Il s'agit de l'URL (slug) de la page ; laissez ce champ vide pour remplissage automatique à partir du titre. Ne peut contenir que des lettres, des chiffres et des tirets. Attention, si vous modifiez cette valeur, les URLs du site seront modifiées.")
        );
        
        if($this->allowNews) {
            $this->addField(
                SharpFormCheckField::make("has_news", "Propose des actualités")
            )->addField(
                SharpFormTagsField::make("tags", Tag::orderBy("name")->pluck("name", "id")->toArray())
                    ->addConditionalDisplay("has_news")
                    ->setLabel("Tags concernés")
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
                ->withSingleField("slug")
                ->withSingleField("pagegroup_id")
                ->withFieldset("Visuel", function($fieldset) {
                    $fieldset->withSingleField("visual")
                        ->withSingleField("visual:legend");
                });

        })->addColumn(6, function (FormLayoutColumn $column) {
            $column->withSingleField("heading_text")
                ->withSingleField("body_text");

            if($this->allowNews) {
                $column->withSingleField("has_news")
                    ->withSingleField("tags");
            }
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
            ->setCustomTransformer('visual', SharpUploadModelFormAttributeTransformer::class)
            ->transform(Page::with("pagegroup", "tags")->findOrFail($id));
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $page = $id ? Page::findOrFail($id) : new Page();

        if(array_key_exists("slug", $data) && !trim($data["slug"])) {
            $data["slug"] = Str::slug($data["title"]);
        }

        $this->save($page, $data);

        return $page->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Page::findOrFail($id)->delete();
    }

    /**
     * @return SharpFormMarkdownField
     */
    protected function bodyField()
    {
        return SharpFormMarkdownField::make("body_text")
            ->setLabel("Texte")
            ->setHeight(600)
            ->setToolbar([
                SharpFormMarkdownField::H1,
                SharpFormMarkdownField::SEPARATOR,
                SharpFormMarkdownField::B, SharpFormMarkdownField::I,
                SharpFormMarkdownField::SEPARATOR,
                SharpFormMarkdownField::UL, SharpFormMarkdownField::A,
            ]);
    }

    /**
     * @return SharpFormMarkdownField
     */
    protected function headingField()
    {
        return SharpFormMarkdownField::make("heading_text")
            ->setLabel("Chapeau")
            ->setHeight(250)
            ->setToolbar([
                SharpFormMarkdownField::B, SharpFormMarkdownField::I,
                SharpFormMarkdownField::SEPARATOR,
                SharpFormMarkdownField::A,
            ]);
    }

    /**
     * @return SharpFormUploadField
     */
    protected function visualField()
    {
        return SharpFormUploadField::make("visual")
            ->setFileFilterImages()
            ->setMaxFileSize(5)
            ->setStorageDisk("local")
            ->setStorageBasePath("data/pages/{id}");
    }
}