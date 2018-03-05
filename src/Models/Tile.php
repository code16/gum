<?php

namespace Code16\Gum\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tile extends Model
{
    protected $guarded = [];

    protected $dates = ['created_at', 'updated_at', 'published_at', 'unpublished_at'];

    /** @var bool */
    public $mustRemoveOldUrl;

    /** @var bool */
    public $mustCreateNewUrl;

    public function visual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function tileblock()
    {
        return $this->belongsTo(Tileblock::class);
    }

    public function contentUrl()
    {
        return $this->belongsTo(ContentUrl::class);
    }

    public function linkable()
    {
        return $this->morphTo();
    }

    /**
     * @return null|string
     */
    public function getUriAttribute()
    {
        if(!$this->contentUrl) {
            return null;
        }

        return $this->contentUrl->relative_uri;
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        if($this->isFreeLink()) {
            return $this->free_link_url;
        }

        return $this->uri ? route("page", $this->uri) : "";
    }

    public function setLinkableIdAttribute($value)
    {
        $this->mustRemoveOldUrl =
            isset($this->attributes["linkable_id"])
            && $this->attributes["linkable_id"] != $value;

        $this->mustCreateNewUrl =
            !is_null($value)
            && ($this->attributes["linkable_id"] ?? null) != $value;

        $this->attributes["linkable_id"] = $value;
    }

    /**
     * @return bool
     */
    public function isFreeLink(): bool
    {
        return is_null($this->linkable_id) && strlen($this->free_link_url) > 0;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visibility == "ONLINE";
    }

    /**
     * @param Carbon|null $date
     * @return bool
     */
    public function isPublished(Carbon $date = null)
    {
        $now = $date ?? Carbon::now();

        return (is_null($this->published_at) || $this->published_at <= $now)
            && (is_null($this->unpublished_at) || $this->unpublished_at > $now);
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }
}
