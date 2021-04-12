<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Sidepanel extends Model
{
    protected $guarded = [];
    protected $casts = [
        'custom_properties' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function visual(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function downloadableFile(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "downloadableFile");
    }

    public function getDefaultAttributesFor($attribute): array
    {
        return in_array($attribute, ["visual", "downloadableFile"])
            ? ["model_key" => $attribute]
            : [];
    }

    public function getAttribute($key)
    {
        if(!$this->isRealAttribute($key)) {
            return $this->getAttribute("custom_properties")[$key] ?? null;
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if(!$this->isRealAttribute($key)) {
            return $this->updateCustomProperty($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    private function updateCustomProperty(string $key, $value): self
    {
        $properties = $this->getAttribute("custom_properties");
        $properties[$key] = $value;
        $this->setAttribute("custom_properties", $properties);

        return $this;
    }

    private function isRealAttribute(string $name): bool
    {
        return in_array($name, [
            "id", "layout", "link", "body_text", "order", "custom_properties",
            "page_id", "page", "visual", "downloadableFile",
            "created_at", "updated_at"
        ]);
    }
}
