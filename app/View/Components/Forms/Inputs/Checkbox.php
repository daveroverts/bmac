<?php

namespace App\View\Components\Forms\Inputs;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public function __construct(
        public string $name,
        public ?string $id = null,
        public bool $checked = false,
        public ?string $value = '',
    ) {
        $this->id = $id ?? $name;
        $this->checked = (bool) old($name, $checked);
    }

    public function render(): View
    {
        return view('components.forms.inputs.checkbox');
    }
}
