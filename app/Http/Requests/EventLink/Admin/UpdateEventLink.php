<?php

namespace App\Http\Requests\EventLink\Admin;

use App\Http\Requests\Request;

class UpdateEventLink extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_link_type_id' => 'exists:airport_link_types,id',
            'name' => 'nullable|string',
            'url' => 'required|url',
        ];
    }
}
