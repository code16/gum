<?php

namespace Code16\Gum\Sharp\Utils;

use Illuminate\Contracts\Validation\Rule;

class FreeLinkRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !trim($value)
            || preg_match('/^(\/([\&\=\#\?\da-z\.-]+))*$/', $value)
            || preg_match('/^(https?:\/\/)([\da-z\.-]+)\.([a-z\.]{2,6})(\/[\da-z\.-]*)*\/?([\&\=\#\?\da-z\.-]*)*\/?$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Doit être une adresse complète (http://...) ou relative au site (débutant pas un /).';
    }
}
