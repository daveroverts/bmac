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
            'startEvent' => 'required|date',
            'endEvent' => 'required|date|after_or_equal:startEvent',
            'startBooking' => 'required|date',
            'endBooking' => 'required|date|after_or_equal:startBooking',
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
            'startEvent' => __('Start event'),
            'endEvent' => __('End event'),
            'startBooking' => __('Start booking'),
            'endBooking' => __('End booking'),
            'image_url' => __('Image URL'),
            'description' => __('Description'),
        ];
    }
}
