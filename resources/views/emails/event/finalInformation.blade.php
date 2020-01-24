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
After receiving your clearance you will be instructed to report ready with Planner [EHAM_P_DEL] on 121.650. You are advised to report ready as soon as you are, so we can plan your flight in the departure sequence. You will be advised of an expected start-up time, which will ensure you'll arrive at the runway within your CTOT window.

The initial climb altitude for all departures is FL060. Do NOT climb further without any instructions. This may cause a conflict with inbound traffic.

After departure, when passing 2000ft, you switch from TOWER to APPROACH [EHAM_W_APP] yourself without contact instructions by ATC, unless TOWER specifically instructs you to: “remain this frequency”.

Important information about your departure airport can be found at:
- [Pilot Briefing](https://www.dutchvacc.nl/visiting-pilots/)
- [Charts](https://www.dutchvacc.nl/charts/)


---
INFORMATION - ENROUTE/OCEANIC
---
---
It will be possible to make position reports using [natTRAK](https://nattrak.vatsim.net/). Oceanic clearance will still need to be requested with Shanwick Delivery [EGGX_DEL] over voice.

When making calls to Gander/Oceanic be sure to include all the required information. The controllers will not have time to keep asking you for the pieces of information, so ensure you know everything you need to tell them in your clearance request and position reports.

Remember that all times given during oceanic procedures are zulu time,therefore ensure your flightsim clock is as close to this as possible.

Important information about oceanic procedures can be found at:

- [Oceanic Operating Procedures](https://www.virtualnorwegian.net/oceanic/)


---
INFORMATION - ARRIVAL
---
---
Important information about your arrival airport can be found at:

- [Pilot Resources](https://torontofir.ca/pilots/)
- [Charts](https://torontofir.ca/airport-charts/)


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
