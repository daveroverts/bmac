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

A: Download the [Transatlantic Operations Checklist (click here)](https://ctp.vatsim.net/system/view/includes/Transatlantic_Radio_Operations_Checksheet.pdf). There are example clearance requests and position reports within this document.

---
INFORMATION - DEPARTURE
---
---
The controllers at Amsterdam are provided with a tool that allows them to prioritize departures based on whether or not your flight is booked, your CTOT and the distance you’ll have to taxi. Using these values a TSAT (targeted start-up approval time) will be calculated which the planner will use to get you pushed back and on your way at the right time.

We advise you to be online 35 minutes prior to your CTOT and request your clearance shortly thereafter.

A live overview of free stands can be found [here](http://scripts.dutchvacc.nl/aipcharts.php?airport=eham).

Important information about your departure airport can be found at:
- [Pilot Briefing](https://www.dutchvacc.nl/index.php?option=com_content&view=article&id=149&Itemid=149)
- [Charts](http://scripts.dutchvacc.nl/aipcharts.php?airport=eham)

---
INFORMATION - ARRIVAL
---
---

Boston ARTCC will issue "descend via" instructions for aircraft on STAR arrival procedures. When given "descend via", you are cleared to follow the published altitude restrictions on the STAR. Otherwise, you must remain at your last cleared altitude.

Ensure the assigned runway matches with your FMS route. Regardless of "descend via", published speed restrictions are always mandatory.

When you check in on a new frequency while “descending via”, you are required to state the arrival and runway (if any) when you check in: "Boston Approach, KLM391, 17,500, descending via the OOSHN5 arrival, Runway 22L, with information Kilo".

Important information about your arrival airport can be found at:

- [Pilot Briefing](http://www.bvartcc.com/LTA)
- [Charts](https://www.airnav.com/airport/KBOS)


---
INFORMATION - ENROUTE/OCEANIC
---



When making calls to Gander/Oceanic be sure to include all the required information. The controllers will not have time to keep asking you for the pieces of information, so ensure you know everything you need to tell them in your clearance request and position reports.

Remember that all times given during oceanic procedures are zulu time,therefore ensure your flightsim clock is as close to this as possible.

Important information about oceanic procedures can be found at:

- [Oceanic Operating Procedures](https://www.virtualnorwegian.net/oceanic/)
- [Oceanic Radio Checklist](https://ctp.vatsim.net/system/view/includes/Transatlantic_Radio_Operations_Checksheet.pdf)


---
BOOKING - DETAILS
---
---
Callsign: **{{ $booking->callsign }}**

SELCAL: **{{ $booking->selcal }}**

It is important you stick to these details to save confusion on the day! We will try to make sure you will be able to use this callsign. Do not worry if someone else has already logged in using your callsign, your booking will still be recognized when you log in under another callsign!

Departure airport: **{{ $booking->dep  }}**

Arrival airport: **{{ $booking->arr }}**

CTOT: **{{ $booking->ctot }}**

Full Route: **{{ $booking->route }}**

Oceanic Entry Level: **FL{{ $booking->getOriginal('oceanicFL') }}**

NAT Track: **{{ $booking->oceanicTrack }}**

NAT TMI: **{{ $booking->event->startEvent->dayOfYear + 1 }}**

---

These details are now accessible on the booking details page within the pilot area on the [Dutch VACC website](https://booking.dutchvacc.nl/).

We would also like to encourage you to take screenshots, videos, recordings of communications, etc. throughout your journey. Tweet to our Twitter account [@DutchVACC](https://twitter.com/DutchVACC) to stay involved - we'll be manning it all day and retweeting your input!

In closing, we would like to ask all participants of the event to send us their feedback via the feedback forms in the pilots area after the event has taken place - this will ensure we can further improve the event in the future.


Regards,

**Dutch VACC**
@endcomponent