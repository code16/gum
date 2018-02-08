<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;

class Sidepanel extends Model
{
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'custom_properties' => 'array',
    ];

    public function container()
    {
        return $this->morphTo();
    }

    public function relatedContent()
    {
        return $this->morphTo('related_content');
    }

    public function visual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function downloadableFile()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "downloadableFile");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual", "downloadableFile"])
            ? ["model_key" => $attribute]
            : [];
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if(!$this->isRealAttribute($key)) {
            return $this->getAttribute("custom_properties")[$key] ?? null;
        }

        return parent::getAttribute($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Model
     */
    public function setAttribute($key, $value)
    {
        if(!$this->isRealAttribute($key)) {
            return $this->updateCustomProperty($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    private function updateCustomProperty($key, $value)
    {
        $properties = $this->getAttribute("custom_properties");
        $properties[$key] = $value;
        $this->setAttribute("custom_properties", $properties);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isRealAttribute(string $name)
    {
        return in_array($name, [
            "id", "layout", "link", "body_text", "order", "custom_properties",
            "container_id", "container_type", "related_content_id", "related_content_type",
            "container", "relatedContent", "visual", "downloadableFile",
            "created_at", "updated_at"
        ]);
    }
}
