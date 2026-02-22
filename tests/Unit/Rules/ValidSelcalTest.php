<?php

use App\Models\Booking;
use App\Models\Event;
use App\Rules\ValidSelcal;
use Illuminate\Support\Facades\Validator;

it('passes validation for a correct SELCAL code', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'AB-CD'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid SELCAL format with invalid characters', function (): void {
    $event = Event::factory()->create();

    // I, N, O, T are not valid characters
    $validator = Validator::make(
        ['selcal' => 'AI-NO'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('format is invalid');
});

it('fails validation for invalid SELCAL format without hyphen', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'ABCD'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('format is invalid');
});

it('fails validation when SELCAL contains duplicate characters', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'AA-BC'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('unique characters');
});

it('fails validation when characters in first pair are not in alphabetical order', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'BA-CD'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('alphabetical order');
});

it('fails validation when characters in second pair are not in alphabetical order', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'AB-DC'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('alphabetical order');
});

it('fails validation when SELCAL already exists for the same event', function (): void {
    $event = Event::factory()->create();
    Booking::factory()->create([
        'event_id' => $event->id,
        'selcal' => 'AB-CD',
    ]);

    $validator = Validator::make(
        ['selcal' => 'AB-CD'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('already in use');
});

it('allows same SELCAL for different events', function (): void {
    $event1 = Event::factory()->create();
    $event2 = Event::factory()->create();

    Booking::factory()->create([
        'event_id' => $event1->id,
        'selcal' => 'AB-CD',
    ]);

    $validator = Validator::make(
        ['selcal' => 'AB-CD'],
        ['selcal' => new ValidSelcal($event2->id)]
    );

    expect($validator->passes())->toBeTrue();
});

it('passes validation when the same SELCAL belongs to the excluded booking', function (): void {
    $event = Event::factory()->create();
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'selcal' => 'AB-CD',
    ]);

    $validator = Validator::make(
        ['selcal' => 'AB-CD'],
        ['selcal' => new ValidSelcal($event->id, $booking->id)]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails validation when same SELCAL is used by a different booking in the same event', function (): void {
    $event = Event::factory()->create();
    $existingBooking = Booking::factory()->create([
        'event_id' => $event->id,
        'selcal' => 'AB-CD',
    ]);
    $otherBooking = Booking::factory()->create([
        'event_id' => $event->id,
        'selcal' => 'EF-GH',
    ]);

    $validator = Validator::make(
        ['selcal' => 'AB-CD'],
        ['selcal' => new ValidSelcal($event->id, $otherBooking->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('already in use');
});

it('fails validation when SELCAL has extra characters around valid pattern', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 'ZZAB-CDzz'],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('format is invalid');
});

it('fails validation when value is not a string', function (): void {
    $event = Event::factory()->create();

    $validator = Validator::make(
        ['selcal' => 123],
        ['selcal' => new ValidSelcal($event->id)]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('selcal'))->toContain('must be a string');
});
