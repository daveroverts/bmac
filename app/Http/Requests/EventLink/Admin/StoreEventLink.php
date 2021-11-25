<?php

namespace App\Http\Requests\EventLink\Admin;

use App\Http\Requests\Request;

class StoreEventLink extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_link_type_id' => 'required|exists:airport_link_types,id',
            'event_id' => 'required|exists:events,id',
            'name' => 'nullable|string',
            'url' => 'required|url',
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
            'event_id' => __('Event'),
            'name' => __('Name'),
            'url' => __('URL'),
        ];
    }
}
