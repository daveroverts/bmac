<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateUserSettings extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'airport_view' => ['required', 'int:1'],
            'use_monospace_font' => ['required', 'boolean']
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
            'airport_view' => __('Default airport view'),
            'use_monospace_font' => __('Use monospace font'),
        ];
    }
}
