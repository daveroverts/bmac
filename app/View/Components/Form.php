<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Form extends Component
{
    public function __construct(
        public ?string $action = null,
        public string $method = 'POST',
        public bool $hasFiles = false,
    ) {
        $this->method = strtoupper($method);
    }

    public function render(): View
    {
        return view('components.form');
    }
}
