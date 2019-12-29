@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

This email serves as confirmation of your booking and route for **{{ $booking->event->name }}**.

Before participating in the event, we advise you re-read the pilot briefings to ensure you have all the required knowledge about the event.

**YOUR ROUTE IS INCLUDED IN THIS EMAIL.**

A list of top tips has been compiled and included in this email; by following the advice given in these tips it will help to make **{{ $booking->event->name }}** an enjoyable event for everyone!

---
TOP TIPS
---
---
Q: What timezone are the times quoted on my booking?

A: All times are in UTC (zulu).

Q: What does CTOT stand for?

A: CTOT stands for "Calculated Takeoff Time". This is the time when you're cleared for takeoff.

Q: How do I request oceanic clearance or make a position report?

A: Click [here](https://www.virtualnorwegian.net/oceanic/). There are example clearance requests and position reports within this website.

---
INFORMATION - DEPARTURE
---
---
Pilots can anticipate receiving a clearance through CPDLC. The clearance may include route amendments. CPDLC clearances are accomplished via private message from ATC. Pilots who are not able to accept clearance via CPDLC should disregard the message and request a voice clearance.

Remember, you need a specific clearance to cross each runway you come to (active or inactive). Always hold short of a runway unless you have received a crossing instruction. If you are unsure, ask!

The phrase “climb via SID” will be used on all RNAV departures. This phrase instructs aircraft to stop the initial climb at the SID top altitude (5,000’) until receiving further instruction from ATC.

Important information about your departure airport can be found at:
- [Pilot Briefing](https://www.dutchvacc.nl/downloads/various/Pilot%20Briefing%20Holland-America%20Line%202018.pdf)
- [Charts](https://www.airnav.com/airport/KBOS)

---
INFORMATION - ARRIVAL
---
---

The STAR into Amsterdam will terminate at waypoint SUGOL which has to passed below FL100, plan your descent accordingly. After SUGOL you can expect a direct to SPL, you will need to add this manually into your FMS.

Standard speed within the 15NM radius of SPL is 220kts. When cleared on the ILS maintain speed 180kts or more until established on the glide path, unless instructed otherwise.

Important information about your arrival airport can be found at:

- [Pilot Briefing](https://www.dutchvacc.nl/downloads/various/Pilot%20Briefing%20Holland-America%20Line%202018.pdf)
- [Charts](http://scripts.dutchvacc.nl/aipcharts.php?airport=eham)


---
INFORMATION - ENROUTE/OCEANIC
---



When making calls to Gander/Oceanic be sure to include all the required information. The controllers will not have time to keep asking you for the pieces of information, so ensure you know everything you need to tell them in your clearance request and position reports.

Remember that all times given during oceanic procedures are zulu time,therefore ensure your flightsim clock is as close to this as possible.

Important information about oceanic procedures can be found at:

- [Oceanic Operating Procedures](https://www.virtualnorwegian.net/oceanic/)


---
BOOKING - DETAILS
---
---
Callsign: **{{ $booking->callsign }}**

SELCAL: **{{ $booking->selcal }}**

It is important you stick to these details to save confusion on the day! We will try to make sure you will be able to use this callsign. Do not worry if someone else has already logged in using your callsign, your booking will still be recognized when you log in under another callsign!

Departure airport: **{{ $booking->flights()->first()->airportDep->icao  }}**

Arrival airport: **{{ $booking->flights()->first()->airportDep->icao }}**

CTOT: **{{ $booking->flights()->first()->ctot }}**

Full Route: **{{ $booking->flights()->first()->route }}**

Oceanic Entry Level: **FL{{ $booking->flights()->first()->getOriginal('oceanicFL') }}**

NAT Track: **{{ $booking->flights()->first()->oceanicTrack }}**

NAT TMI: **{{ $booking->event->startEvent->dayOfYear + 1 }}**

---

These details are now accessible on the booking details page within the pilot area on the [Dutch VACC website](https://booking.dutchvacc.nl/).

We would also like to encourage you to take screenshots, videos, recordings of communications, etc. throughout your journey. Tweet to our Twitter account [@DutchVACC](https://twitter.com/DutchVACC) to stay involved - we'll be manning it all day and retweeting your input!

In closing, we would like to ask all participants of the event to send us their feedback via the feedback forms in the pilots area after the event has taken place - this will ensure we can further improve the event in the future.


@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
