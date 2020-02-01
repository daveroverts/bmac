<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\AdminController;
use App\Http\Requests\Faq\Admin\StoreFaq;
use App\Http\Requests\Faq\Admin\UpdateFaq;
use App\Models\Event;
use App\Models\Faq;
use App\Policies\FaqPolicy;

class FaqAdminController extends AdminController
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(FaqPolicy::class, 'faq');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faqs = Faq::withCount('events')->paginate();
        return view('faq.admin.overview', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $faq = new Faq();
        return view('faq.admin.form', compact('faq'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreFaq  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFaq $request)
    {
        $faq = Faq::create($request->validated());
        flashMessage('success', 'Done', 'Question has been added!');
        return redirect(route('admin.faq.edit', $faq));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function edit(Faq $faq)
    {
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent', 'desc')
            ->get();

        return view('faq.admin.form', compact('faq', 'events'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateFaq  $request
     * @param  Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFaq $request, Faq $faq)
    {
        $faq->update($request->validated());
        flashMessage('success', 'Done', 'Question has been updated!');
        return redirect(route('admin.faq.edit', $faq));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Faq  $faq
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        flashMessage('success', 'Done', 'Question has been removed!');
        return redirect(route('admin.faq.index'));
    }

    /**
     * Link or unlink a event to a FAQ
     *
     * @param  Faq  $faq
     * @param  Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleEvent(Faq $faq, Event $event)
    {
        if ($faq->events()->where('event_id', $event->id)->get()->isNotEmpty()) {
            $faq->events()->detach($event->id);
            flashMessage('success', 'Event unlinked', 'Event has been unlinked to this FAQ');
        } else {
            $faq->events()->attach($event->id);
            flashMessage('success', 'Event linked', 'Event has been linked to this FAQ');
        }
        return back();
    }
}
