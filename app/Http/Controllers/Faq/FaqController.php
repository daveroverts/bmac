<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return inertia('Faq/Index', [
            'faq' => Faq::doesntHave('events')
                ->whereIsOnline(true)
                ->get(),
            'events' => Event::upcoming()
                ->with(['faqs' => function ($query) {
                    $query->whereIsOnline(true);
                }])
                ->whereHas('faqs', function (Builder $query) {
                    $query->whereIsOnline(true);
                })
                ->get(['id', 'name'])
        ]);
    }
}
