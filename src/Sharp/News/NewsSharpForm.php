<?php

namespace Code16\Gum\Sharp\News;

use Carbon\Carbon;
use Code16\Gum\Models\News;
use Code16\Gum\Models\Tag;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormDateField;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTagsField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\Layout\FormLayoutFieldset;
use Code16\Sharp\Form\SharpForm;

class NewsSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    function buildFormFields(): void
    {
        $this->addField(
            SharpFormDateField::make("published_at")
                ->setLabel("Date de publication")
                ->setMondayFirst()
                ->setStepTime(15)
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        );

        if($this->hasField("visual")) {
            $this
                ->addField(
                    SharpFormUploadField::make("visual")
                        ->setFileFilterImages()
                        ->setMaxFileSize(5)
                        ->setStorageDisk("local")
                        ->setStorageBasePath("data/news/{id}")
                )
                ->addField(
                    SharpFormTextField::make("visual:legend")
                        ->setPlaceholder("Légende")
                );

            foreach($this->additionalVisualFields() as $field) {
                $this->addField($field);
            }
        }

        if($this->hasField("surtitle")) {
            $this->addField(
                SharpFormTextareaField::make("surtitle")
                    ->setLabel("Sur-titre")
            );
        }

        if($this->hasField("title")) {
            $this->addField(
                SharpFormTextareaField::make("title")
                    ->setLabel("Titre")
            );
        }

        if($this->hasField("heading_text")) {
            $this->addField(
                SharpFormMarkdownField::make("heading_text")
                    ->setLabel("Chapeau")
                    ->setToolbar([
                        SharpFormMarkdownField::B, SharpFormMarkdownField::I,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::A,
                    ])
                    ->setHeight(200)
            );
        }

        if($this->hasField("body_text")) {
            $this->addField(
                SharpFormMarkdownField::make("body_text")
                    ->setHeight(600)
                    ->setToolbar([
                        SharpFormMarkdownField::H1,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::B, SharpFormMarkdownField::I,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::UL, SharpFormMarkdownField::A
                    ])
                    ->setLabel("Texte")
            );
        }

        if($this->hasField("category")) {
            if($this->categoryLabels()) {
                $this->addField(
                    SharpFormSelectField::make("category_label", $this->categoryLabels())
                        ->setLabel("Variante")
                        ->setDisplayAsDropdown()
                        ->setClearable()
                );

            } else {
                $this->addField(
                    SharpFormTextField::make("category_label")
                        ->setLabel("Variante")
                );
            }
        }

        if($this->hasField("importance")) {
            $this->addField(
                SharpFormSelectField::make("importance", $this->importanceLevels())
                    ->setLabel("Niveau d'importance")
                    ->setDisplayAsDropdown()
            );
        }

        if($this->hasField("tags")) {
            $this->addField(
                SharpFormTagsField::make("tags", Tag::orderBy("name")->pluck("name", "id")->toArray())
                    ->setCreatable()
                    ->setCreateAttribute("name")
                    ->setCreateText("Nouveau :")
                    ->setLabel("Tags")
            );
        }

        if($this->hasField("attachments")) {
            $attachments = SharpFormListField::make("attachments")
                ->setLabel("Pièces jointes")
                ->setAddable()->setAddText("Ajouter")
                ->setRemovable()
                ->setSortable()
                ->setOrderAttribute("order")
                ->addItemField(
                    SharpFormUploadField::make("file")
                        ->setMaxFileSize(12)
                        ->setStorageDisk("local")
                        ->setStorageBasePath("data/news/{id}/attachments")
                )
                ->addItemField(
                    SharpFormTextField::make("legend")
                        ->setPlaceholder("Légende")
                );

            foreach($this->additionalAttachmentItemFields() as $field) {
                $attachments->addItemField($field);
            }

            $this->addField($attachments);
        }
    }

    function buildFormLayout(): void
    {
        $this
            ->addColumn(6, function (FormLayoutColumn $column) {
                $column->withSingleField("published_at");
    
                if($this->hasField("tags")) {
                    $column->withSingleField("tags");
                }
    
                if($this->hasField("category")) {
                    $column->withSingleField("category_label");
                }
    
                if($this->hasField("importance")) {
                    $column->withSingleField("importance");
                }
    
                if($this->hasField("title")) {
                    $column->withSingleField("title");
                }
    
                if($this->hasField("surtitle")) {
                    $column->withSingleField("surtitle");
                }
    
                if($this->hasField("visual")) {
                    $column->withFieldset("Visuel", function (FormLayoutFieldset $fieldset) {
                        $fieldset->withSingleField("visual")
                            ->withSingleField("visual:legend");
    
                        foreach ($this->additionalVisualFields() as $key => $field) {
                            $fieldset->withSingleField($key);
                        }
                    });
                }
    
                if($this->hasField("attachments")) {
                    $column->withSingleField("attachments", function (FormLayoutColumn $item) {
                        $item->withSingleField("file")
                            ->withSingleField("legend");
    
                        foreach($this->additionalAttachmentItemFields() as $key => $field) {
                            $item->withSingleField($key);
                        }
                    });
                }
    
            })
            ->addColumn(6, function (FormLayoutColumn $column) {
                if($this->hasField("heading_text")) {
                    $column->withSingleField("heading_text");
                }
    
                if($this->hasField("body_text")) {
                    $column->withSingleField("body_text");
                }
            });
    }

    function find($id): array
    {
        return $this
            ->setCustomTransformer('visual', SharpUploadModelFormAttributeTransformer::class)
            ->setCustomTransformer('attachments', SharpUploadModelFormAttributeTransformer::class)
            ->transform(News::with("visual", "attachments", "tags")->findOrFail($id));
    }

    function update($id, array $data)
    {
        $news = $id ? News::findOrFail($id) : new News();

        $this->save($news, $data);

        $this->deleteOrphanTags();

        return $news->id;
    }

    function create(): array
    {
        // Date rounded to the next quarter hour (15, 30, 45)
        $date = Carbon::createFromTimestamp(ceil(time() / (15 * 60)) * (15 * 60));

        return $this->transform(new News([
            "published_at" => $date,
            "importance" => 3
        ]));
    }

    function delete($id): void
    {
        News::findOrFail($id)->delete();
    }

    protected function newsFields(): array
    {
        return [
            "visual", "legend", "title", "surtitle", "tags",
            "body_text", "heading_text", "attachments"
        ];
    }

    protected function additionalVisualFields(): array
    {
        return [];
    }

    protected function additionalAttachmentItemFields(): array
    {
        return [];
    }

    protected function categoryLabels(): array
    {
        return [];
    }

    protected function importanceLevels(): array
    {
        return [
            "1" => "Actualité majeure",
            "2" => "Actualité importante",
            "3" => "Actualité normale",
            "4" => "Actualité peu importante",
            "5" => "Actualité mineure",
        ];
    }

    private function hasField($field): bool
    {
        return in_array($field, $this->newsFields());
    }

    protected function deleteOrphanTags(): void
    {
        Tag::whereNotIn("id", function($query) {
            return $query->select("tag_id")
                ->from("taggables");
        })->delete();
    }
}