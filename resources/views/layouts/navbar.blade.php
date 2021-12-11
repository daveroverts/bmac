<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">

        <a class="navbar-brand" href="{{ URL::to('/') }}"><img src="{{ asset('images/division-square.png') }}" height="45"></a>

        <a class="navbar-brand" href="{{ URL::to('/') }}">
            {{ config('app.title') }}
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Events
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ url('/') }}">Overview</a>

                        @if ($navbarEvents)
                            <div class="dropdown-divider"></div>
                            @foreach($navbarEvents as $event)
                                <a class="dropdown-item {{ request()->fullUrlIs(route('bookings.event.index', $event)) ? 'active' : '' }}"
                                    href="{{ route('bookings.event.index', $event) }}">{{ $event->name }}
                                    – {{ $event->startEvent->toFormattedDateString() }}</a>
                                @auth
                                    @foreach($bookings = auth()->user()->bookings->where('event_id', $event->id) as $booking)
                                        <a class="dropdown-item {{ request()->fullUrlIs(route('bookings.show', $booking)) ? 'active' : '' }}"
                                            href="{{ route('bookings.show', $booking) }}">
                                            <i class="fas fa-chevron-right"></i>&nbsp;{{ $bookings->count() > 1 ? $booking->callsign : 'My booking' }}
                                        </a>
                                    @endforeach
                                @endauth
                                @if(!$loop->last)
                                    <div class="dropdown-divider"></div>
                                @endif
                            @endforeach
                        @endif

                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('faq') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('faq') }}">FAQ</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="mailto:{{ config('app.contact_mail') }}">Contact Us</a>
                </li>

                @auth
                    @if(auth()->user()->isAdmin)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Admin
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item {{ request()->routeIs('admin.events*') ? 'active' : '' }}"
                                    href="{{ route('admin.events.index') }}">Events</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ request()->routeIs('admin.airports*') ? 'active' : '' }}"
                                    href="{{ route('admin.airports.index') }}">Airports</a>
                                <a class="dropdown-item {{ request()->routeIs('admin.faq*') ? 'active' : '' }}"
                                    href="{{ route('admin.faq.index') }}">FAQ</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item {{ request()->routeIs('admin.voting') ? 'active' : '' }}"
                               href="{{ route('admin.voting') }}">Voting</a>
                        </div>
                    </li>
                    @endif
                    @if ($pollOpen)
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="{{route('voting.main')}}" id="navbarDropdown" role="button">
                                Airfield Voting
                            </a>
                        </li>
                        @endif
                @endauth
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">

                @guest
                    @if(request()->routeIs('events.show'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('login', ['event' => $event]) }}">Login</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endif
                @else

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ auth()->user()->fullName }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item {{ request()->routeIs('user.settings') ? 'active' : '' }}"
                                href="{{ route('user.settings') }}">My settings</a>
                            <a class="dropdown-item {{ request()->routeIs('user.settings') ? 'active' : '' }}"
                                href="{{ route('logout') }}">Log out</a>
                        </div>
                    </li>

                @endguest


            </ul>

        </div>
    </div>
</nav>
