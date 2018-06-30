<nav class="navbar navbar-expand-md navbar-light navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/DV-Logo3-icon.png') }}" width="40">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item {{ Request::is('booking') ? 'active' : '' }}"><a class="nav-link" href="{{ route('booking.index') }}">Bookings</a></li>
                @auth
                    <li class="nav-item {{ Request::is('booking/*') ? 'active' : '' }}"><a class="nav-link" href="">My bookings</a></li>
                @endauth
                <li class="nav-item" {{ Request::is('faq') ? 'active' : '' }}><a class="nav-link" href="">FAQ</a></li>
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