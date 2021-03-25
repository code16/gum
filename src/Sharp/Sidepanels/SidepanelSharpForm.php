<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;

abstract class SidepanelSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    function buildFormFields(): void
    {
        $this
            ->addField(
                SharpFormTextField::make("layout_label")
                    ->setReadOnly()
                    ->setLabel("Type de panneau")
            )
            ->addField(
                SharpFormTextField::make("page_label")
                    ->setReadOnly()
                    ->setLabel("Page")
            );

        if($this->hasField("visual")) {
            $this->addField(
                SharpFormUploadField::make("visual")
                    ->setLabel("Visuel")
                    ->setFileFilterImages()
                    ->setMaxFileSize(5)
                    ->setStorageDisk("local")
                    ->setStorageBasePath("data/sidepanels/{id}")
            );

            if($this->hasField("visual_legend")) {
                $this->addField(
                    SharpFormTextareaField::make("visual:legend")
                        ->setRowCount(3)
                        ->setLabel("Légende")
                );
            }

            if($this->hasField("video")) {
                $this->addField(
                    SharpFormTextField::make("visual:video_url")
                        ->setLabel("URL de la vidéo")
                );
            }
        }

        if($this->hasField("download")) {
            $this
                ->addField(
                    SharpFormUploadField::make("downloadableFile")
                        ->setLabel("Fichier")
                        ->setFileFilter($this->getDownloadableFileFilter())
                        ->setMaxFileSize(12)
                        ->setStorageDisk("local")
                        ->setStorageBasePath("data/sidepanels/{id}")
                )
                ->addField(
                    SharpFormTextareaField::make("downloadableFile:title")
                        ->setRowCount(3)
                        ->setLabel("Titre")
                );
        }

        if($this->hasField("body_text")) {
            $this->addField(
                SharpFormMarkdownField::make("body_text")
                    ->setLabel("Texte")
                    ->setToolbar([
                        SharpFormMarkdownField::H1,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::B, SharpFormMarkdownField::I, SharpFormMarkdownField::A,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::UL,
                    ])
            );
        }

        if($this->hasField("link")) {
            $this->addField(
                SharpFormTextField::make("link")
                    ->setLabel("Lien")
            );
        }

        foreach($this->additionalFields() as $field) {
            $this->addField($field);
        }
    }
    
    function buildFormLayout(): void
    {
        $this
            ->addColumn(6, function (FormLayoutColumn $column) {
                $column->withSingleField("page_label")
                    ->withSingleField("layout_label");
    
                if($this->hasField("body_text")) {
                    $column->withSingleField("body_text");
                }
    
                if($this->hasField("link")) {
                    $column->withSingleField("link");
                }
    
            })
            ->addColumn(6, function (FormLayoutColumn $column) {
                if($this->hasField("visual")) {
                    $column->withSingleField("visual");
    
                    if($this->hasField("video")) {
                        $column->withSingleField("visual:video_url");
                    }
    
                    if($this->hasField("visual_legend")) {
                        $column->withSingleField("visual:legend");
                    }
                }
    
                if($this->hasField("download")) {
                    $column->withSingleField("downloadableFile")
                        ->withSingleField("downloadableFile:title");
                }
    
                foreach ($this->additionalFields() as $key => $field) {
                    $column->withSingleField($key);
                }
            });
    }

    function find($id): array
    {
        $this
            ->setCustomTransformer('visual', SharpUploadModelFormAttributeTransformer::class)
            ->setCustomTransformer('downloadableFile', SharpUploadModelFormAttributeTransformer::class)
            ->setCustomTransformer('page_label', function($value, SidePanel $sidepanel) {
                return $sidepanel->page->title;
            })
            ->setCustomTransformer('layout_label', function() {
                return $this->layoutLabel();
            });

        foreach($this->additionalTransformers() as $attribute => $transformer) {
            $this->setCustomTransformer($attribute, $transformer);
        }

        return $this->transform(Sidepanel::with("visual", "downloadableFile", "page")->findOrFail($id));
    }

    public function create(): array
    {
        return $this
            ->setCustomTransformer('layout_label', function() {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('page_label', function() {
                return Page::findOrFail(
                    currentSharpRequest()->getPreviousShowFromBreadcrumbItems()->instanceId()
                )->title;
            })
            ->transform(new Sidepanel());
    }

    function update($id, array $data)
    {
        $sidepanel = $id 
            ? Sidepanel::findOrFail($id) 
            : new Sidepanel([
                "layout" => $this->layoutKey(),
                "page_id" => currentSharpRequest()->getPreviousShowFromBreadcrumbItems()->instanceId()
            ]);

        $this->ignore(["page_label", "layout_label"])
            ->save($sidepanel, $data);

        return $sidepanel->id;
    }

    function delete($id): void
    {
        Sidepanel::findOrFail($id)->delete();
    }

    protected function sidepanelFields(): array
    {
        return [
            "visual", "download", "body_text", "link"
        ];
    }

    abstract protected function layoutKey(): string;

    abstract protected function layoutLabel(): string;

    protected function additionalFields(): array
    {
        return [];
    }

    protected function additionalTransformers(): array
    {
        return [];
    }

    private function hasField($field): bool
    {
        return in_array($field, $this->sidepanelFields());
    }

    protected function getDownloadableFileFilter(): array
    {
        return ["pdf","zip"];
    }
}