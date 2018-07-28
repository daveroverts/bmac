@component('mail::message')
# Booking confirmed

Dear **{{ $booking->bookedBy->full_name }}**,

This email serves as confirmation of your booking and route for **{{ $booking->event->name }}**.

Before flying in the event, we would advise that you re-read the pilot briefings to ensure that you have all the relevant information about the event.

**YOUR ROUTE IS INCLUDED IN THIS EMAIL.**

A list of top tips have been compiled and included in this email; by following the advice given in these tips it will help to make **{{ $booking->event->name }}** an enjoyable event for everyone!

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
Important information about your departure airport can be found at: [Schiphol Pilot Briefing](https://www.dutchvacc.nl/index.php?option=com_content&view=article&id=149&Itemid=149)

---
INFORMATION - ARRIVAL
---
---
Important information about your arrival airport can be found at: [Boston Pilot Briefing](http://www.bvartcc.com/)

---
INFORMATION - ENROUTE/OCEANIC
---

For information on Oceanic operating procedures, you need to visit [https://www.virtualnorwegian.net/oceanic/](https://www.virtualnorwegian.net/oceanic/)

When making calls to Gander/Oceanic be sure to include all the required information. The controllers will not have time to keep asking you for the pieces of information, so ensure you know everything you need to tell them in your clearance request and position reports.

Remember that all times given during oceanic procedures are zulu time,therefore ensure your flightsim clock is as close to this as possible.

It is highly recommended that you download, print, complete and use the Oceanic Clearance/Position Report helpsheet, available at: [https://ctp.vatsim.net/system/view/includes/Transatlantic_Radio_Operations_Checksheet.pdf](https://ctp.vatsim.net/system/view/includes/Transatlantic_Radio_Operations_Checksheet.pdf)

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

NAT TMI: **{{ $booking->event->startEvent->dayOfYear }}**

---

These details are now accessible on the booking details page within the pilot area on the [Dutch VACC website](https://booking.dutchvacc.nl/).

We would also like to encourage you to take screenshots, videos, recordings of communications, etc. throughout your journey. Tweet to our Twitter account [@DutchVACC](https://twitter.com/DutchVACC) to stay involved - we'll be manning it all day and retweeting your input!

In closing, we would like to ask all participants of the event to send us their feedback via the feedback forms in the pilots area after the event has taken place - this will ensure we can further improve the event in the future.


Regards,

**Dutch VACC**
@endcomponent