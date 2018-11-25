<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/DV-Logo3-icon.png') }}" width="40">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ Route::currentRouteNamed('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item {{ Route::currentRouteNamed('booking.index') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('booking.index') }}">Bookings</a>
                </li>
                @auth
                    @if($event = App\Models\Event::where('endEvent', '>', now())->orderBy('startEvent', 'asc')->first())
                        @foreach($bookings = Auth::user()->booking()->where('event_id',$event->id)->get() as $booking)
                            @if($bookings->count() > 1)
                                <li class="nav-item {{ url()->current() === route('booking.show', $booking) ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('booking.show', $booking) }}">
                                        {{ $booking->callsign }}</a></li>
                            @else
                                <li class="nav-item {{ Route::currentRouteNamed('booking.show') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('booking.show', $booking) }}">
                                        My booking</a></li>
                            @endif
                        @endforeach
                    @endif
                @endauth
                <li class="nav-item {{ Route::currentRouteNamed('faq') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('faq') }}">FAQ</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="mailto:events@dutchvacc.nl">Contact Us</a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin)
                    <li class="nav-item">
                        <div class="dropdown">
                            <a class="btn btn-outline-secondary text-white dropdown-toggle {{ Request::is('admin/*') ? 'active' : '' }}"
                               href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                Admin
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item {{ Route::currentRouteNamed('airport.index') ? 'active' : '' }}"
                                   href="{{ route('airport.index') }}">Airports</a>
                                <a class="dropdown-item {{ Route::currentRouteNamed('event.index') ? 'active' : '' }}"
                                   href="{{ route('event.index') }}">Events</a>
                            </div>
                        </div>
                    </li>
                @endif
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('logout') }}">Logout</a></li>
                @endguest
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><img src="{{ asset('images/DV-Logo3.png') }}" width="200"></li>
            </ul>

        </div>
    </div>
</nav>
