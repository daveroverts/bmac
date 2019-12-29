<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateUserSettings extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'airport_view' => 'required|int:1',
            'use_monospace_font' => 'required|boolean'
        ];
    }
}
