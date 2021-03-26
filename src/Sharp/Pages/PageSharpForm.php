<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tag;
use Code16\Gum\Sharp\Utils\SharpFormWithStyleKey;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormCheckField;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTagsField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Illuminate\Support\Str;

class PageSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, SharpFormWithStyleKey;
    
    protected bool $allowNews = false;

    function buildFormFields(): void
    {
        $this
            ->addField(
                SharpFormTextareaField::make("title")
                    ->setLabel("Titre")
                    ->setRowCount(2)
            )
            ->addField(
                SharpFormTextField::make("short_title")
                    ->setLabel("Titre menu")
                    ->setHelpMessage("Utilisé dans les menus / fils d'ariane. Facultatif.")
            )
            ->addField(
                $this->bodyField()
            )
            ->addField(
                $this->headingField()
            )
            ->addField(
                $this->visualField()
            )
            ->addField(
                SharpFormTextField::make("visual:legend")
                    ->setPlaceholder("Légende")
            )
            ->addField(
                SharpFormTextField::make("slug")
                    ->setLabel("URL")
                    ->setHelpMessage("Il s'agit de l'URL (slug) de la page ; laissez ce champ vide pour remplissage automatique à partir du titre. Ne peut contenir que des lettres, des chiffres et des tirets. Attention, si vous modifiez cette valeur, les URLs du site seront modifiées.")
            );
        
        if($this->allowNews) {
            $this
                ->addField(
                    SharpFormCheckField::make("has_news", "Propose des actualités")
                )
                ->addField(
                    SharpFormTagsField::make("tags", Tag::orderBy("name")->pluck("name", "id")->toArray())
                        ->addConditionalDisplay("has_news")
                        ->setLabel("Tags concernés")
                );
        }

        if($this->hasStylesDefined()) {
            $this->addField(
                SharpFormSelectField::make("style_key", $this->stylesDefined())
                    ->setLabel("Thème")
                    ->setClearable()
                    ->setDisplayAsDropdown()
            );
        }
    }

    function buildFormLayout(): void
    {
        $isPagegroup = currentSharpRequest()->isUpdate()
            && Page::find(currentSharpRequest()->instanceId())->isPageGroup();
        
        $this
            ->addColumn(6, function (FormLayoutColumn $column) use ($isPagegroup) {
                $column
                    ->withSingleField("title")
                    ->withSingleField("short_title")
                    ->withSingleField("slug");

                if($this->hasStylesDefined()) {
                    $column->withSingleField("style_key");
                }
                
                if(!$isPagegroup) {
                    $column->withFieldset("Visuel", function ($fieldset) {
                        $fieldset->withSingleField("visual")
                            ->withSingleField("visual:legend");
                    });
                }
            });
        
        if(!$isPagegroup) {
            $this->addColumn(6, function (FormLayoutColumn $column) {
                $column->withSingleField("heading_text")
                    ->withSingleField("body_text");

                if ($this->allowNews) {
                    $column->withSingleField("has_news")
                        ->withSingleField("tags");
                }
            });
        }
    }

    function find($id): array
    {
        return $this
            ->setCustomTransformer('visual', SharpUploadModelFormAttributeTransformer::class)
            ->transform(Page::with("tags")->findOrFail($id));
    }

    function update($id, array $data)
    {
        if($id) {
            $page = Page::findOrFail($id);
        } else {
            $pagegroupId = null;
            if($previousPage = currentSharpRequest()->getPreviousShowFromBreadcrumbItems()) {
                $previousPage = Page::findOrFail($previousPage->instanceId());
                if($previousPage->isPagegroup()) {
                    $pagegroupId = $previousPage->id;
                }
            }
            
            $page = new Page([
                "pagegroup_id" => $pagegroupId
            ]);
        }

        if(array_key_exists("slug", $data) && !trim($data["slug"])) {
            $data["slug"] = Str::slug($data["title"]);
        }

        $this->save($page, $data);

        return $page->id;
    }

    function delete($id): void
    {
        Page::findOrFail($id)->delete();
    }

    protected function bodyField(): SharpFormMarkdownField
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

    protected function headingField(): SharpFormMarkdownField
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

    protected function visualField(): SharpFormUploadField
    {
        return SharpFormUploadField::make("visual")
            ->setFileFilterImages()
            ->setMaxFileSize(5)
            ->setStorageDisk("local")
            ->setStorageBasePath("data/pages/{id}");
    }
}