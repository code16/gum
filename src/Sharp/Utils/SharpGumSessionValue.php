<?php

namespace Code16\Gum\Sharp\Utils;

class SharpGumSessionValue
{
    protected static $values = [];

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public static function get(string $key, $default = null)
    {
        $value = self::$values[$key] ?? session("sharpgum_$key");

        if(!$value && $default) {
            $value = $default;
            self::set($key, $value);
        }

        return $value;
    }

    /**
     * @param string $key
     * @param $value
     */
    public static function set(string $key, $value)
    {
        self::$values[$key] = $value;
        session(["sharpgum_$key" => $value]);
    }

    /**
     * @param $value
     */
    public static function setDomain($value)
    {
        self::set("domain", $value);
    }

    /**
     * @return string|null
     */
    public static function getDomain()
    {
        $domains = collect(config("gum.domains"));

        if(!$domains) {
            return null;
        }

        $domain = self::get("domain");

        if(!$domain || !$domains->keys()->search($domain)) {
            $domain = $domains->keys()->first();
            self::setDomain($domain);
        }

        return $domain;
    }
}