<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __invoke(Request $request): View
    {
        $faqs = Faq::doesntHave('events')
            ->whereIsOnline(true)
            ->get();

        return view('faq.overview', compact('faqs'));
    }
}
