<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;

class Error extends Component
{
    public function __construct(public string $field, public string $bag = 'default')
    {
    }

    /**
     * @return array<int, string>
     */
    public function messages(ViewErrorBag $errors): array
    {
        $bag = $errors->getBag($this->bag);

        return $bag->has($this->field) ? $bag->get($this->field) : [];
    }

    public function render(): View
    {
        return view('components.forms.error');
    }
}
