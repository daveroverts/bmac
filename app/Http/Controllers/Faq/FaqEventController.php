<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;

class FaqEventController extends Controller
{
    public function store(Faq $faq, Event $event): RedirectResponse
    {
        $this->authorize('update', $faq);

        $faq->events()->attach($event->id);
        flashMessage('success', __('Event linked'), __('Event has been linked to this FAQ'));

        return back();
    }

    public function destroy(Faq $faq, Event $event): RedirectResponse
    {
        $this->authorize('update', $faq);

        $faq->events()->detach($event->id);
        flashMessage('success', __('Event unlinked'), __('Event has been unlinked to this FAQ'));

        return back();
    }
}
