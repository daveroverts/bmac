<?php

namespace App\Http\Controllers\Faq;

use App\Models\Faq;
use App\Models\Event;
use Illuminate\View\View;
use App\Policies\FaqPolicy;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\Admin\StoreFaq;
use App\Http\Requests\Faq\Admin\UpdateFaq;

class FaqAdminController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FaqPolicy::class, 'faq');
    }

    public function index(): View
    {
        $faqs = Faq::withCount('events')->paginate();
        return view('faq.admin.overview', ['faqs' => $faqs]);
    }

    public function create(): View
    {
        $faq = new Faq();

        $events = collect();
        return view('faq.admin.form', ['faq' => $faq, 'events' => $events]);
    }

    public function store(StoreFaq $request): RedirectResponse
    {
        $faq = Faq::create($request->validated());
        flashMessage('success', __('Done'), __('FAQ has been added!'));
        return to_route('admin.faq.edit', $faq);
    }

    public function edit(Faq $faq): View
    {
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent', 'desc')
            ->get();

        return view('faq.admin.form', ['faq' => $faq, 'events' => $events]);
    }

    public function update(UpdateFaq $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());
        flashMessage('success', __('Done'), __('FAQ has been updated!'));
        return to_route('admin.faq.edit', $faq);
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        flashMessage('success', __('Done'), 'Question has been removed!');
        return to_route('admin.faq.index');
    }

}
