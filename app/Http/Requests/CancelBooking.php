<?php

namespace App\Http\Requests;

use App\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CancelBooking extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $booking = Booking::find($this->route('booking'));

        return $booking && $this->user()->can('cancel', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
