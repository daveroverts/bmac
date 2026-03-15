<?php

namespace App\Http\Requests\Event\Admin;

use App\Http\Requests\Request;

class SendFinalInformation extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'testmode' => ['boolean'],
            'forceSend' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'testmode' => __('Test mode'),
            'forceSend' => __('Send to everybody'),
        ];
    }
}
