<?php

namespace Code16\Gum\Sharp\Tiles\Forms;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\SharpFormWithStyleKey;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\Transformers\FormUploadModelTransformer;
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
use Code16\Sharp\Form\Layout\FormLayoutFieldset;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpFormContext;

abstract class TileblockSharpForm extends SharpForm
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
            SharpFormTextField::make("layout_label")
                ->setReadOnly()
                ->setLabel("Type de tuiles")
        )->addField(
            SharpFormTextField::make("section_label")
                ->setReadOnly()
                ->setLabel("Section")
        )->addField(
            SharpFormDateField::make("published_at")
                ->setMondayFirst()
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        )->addField(
            SharpFormCheckField::make("has_unpublished_date", "Indiquer une date de fin de publication")
        )->addField(
            SharpFormDateField::make("unpublished_at")
                ->addConditionalDisplay("has_unpublished_date")
                ->setMondayFirst()
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        )->addField(
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
                    ->setHelpMessage("Laisser vide à moins de ne pas vouloir reprendre le thème de la section dans laquelle se trouvent les tuiles")
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
        $this->addColumn(4, function (FormLayoutColumn $column) {
            $column->withSingleField("section_label")
                ->withSingleField("layout_label");

            if($this->hasLayoutVariants()) {
                $column->withSingleField("layout_variant");
            }

            if($this->hasStylesDefined()) {
                $column->withSingleField("style_key");
            }

            $column->withFieldset("Date de mise en ligne", function(FormLayoutFieldset $fieldset) {
                $fieldset->withSingleField("published_at")
                    ->withSingleField("has_unpublished_date")
                    ->withSingleField("unpublished_at");
            });

        })->addColumn(8, function (FormLayoutColumn $column) {
            $column
//                ->withSingleField("news_tag")
                ->withSingleField("tiles", function (FormLayoutColumn $item) {
                    if($this->tileHasField("visual")) {
                        $item->withSingleField("visual");

                        if($this->tileHasField("video")) {
                            $item->withSingleField("visual:is_video");
                        }
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

                    $item->withFields("link_type|4", "free_link_url|8", "section|8", "pagegroup|8", "page|8");

                    $item->withFields("visibility|4", "published_at|4", "unpublished_at|4");
                });
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
            ->setCustomTransformer('tiles[visual]', FormUploadModelTransformer::class)
            ->setCustomTransformer('has_unpublished_date', function($value, $tileblock) {
                return !is_null($tileblock->unpublished_at);
            })
            ->setCustomTransformer('section_label', function($value, $tileblock) {
                return $tileblock->section->title;
            })
            ->setCustomTransformer('layout_label', function($value, $tileblock) {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('tiles[page]', function($value, $tile) {
                return $tile->linkable_type == Page::class ? $tile->linkable_id : null;
            })
            ->setCustomTransformer('tiles[section]', function($value, $tile) {
                return $tile->linkable_type == Section::class ? $tile->linkable_id : null;
            })
            ->setCustomTransformer('tiles[pagegroup]', function($value, $tile) {
                return $tile->linkable_type == Pagegroup::class ? $tile->linkable_id : null;
            })
            ->setCustomTransformer('tiles[link_type]', function($value, $tile) {
                return $tile->linkable_type ?: "free";
            })
            ->transform(Tileblock::with("tiles", "tiles.visual", "section")->findOrFail($id));
    }

    public function create(): array
    {
        return $this
            ->setCustomTransformer('layout_label', function($value, $tileblock) {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('section_label', function($value, $tileblock) {
                return Section::find(SharpGumSessionValue::get("section"))->title;
            })
            ->transform(new Tileblock());
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $tileblock = $id ? Tileblock::findOrFail($id) : new Tileblock();

        $this
            ->ignore(["section_label", "layout_label"])
            ->save($tileblock, $this->cleanUpData($data));

        return $tileblock->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Tileblock::findOrFail($id)->delete();
    }

    /**
     * @return array
     */
    protected function tileFields(): array
    {
        return [
            "visual", "video", "title", "surtitle", "body_text"
        ];
    }

    /**
     * @return array
     */
    protected function layoutVariants(): array
    {
        return [];
    }

    /**
     * @return string
     */
    abstract protected function layoutKey(): string;

    /**
     * @return string
     */
    abstract protected function layoutLabel(): string;

    /**
     * @return bool
     */
    private function hasLayoutVariants(): bool
    {
        return !! sizeof($this->layoutVariants());
    }

    /**
     * @return int
     */
    protected function maxTilesCount(): int
    {
        return 0;
    }

    /**
     * @param $field
     * @return bool
     */
    private function tileHasField($field): bool
    {
        return in_array($field, $this->tileFields());
    }

    /**
     * @param $data
     * @return array
     */
    protected function cleanUpData($data): array
    {
        if(! ($data['has_unpublished_date'] ?? false)) {
            $data['unpublished_at'] = null;
        }
        unset($data["has_unpublished_date"]);

        if(isset($data["tiles"])) {
            foreach ($data["tiles"] as &$dataTile) {
                if ($dataTile["link_type"] != "free") {
                    $dataTile["linkable_type"] = $dataTile["link_type"];
                    $dataTile["linkable_id"] = $dataTile[strtolower(basename(str_replace('\\', '/', $dataTile["link_type"])))];
                } else {
                    $dataTile["linkable_type"] = null;
                    $dataTile["linkable_id"] = null;
                }

                unset($dataTile["link_type"], $dataTile["page"], $dataTile["section"], $dataTile["pagegroup"]);
            }
        }

        if($this->context()->isCreation()) {
            $data["layout"] = $this->layoutKey();
            $data["section_id"] = SharpGumSessionValue::get("section");
        }

        return $data;
    }

    /**
     * @return SharpFormListField
     */
    protected function createTilesListField()
    {
        $listField = SharpFormListField::make("tiles")
//          ->addConditionalDisplay("layout", "!news_banner")
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
            $listField->addItemField(
                SharpFormUploadField::make("visual")
                    ->setLabel("Visuel")
                    ->setFileFilterImages()
                    ->setMaxFileSize(5)
                    ->setStorageDisk("local")
                    ->setCompactThumbnail()
                    ->setStorageBasePath("data/tiles/{id}")
            )->addItemField(
                SharpFormCheckField::make("visual:is_video", "Tuile vidéo")
            );
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

        $listField->addItemField(
            SharpFormSelectField::make("visibility", ["ONLINE" => "Visible", "OFFLINE" => "Masqué"])
                ->setDisplayAsDropdown()
                ->setLabel("Visibilité")
        )->addItemField(
            SharpFormDateField::make("published_at")
                ->addConditionalDisplay("visibility", "ONLINE")
                ->setLabel("Publié le")
                ->setMondayFirst()
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        )->addItemField(
            SharpFormDateField::make("unpublished_at")
                ->addConditionalDisplay("visibility", "ONLINE")
                ->setLabel("Jusqu'au")
                ->setMondayFirst()
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        )->addItemField(
            SharpFormSelectField::make("link_type", [
                "free" => "Lien libre",
                Page::class => "Page",
                Pagegroup::class => "Groupe de pages",
                Section::class => "Section",
            ])
                ->setDisplayAsDropdown()
                ->setLabel("Lien")
        )->addItemField(
            SharpFormAutocompleteField::make("section", "local")
                ->setLocalSearchKeys(["label"])
                ->addConditionalDisplay("link_type", Section::class)
                ->setResultItemInlineTemplate("{{label}}")
                ->setListItemInlineTemplate("{{label}}")
                ->setLocalValues(Section::domain(SharpGumSessionValue::getDomain())->orderBy("title")->get()->pluck("title", "id")->all())
                ->setLabel("Section")
        )->addItemField(
            SharpFormAutocompleteField::make("page", "local")
                ->setLocalSearchKeys(["label"])
                ->addConditionalDisplay("link_type", Page::class)
                ->setResultItemInlineTemplate("{{label}}")
                ->setListItemInlineTemplate("{{label}}")
                ->setLocalValues(Page::whereNull("pagegroup_id")->orderBy("title")->get()->pluck("title", "id")->all())
                ->setLabel("Page")
        )->addItemField(
            SharpFormAutocompleteField::make("pagegroup", "local")
                ->setLocalSearchKeys(["label"])
                ->addConditionalDisplay("link_type", Pagegroup::class)
                ->setResultItemInlineTemplate("{{label}}")
                ->setListItemInlineTemplate("{{label}}")
                ->setLocalValues(Pagegroup::orderBy("title")->get()->pluck("title", "id")->all())
                ->setLabel("Groupe de pages")
        )->addItemField(
            SharpFormTextField::make("free_link_url")
                ->addConditionalDisplay("link_type", "free")
                ->setLabel("Lien")
        );

        return $listField;
    }
}