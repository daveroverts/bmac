<?php

namespace App\Http\Controllers\Faq;

use App\Models\Faq;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function __invoke(Request $request): View
    {
        $faqs = Faq::doesntHave('events')
            ->whereIsOnline(true)
            ->get();

        return view('faq.overview', ['faqs' => $faqs]);
    }
}
