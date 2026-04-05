<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FlatPickr extends Component
{
    public function __construct(
        public string $name,
        public ?string $id = null,
        public ?string $value = '',
        public string $format = 'Y-m-d H:i',
        public ?string $placeholder = null,
        public array $options = [],
    ) {
        $this->id = $id ?? $name;
        $this->value = old($name, $value ?? '');
        $this->placeholder = $placeholder ?? $format;
    }

    public function options(): array
    {
        return array_merge([
            'time_24hr' => true,
            'altFormat' => 'F j, Y H:i\\z',
            'ariaDateFormat' => 'F j, Y H:i',
            'dateFormat' => $this->format,
            'altInput' => true,
            'enableTime' => true,
        ], $this->options);
    }

    public function jsonOptions(): string
    {
        if (empty($this->options())) {
            return '';
        }

        return json_encode((object) $this->options());
    }

    public function render(): View
    {
        return view('components.flat-pickr');
    }
}
