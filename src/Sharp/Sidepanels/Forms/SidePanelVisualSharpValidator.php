<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

use Illuminate\Foundation\Http\FormRequest;

class SidePanelVisualSharpValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "visual" => "required",
        ];
    }
}