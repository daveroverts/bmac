<?php

namespace App\Http\Requests\Faq\Admin;

use App\Http\Requests\Request;

class StoreFaq extends Request
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
            'answer' => 'required:string'
        ];
    }
}
