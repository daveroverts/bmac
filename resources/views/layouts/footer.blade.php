<footer><span class="fa fa-copyright"></span> <?php
    //An script to generate the copyright date using the server's year
    $fromYear = 2018;
    $thisYear = (int)date('Y');
    echo $fromYear . (($fromYear != $thisYear) ? '-' . $thisYear : '');?> <a href="https://www.dutchvacc.nl">Dutch VACC</a> Booking System, created by Dave Roverts - 1186831.
</footer>