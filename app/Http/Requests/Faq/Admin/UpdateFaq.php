<?php

namespace App\Http\Requests\Faq\Admin;

use App\Http\Requests\Request;

class UpdateFaq extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_online' => 'required:boolean',
            'question' => 'required:string',
            'answer' => 'required:string',
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
            'is_online' => __('Is online'),
            'question' => __('Question'),
            'answer' => __('Answer'),
        ];
    }
}
