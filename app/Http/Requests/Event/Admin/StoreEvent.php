<?php

namespace App\Http\Requests\Event\Admin;

use App\Http\Requests\Request;

class StoreEvent extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_online' => 'required|boolean',
            'show_on_homepage' => 'required|boolean',
            'name' => 'bail|required:string',
            'event_type_id' => 'exists:event_types,id|required',
            'import_only' => 'required|boolean',
            'uses_times' => 'required|boolean',
            'multiple_bookings_allowed' => 'required|boolean',
            'is_oceanic_event' => 'required|boolean',
            'dep' => 'exists:airports,id|required',
            'arr' => 'exists:airports,id|required',
            'dateEvent' => 'required|date',
            'timeBeginEvent' => 'required',
            'timeEndEvent' => 'required',
            'dateBeginBooking' => 'required|date',
            'timeBeginBooking' => 'required',
            'dateEndBooking' => 'required|date|after_or_equal:dateBeginBooking',
            'timeEndBooking' => 'required',
            'image_url' => 'nullable|url',
            'description' => 'required:string',
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
            'is_online' => __('Show online?'),
            'show_on_homepage' => __('Show on homepage?'),
            'name' => __('Name'),
            'event_type_id' => __('Event type'),
            'import_only' => __('Only import?'),
            'uses_times' => __('Show times?'),
            'multiple_bookings_allowed' => __('Multiple bookings allowed?'),
            'is_oceanic_event' => __('Oceanic event?'),
            'dep' => __('Departure airport'),
            'arr' => __('Arrival airport'),
            'dateEvent' => __('Event date'),
            'timeBeginEvent' => __('Event begin'),
            'timeEndEvent' => __('Event end'),
            'dateBeginBooking' => __('Start bookings date'),
            'timeBeginBooking' => __('Start bookings time'),
            'dateEndBooking' => __('End bookings date'),
            'timeEndBooking' => __('End bookings time'),
            'image_url' => __('Image URL'),
            'description' => __('Description'),
        ];
    }
}
