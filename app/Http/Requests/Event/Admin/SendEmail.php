<?php

namespace App\Http\Requests\Event\Admin;

use App\Http\Requests\Request;

class SendEmail extends Request
{
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
            'testmode' => 'boolean'
        ];
    }
}
