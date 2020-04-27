<footer><span class="fa fa-copyright"></span> <?php
    //An script to generate the copyright date using the server's year
    $fromYear = 2018;
    $thisYear = (int) date('Y');
    echo $fromYear.(($fromYear != $thisYear) ? '-'.$thisYear : '');?> Booking System by Dave Roverts (1186831). Used and maintained by <a href="{{ config('app.division_url') }}" target="_blank">{{ config('app.division') }}</a>
    <br>
    <a class="navbar-brand" href="{{ config('app.division_url') }}"><img src="{{ asset('images/division-horizontal.png') }}" height="45"></a>
</footer>
