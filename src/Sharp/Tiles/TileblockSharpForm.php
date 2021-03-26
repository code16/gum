<?php

namespace Code16\Gum\Sharp\Tiles;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\SharpFormWithStyleKey;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormAutocompleteField;
use Code16\Sharp\Form\Fields\SharpFormCheckField;
use Code16\Sharp\Form\Fields\SharpFormDateField;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Illuminate\Support\Str;

abstract class TileblockSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, SharpFormWithStyleKey;

    function buildFormFields(): void
    {
        $this
            ->addField(
                SharpFormTextField::make("layout_label")
                    ->setReadOnly()
                    ->setLabel("Type de tuiles")
            )
            ->addField(
                SharpFormTextField::make("page_label")
                    ->setReadOnly()
                    ->setLabel("Page")
            )
            ->addField(
                $this->createTilesListField()
            );
    
        if($this->hasLayoutVariants()) {
            $this->addField(
                SharpFormSelectField::make("layout_variant", $this->layoutVariants())
                    ->setDisplayAsDropdown()
                    ->setClearable()
                    ->setLabel("Variante")
            );
        }

        if($this->hasStylesDefined()) {
            $this->addField(
                SharpFormSelectField::make("style_key", $this->stylesDefined())
                    ->setLabel("Thème")
                    ->setClearable()
                    ->setDisplayAsDropdown()
                    ->setHelpMessage("Laisser vide à moins de ne pas vouloir reprendre le thème de la page dans laquelle se trouvent les tuiles")
            );
        }
    }

    function buildFormLayout(): void
    {
        $this
            ->addColumn(4, function (FormLayoutColumn $column) {
                $column->withSingleField("page_label")
                    ->withSingleField("layout_label");
    
                if($this->hasLayoutVariants()) {
                    $column->withSingleField("layout_variant");
                }
    
                if($this->hasStylesDefined()) {
                    $column->withSingleField("style_key");
                }

            })
            ->addColumn(8, function (FormLayoutColumn $column) {
                $column
                    ->withSingleField("tiles", function (FormLayoutColumn $item) {
                        if($this->tileHasField("visual")) {
                            $item->withSingleField("visual");
    
                            if($this->tileHasField("video")) {
                                $item->withSingleField("visual:is_video");
                            }
                        }
    
                        foreach ($this->additionalVisualFields() as $key => $field) {
                            $item->withSingleField($key);
                        }
    
                        if($this->tileHasField("surtitle")) {
                            $item->withSingleField("surtitle");
                        }
    
                        if($this->tileHasField("title") && $this->tileHasField("body_text")) {
                            $item->withFields("title|6", "body_text|6");
                        } elseif($this->tileHasField("title")) {
                            $item->withSingleField("title");
                        } elseif($this->tileHasField("body_text")) {
                            $item->withSingleField("body_text");
                        }
    
                        if($this->tileHasLink()) {
                            $item->withFields("link_type|4", "free_link_url|8", "page_id|8", "orphan_page_id|8", "new_page_title|8");
                        }
    
                        $item->withFields("visibility|4", "published_at|4", "unpublished_at|4");
                    });
            });
    }

    function find($id): array
    {
        return $this
            ->setCustomTransformer('tiles[visual]', SharpUploadModelFormAttributeTransformer::class)
            ->setCustomTransformer('page_label', function($value, Tileblock $tileblock) {
                return $tileblock->page->title;
            })
            ->setCustomTransformer('layout_label', function() {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('tiles[link_type]', function($value, Tile $tile) {
                return $tile->page_id ? "page": "free";
            })
            ->transform(
                Tileblock::with("tiles", "tiles.visual", "page")->findOrFail($id)
            );
    }

    public function create(): array
    {
        return $this
            ->setCustomTransformer('layout_label', function($value, $tileblock) {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('page_label', function($value, $tileblock) {
                return Page::find(currentSharpRequest()->getPreviousShowFromBreadcrumbItems()->instanceId())->title;
            })
            ->transform(new Tileblock());
    }

    function update($id, array $data)
    {
        $tileblock = $id 
            ? Tileblock::findOrFail($id) 
            : new Tileblock([
                "layout" => $this->layoutKey(),
                "page_id" => currentSharpRequest()->getPreviousShowFromBreadcrumbItems()->instanceId()
            ]);
        
        unset($data["page_label"], $data["layout_label"]);

        if($this->tileHasLink()) {
            $data["tiles"] = collect($data["tiles"] ?? [])
                ->map(function($dataTile) use($tileblock) {
                    if ($dataTile["link_type"] === "free") {
                        $dataTile["page_id"] = null;
                        
                    } else {
                        $dataTile["free_link_url"] = null;
                        
                        if ($dataTile["link_type"] === "orphan") {
                            $dataTile["page_id"] = $dataTile["orphan_page_id"];

                        } elseif (in_array($dataTile["link_type"], ["new", "new_pagegroup"])) {
                            $dataTile["page_id"] = Page::create([
                                "title" => $dataTile["new_page_title"],
                                "slug" => Str::slug($dataTile["new_page_title"]),
                                "is_pagegroup" => $dataTile["link_type"] == "new_pagegroup"
                            ])->id;
                        }
                    }
                    
                    unset($dataTile["link_type"], $dataTile["orphan_page_id"], $dataTile["new_page_title"]);
                    
                    return $dataTile;
                })
                ->toArray();
        }
        
        $this->save($tileblock, $data);

        return $tileblock->id;
    }

    function delete($id): void
    {
        Tileblock::findOrFail($id)->delete();
    }

    protected function tileFields(): array
    {
        return [
            "visual", "video", "title", "surtitle", "body_text"
        ];
    }

    protected function layoutVariants(): array
    {
        return [];
    }

    abstract protected function layoutKey(): string;

    abstract protected function layoutLabel(): string;

    private function hasLayoutVariants(): bool
    {
        return !! sizeof($this->layoutVariants());
    }

    protected function maxTilesCount(): int
    {
        return 0;
    }

    protected function tileHasLink(): bool
    {
        return true;
    }

    protected function additionalVisualFields(): array
    {
        return [];
    }

    private function tileHasField($field): bool
    {
        return in_array($field, $this->tileFields());
    }

    protected function createTilesListField(): SharpFormListField
    {
        $listField = SharpFormListField::make("tiles")
            ->setLabel("Tuiles")
            ->setAddable()
            ->setAddText("Ajouter une tuile")
            ->setRemovable()
            ->setSortable()
            ->setOrderAttribute("order");

        if($this->maxTilesCount()) {
            $listField->setMaxItemCount($this->maxTilesCount());
        }

        if($this->tileHasField("visual")) {
            $listField
                ->addItemField(
                    SharpFormUploadField::make("visual")
                        ->setLabel("Visuel")
                        ->setFileFilterImages()
                        ->setMaxFileSize(5)
                        ->setStorageDisk("local")
                        ->setCompactThumbnail()
                        ->setStorageBasePath("data/tiles/{id}")
                )
                ->addItemField(
                    SharpFormCheckField::make("visual:is_video", "Tuile vidéo")
                );

            foreach($this->additionalVisualFields() as $field) {
                $listField->addItemField($field);
            }
        }

        if($this->tileHasField("title")) {
            $listField->addItemField(
                SharpFormTextareaField::make("title")
                    ->setRowCount(3)
                    ->setLabel("Titre")
            );
        }

        if($this->tileHasField("surtitle")) {
            $listField->addItemField(
                SharpFormTextField::make("surtitle")
                    ->setLabel("Sur-titre")
            );
        }

        if($this->tileHasField("body_text")) {
            $listField->addItemField(
                SharpFormTextareaField::make("body_text")
                    ->setRowCount(3)
                    ->setLabel("Texte")
            );
        }

        $listField
            ->addItemField(
                SharpFormSelectField::make("visibility", ["ONLINE" => "Visible", "OFFLINE" => "Masqué"])
                    ->setDisplayAsDropdown()
                    ->setLabel("Visibilité")
            )
            ->addItemField(
                SharpFormDateField::make("published_at")
                    ->addConditionalDisplay("visibility", "ONLINE")
                    ->setLabel("Publié le")
                    ->setMondayFirst()
                    ->setHasTime(true)
                    ->setDisplayFormat("DD/MM/YYYY HH:mm")
            )
            ->addItemField(
                SharpFormDateField::make("unpublished_at")
                    ->addConditionalDisplay("visibility", "ONLINE")
                    ->setLabel("Jusqu'au")
                    ->setMondayFirst()
                    ->setHasTime(true)
                    ->setDisplayFormat("DD/MM/YYYY HH:mm")
            );

        if($this->tileHasLink()) {
            $listField
                ->addItemField(
                    SharpFormSelectField::make("link_type", [
                        "free" => "Lien libre",
                        "page" => "Page",
                        "orphan" => "Page orpheline",
                        "new" => "Nouvelle page",
                        "new_pagegroup" => "Nouveau groupe",
                    ])
                        ->setDisplayAsDropdown()
                        ->setLabel("Lien")
                )
                ->addItemField(
                    SharpFormAutocompleteField::make("page_id", "local")
                        ->setLabel("Page")
                        ->setLocalValues(Page::notHome()->notOrphan()->orderBy("title")->get())
                        ->setLocalSearchKeys(["title", "slug"])
                        ->setResultItemInlineTemplate("{{title}} <small class='text-muted'>{{slug}}</small>")
                        ->setListItemInlineTemplate("{{title}}<div class='text-muted'><small>{{slug}}</small></div>")
                        ->addConditionalDisplay("link_type", "page")
                )
                ->addItemField(
                    SharpFormAutocompleteField::make("orphan_page_id", "local")
                        ->setLabel("Page")
                        ->setLocalValues(Page::notHome()->orphan()->orderBy("title")->get())
                        ->setLocalSearchKeys(["title", "slug"])
                        ->setResultItemInlineTemplate("{{title}} <small class='text-muted'>{{slug}}</small>")
                        ->setListItemInlineTemplate("{{title}}<div class='text-muted'><small>{{slug}}</small></div>")
                        ->addConditionalDisplay("link_type", "orphan")
                )
                ->addItemField(
                    SharpFormTextField::make("new_page_title")
                        ->addConditionalDisplay("link_type", ["new", "new_pagegroup"])
                        ->setLabel("Titre de la page")
                )
                ->addItemField(
                    SharpFormTextField::make("free_link_url")
                        ->addConditionalDisplay("link_type", "free")
                        ->setLabel("Lien")
                );
        }

        return $listField;
    }
}