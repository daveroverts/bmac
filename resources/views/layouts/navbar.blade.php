<nav class="navbar navbar-expand-md navbar-light navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('storage/DV-Logo3-icon.png') }}" width="40">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="">Bookings</a></li>
                <li class="nav-item"><a class="nav-link" href="">Partners</a></li>
                <li class="nav-item"><a class="nav-link" href="">My bookings</a></li>
                <li class="nav-item"><a class="nav-link" href="">FAQ</a></li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><img src="{{ asset('storage/DV-Logo3.png') }}" width="200"></li>
            </ul>

        </div>
    </div>
</nav>