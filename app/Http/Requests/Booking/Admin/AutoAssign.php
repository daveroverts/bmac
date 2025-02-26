<?php

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class AutoAssign extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'oceanicTrack1' => 'required|alpha|min:1|max:2',
            'oceanicTrack2' => 'required|alpha|min:1|max:2',
            'route1' => 'required',
            'route2' => 'required',
            'minFL' => 'required|int:3',
            'maxFL' => 'required|int:3',
            'checkAssignAllFlights' => 'sometimes',
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
            'oceanicTrack1' => __('Track #:number', ['number' => 1]),
            'oceanicTrack2' => __('Track #:number', ['number' => 2]),
            'route1' => __('Route #:number', ['number' => 1]),
            'route2' => __('Route #:number', ['number' => 2]),
            'minFL' => __('Minimum Oceanic Entry FL'),
            'maxFL' => __('Maximum Oceanic Entry FL'),
            'checkAssignAllFlights' => __('Auto-assign all flights?'),
        ];
    }
}
