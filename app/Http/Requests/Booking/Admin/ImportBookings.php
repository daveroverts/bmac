<?php

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class ImportBookings extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file',
        ];
    }
}
