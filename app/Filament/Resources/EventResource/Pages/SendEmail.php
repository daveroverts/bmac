<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Models\User;
use App\Models\Event;
use App\Enums\BookingStatus;
use App\Events\EventBulkEmail;
use Filament\Resources\Pages\Page;
use App\Events\EventFinalInformation;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\EventResource;
use Filament\Forms\Components\RichEditor;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SendEmail extends Page
{
    protected static string $resource = EventResource::class;

    protected static string $view = 'filament.resources.event-resource.pages.send-email';

    public Event $event;

    public function mount(Event $record): void
    {
        $this->event = $record;
        $this->finalInformationEmailForm->fill();
        $this->emailForm->fill();
    }

    protected function getFinalInformationEmailSchema(): array
    {
        return [
            Toggle::make('test_mode_final')
                ->label('Test mode')
                ->helperText('Send a random **Final Information E-mail** to yourself'),
            Toggle::make('force_send')
                ->helperText('Send to all particpants, even though they already received it (and no edit was made)'),

        ];
    }

    protected function getEmailSchema(): array
    {
        return [
            TextInput::make('subject')
                ->prefix($this->event->name . ':'),
            RichEditor::make('message')
                ->helperText('Salutation and closing are already included')
                ->disableToolbarButtons([
                    'attachFiles',
                    'codeBlock',
                ]),
            Toggle::make('test_mode')
                ->helperText('Send a random **E-mail** to yourself'),
        ];
    }

    protected function getForms(): array
    {
        return [
            'finalInformationEmailForm' => $this->makeForm()
                ->schema($this->getFinalInformationEmailSchema())
                ->model($this->event),
            'emailForm' => $this->makeForm()
                ->schema($this->getEmailSchema())
                ->model($this->event),
        ];
    }

    public function sendFinalInformationEmail(): void
    {
        $state = $this->finalInformationEmailForm->getState();

        $bookings = $this->event->bookings()
            ->with(['user', 'flights'])
            ->where('status', BookingStatus::BOOKED)
            ->get();

        if ($state['test_mode_final']) {
            EventFinalInformation::dispatch($bookings->random(), auth()->user());
            $this->notify('primary', 'Email has been sent to yourself');
            return;
        }

        $count = $bookings->count();
        $countSkipped = 0;
        foreach ($bookings as $booking) {
            if (!$booking->has_received_final_information_email || $state['force_send']) {
                EventFinalInformation::dispatch($booking);
            } else {
                $count--;
                $countSkipped++;
            }
        }

        $message = "Final Information has been sent to {$count} people!";
        if ($countSkipped != 0) {
            $message .= " However, {$countSkipped} where skipped, because they already received one";
        }

        activity()
            ->by(auth()->user())
            ->on($this->event)
            ->withProperties([
                'count' => $count,
                'countSkipped', $countSkipped
            ])
            ->log('Final Information E-mail');

        $this->notify('success', $message, true);
        $this->redirectRoute('filament.resources.events.index');
    }

    public function sendEmail(): void
    {
        $state = $this->emailForm->getState();

        if ($state['test_mode']) {
            EventBulkEmail::dispatch($this->event, $state, collect([auth()->user()]));
            $this->notify('primary', 'Email has been sent to yourself');
            return;
        }

        $users = User::whereHas('bookings', function (Builder $query) {
            $query->where('event_id', $this->event->id);
            $query->where('status', BookingStatus::BOOKED);
        })->get();

        EventBulkEmail::dispatch($this->event, $state, $users);
        $this->notify('success', "Bulk E-mail has been sent to {$users->count()} people!", true);
        $this->redirectRoute('filament.resources.events.index');
    }
}
