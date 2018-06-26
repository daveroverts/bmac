<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateBooking extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::check() && Auth::user()->isAdmin) {
            return true;
        }
        else return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ctot' => 'date_format:H:i',
            'route' => 'nullable|string',
            'oceanicFL' => 'sometimes|int:3',
            'oceanicTrack' => 'nullable|alpha|min:1|max:2',
        ];
    }
}
