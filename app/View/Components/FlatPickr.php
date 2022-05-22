<?php

namespace App\View\Components;

class FlatPickr extends \BladeUIKit\Components\Forms\Inputs\FlatPickr
{
    public function options(): array
    {
        return array_merge([
            'time_24hr' => true,
            'altFormat' => 'F j, Y H:i\\z',
            'ariaDateFormat' => 'F j, Y H:i'
        ], parent::options());
    }
}
