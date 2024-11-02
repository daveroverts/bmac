<?php

namespace App\View\Components\Forms\Inputs;

use BladeUIKit\Components\Forms\Inputs\Input as OriginalInput;

class Input extends OriginalInput
{
    public function __construct(string $name, string $id = null, string $type = 'text', ?string $value = '')
    {
        $this->name = $name;
        $this->id = $id ?? $name;
        $this->type = $type;

        $this->value = old($name, $value ?? '');
    }
}
