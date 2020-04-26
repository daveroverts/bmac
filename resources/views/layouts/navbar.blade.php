<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ URL::to('/') }}">
            Event Booking
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                </li>

                <li class="nav-item">
                    <div class="dropdown">
                        <a class="btn btn-outline-secondary text-white dropdown-toggle {{ request()->routeIs(['events*', 'bookings*']) ? 'active' : '' }}"
                           href="#" role="button" id="dropdownEvents" data-toggle="dropdown" aria-haspopup="true"
                           aria-expanded="false">
                            Events
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownEvents">
                            @foreach(nextEvents() as $event)
                                <a class="dropdown-item {{ request()->fullUrlIs(route('events.show', $event)) ? 'active' : '' }}"
                                   href="{{ route('events.show', $event) }}">{{ $event->name }}
                                    – {{ $event->startEvent->toFormattedDateString() }}</a>
                                <a class="dropdown-item {{ request()->fullUrlIs(route('bookings.event.index', $event)) ? 'active' : '' }}"
                                   href="{{ route('bookings.event.index', $event) }}">Bookings</a>
                                @auth
                                    @foreach($bookings = auth()->user()->bookings->where('event_id', $event->id) as $booking)
                                        <a class="dropdown-item {{ request()->fullUrlIs(route('bookings.show', $booking)) ? 'active' : '' }}"
                                           href="{{ route('bookings.show', $booking) }}">
                                            <i class="fas fa-arrow-right"></i>&nbsp;{{ $bookings->count() > 1 ? $booking->callsign : 'My booking' }}
                                        </a>
                                    @endforeach
                                @endauth
                                @if(!$loop->last)
                                    <div class="dropdown-divider"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('faq') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('faq') }}">FAQ</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="mailto:{{ config('app.contact_mail') }}">Contact Us</a>
                </li>
                @guest
                    @if(request()->routeIs('events.show'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('login', ['event' => $event]) }}">Login</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endif
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('logout') }}">Logout</a></li>

                    <li class="nav-item">
                        <div class="dropdown">
                            <a class="btn btn-outline-secondary text-white dropdown-toggle {{ request()->routeIs(['admin*', 'user*']) ? 'active' : '' }}"
                               href="#" role="button" id="dropdownUser" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                {{ auth()->user()->pic }}
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownUser">
                                <a class="dropdown-item {{ request()->routeIs('user.settings') ? 'active' : '' }}"
                                   href="{{ route('user.settings') }}">My settings</a>
                                @if(auth()->user()->isAdmin)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item {{ request()->routeIs('admin.airports*') ? 'active' : '' }}"
                                       href="{{ route('admin.airports.index') }}">Airports</a>
                                    <a class="dropdown-item {{ request()->routeIs('admin.events*') ? 'active' : '' }}"
                                       href="{{ route('admin.events.index') }}">Events</a>
                                    <a class="dropdown-item {{ request()->routeIs('admin.faq*') ? 'active' : '' }}"
                                       href="{{ route('admin.faq.index') }}">FAQ</a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endguest
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="https://www.dutchvacc.nl/"><img src="{{ asset('images/DV-Logo3.png') }}" width="200"></a>
                </li>
            </ul>

        </div>
    </div>
</nav>
