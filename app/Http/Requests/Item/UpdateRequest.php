<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'favorite' => 'sometimes|boolean',
            'archived' => 'sometimes|boolean',
            'title' => 'sometimes|string',
            'serial_number' => 'sometimes|string',
            'weight' => 'sometimes|string',
            'dimension' => 'sometimes|string',
            'warranty_length' => 'sometimes|string',
            'brand' => 'sometimes|string',
            'ingredients' => 'sometimes|string',
            'nutrition_facts' => 'sometimes|string',
            'size' => 'sometimes|string',
            'description' => 'sometimes|string',
            'last_update' => 'sometimes|string',
            'category_ids' => 'array|required',
            'category_ids.*' => 'string'

        ];
    }
}
