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

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'subject' => __('Subject'),
            'message' => __('Message'),
            'testmode' => __('Test mode'),
        ];
    }
}
