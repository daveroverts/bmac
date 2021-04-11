<?php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{

    /**
     * Show the homepage
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return inertia('Home/Index', [
            'events' => Event::upcoming()
                ->whereIsOnline(true)
                ->whereShowOnHomepage(true)
                ->get(['id', 'slug', 'startEvent', 'endEvent', 'description', 'image_url'])
        ]);
    }
}
