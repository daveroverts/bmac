<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\Request;
use App\Rules\ValidSelcal;

class UpdateBooking extends Request
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Combine selcal1 and selcal2 into a single selcal field for validation
        if ($this->filled('selcal1') && $this->filled('selcal2')) {
            $this->merge([
                'selcal' => strtoupper($this->selcal1 . '-' . $this->selcal2),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'callsign' => 'sometimes|alpha_num|between:4,7|unique:bookings,callsign,' . auth()->id() . ',user_id,event_id,' . $this->route('booking')->event->id,
            'acType' => ['sometimes', 'alpha_num', 'between:3,4'],
            'selcal1' => ['sometimes', 'nullable', 'alpha', 'size:2'],
            'selcal2' => 'sometimes|nullable|required_with:selcal1,!=' . null . '|alpha|size:2',
            'selcal' => ['sometimes', 'nullable', new ValidSelcal($this->route('booking')->event->id)],
            'checkStudy' => ['sometimes', 'accepted'],
            'checkCharts' => ['sometimes', 'accepted'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'checkStudy.accepted' => __('You must agree to study the provided briefing material'),
            'checkCharts.accepted' => __('You must agree to have applicable charts at hand during the event'),
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
            'callsign' => __('Callsign'),
            'acType' => __('Aircraft code'),
            'selcal1' => __('SELCAL'),
            'selcal2' => __('SELCAL'),
            'checkStudy' => __('Briefing material'),
            'checkCharts' => __('Charts'),
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // If the combined selcal field has errors, add to selcal1 for display
            // and mark selcal2 as invalid for styling (without duplicating the message)
            if ($validator->errors()->has('selcal')) {
                $selcalErrors = $validator->errors()->get('selcal');
                foreach ($selcalErrors as $error) {
                    $validator->errors()->add('selcal1', $error);
                }

                // Add a marker to selcal2 for styling purposes only (no message)
                $validator->errors()->add('selcal2', '');
                // Remove the error from the combined field since it's not displayed
                $validator->errors()->forget('selcal');
            }
        });
    }
}
