<?php

namespace App\Http\Requests\EventLink\Admin;

use App\Http\Requests\Request;

class UpdateEventLink extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'event_link_type_id' => ['required', 'exists:airport_link_types,id'],
            'name' => ['nullable', 'string'],
            'url' => ['required', 'url'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'event_link_type_id' => __('Type'),
            'name' => __('Name'),
            'url' => __('URL'),
        ];
    }
}
