<?php

declare(strict_types=1);

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class StoreBooking extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['exists:events,id', 'required'],
            'bulk' => ['required', 'boolean'],
            'is_callsign_editable' => ['required', 'boolean'],
            'is_actype_editable' => ['required', 'boolean'],
            'callsign' => ['nullable', 'alpha_num', 'between:4,7'],
            'acType' => ['nullable', 'alpha_num', 'between:3,4'],
            'ctot' => ['sometimes', 'nullable'],
            'eta' => ['sometimes', 'nullable'],
            'route' => ['sometimes', 'nullable'],
            'dep' => ['nullable', 'exists:airports,id'],
            'arr' => ['nullable', 'exists:airports,id'],
            'start' => ['sometimes', 'date_format:H:i'],
            'end' => ['sometimes', 'date_format:H:i'],
            'separation' => ['sometimes', 'numeric', 'min:1'],
            'oceanicFL' => ['sometimes', 'nullable', 'integer:3'],
            'notes' => ['nullable'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    #[\Override]
    public function attributes()
    {
        return [
            'id' => __('Event id'),
            'bulk' => __('Bulk'),
            'is_callsign_editable' => __('Callsign editable?'),
            'is_actype_editable' => __('Aircraft code editable?'),
            'callsign' => __('Callsign'),
            'acType' => __('Aircraft code'),
            'ctot' => __('CTOT'),
            'eta' => __('ETA'),
            'route' => __('Route'),
            'dep' => __('Departure airport'),
            'arr' => __('Arrival airport'),
            'start' => __('Start'),
            'end' => __('End'),
            'separation' => __('Separation (in minutes)'),
            'oceanicFL' => __('Oceanic Entry Level') . ' / ' . __('Cruise FL'),
            'notes' => __('Notes'),
        ];
    }
}
