<?php

use App\Models\Event;

it('flashes type, title, and text to the session', function (): void {
    flashMessage('success', 'Test Title', 'Test Text');

    expect(session('type'))->toBe('success')
        ->and(session('title'))->toBe('Test Title')
        ->and(session('text'))->toBe('Test Text');
});

it('returns the next upcoming online event', function (): void {
    $futureEvent = Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->expired()->create(['is_online' => true]);

    $result = nextEvent();

    expect($result->id)->toBe($futureEvent->id);
});

it('returns the soonest upcoming event when multiple exist', function (): void {
    $soonest = Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->addDays(3),
        'endEvent' => now()->addDays(3)->addHours(3),
    ]);

    Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->addDays(10),
        'endEvent' => now()->addDays(10)->addHours(3),
    ]);

    expect(nextEvent()->id)->toBe($soonest->id);
});

it('returns collection of upcoming online events from nextEvents', function (): void {
    Event::factory()->count(3)->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->expired()->create(['is_online' => true]);

    $events = nextEvents();

    expect($events)->toHaveCount(3);
});

it('filters events by homepage when homepage parameter is true', function (): void {
    Event::factory()->onHomePage()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->notOnHomePage()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    $events = nextEvents(false, false, true);

    expect($events)->toHaveCount(1);
});

it('includes offline events in nextEvents when showAll is true', function (): void {
    Event::factory()->create([
        'is_online' => false,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    $events = nextEvents(false, true);

    expect($events)->toHaveCount(2);
});

it('returns next events for FAQ with faqs relationship eager loaded', function (): void {
    Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    $events = nextEventsForFaq();

    expect($events)->toHaveCount(1)
        ->and($events->first()->relationLoaded('faqs'))->toBeTrue();
});
