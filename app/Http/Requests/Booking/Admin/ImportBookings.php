<?php

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class ImportBookings extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file',
        ];
    }
}
