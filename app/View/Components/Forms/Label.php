<?php

namespace App\View\Components\Forms;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Label extends Component
{
    public function __construct(public string $for, public ?string $label = null)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.forms.label', [
            'for' => $this->for,
            'fallback' => $this->fallback(),
        ]);
    }

    public function fallback(): string
    {
        return Str::ucfirst(str_replace('_', ' ', $this->for));
    }
}
