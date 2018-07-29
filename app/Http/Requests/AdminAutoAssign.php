<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAutoAssign extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'oceanicTrack1' => 'required|alpha|min:1|max:2',
            'oceanicTrack2' => 'required|alpha|min:1|max:2',
            'route1' => 'required',
            'route2' => 'required',
            'minFL' => 'required|int:3',
            'maxFL' => 'required|int:3',
        ];
    }
}
