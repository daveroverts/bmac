<x-filament::page>
    <x-filament::form wire:submit.prevent="sendFinalInformationEmail">
        {{ $this->finalInformationEmailForm }}

        <x-filament::button type="submit" icon="heroicon-o-mail">
            Send <strong>Final Information</strong> E-mail
        </x-filament::button>

    </x-filament::form>

    <hr />

    <x-filament::form wire:submit.prevent="sendEmail">
        {{ $this->emailForm }}

        <x-filament::button type="submit" icon="heroicon-o-mail">
            Send E-mail
        </x-filament::button>

    </x-filament::form>
</x-filament::page>
