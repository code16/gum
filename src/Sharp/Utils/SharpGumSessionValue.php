<?php

namespace Code16\Gum\Sharp\Utils;

class SharpGumSessionValue
{
    protected static array $values = [];

    public static function get(string $key, string $default = null): ?string
    {
        $value = self::$values[$key] ?? session("sharpgum_$key");

        if(!$value && $default) {
            $value = $default;
            self::set($key, $value);
        }

        return $value;
    }

    public static function set(string $key, ?string $value): void
    {
        self::$values[$key] = $value;
        session(["sharpgum_$key" => $value]);
    }

    public static function setDomain(?string $value): void
    {
        if(gum_domain_allowed_to_user($value)) {
            self::set("domain", $value);
        }
    }

    public static function getDomain(): ?string
    {
        $domains = collect(config("gum.domains"))
            ->filter(function($label, $domain) {
                return gum_domain_allowed_to_user($domain);
            });

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