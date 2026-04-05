<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Logout extends Component
{
    public function __construct(public ?string $action = null)
    {
        $this->action = $action ?? route('logout');
    }

    public function render(): View
    {
        return view('components.logout');
    }
}
