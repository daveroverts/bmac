@component('mail::message')
# Feedback request

Dear **{{ $user->full_name }}**,

Thanks for participating in **{{ $event->name }}** event, we hope you enjoyed it as much as we did. Please let us know if what we could improve and what you enjoyed most about the event. We really do appreciate this feedback to improve the quality of our events in the future!


[Click this link](https://www.dutchvacc.nl/index.php?option=com_mad4joomla&jid=4&Itemid=123) to be redirected to leave your feedback.

Regards,

**Dutch VACC**
@endcomponent