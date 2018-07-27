<?php

namespace App\Http\Requests;

use Illuminate\{
    Foundation\Http\FormRequest, Support\Facades\Auth
};

class SendEmail extends FormRequest
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
        } else return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => 'bail|required:string',
            'message' => 'required:string',
        ];
    }
}
