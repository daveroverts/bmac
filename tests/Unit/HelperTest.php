<?php

it('flashes type, title, and text to the session', function (): void {
    flashMessage('success', 'Test Title', 'Test Text');

    expect(session('type'))->toBe('success')
        ->and(session('title'))->toBe('Test Title')
        ->and(session('text'))->toBe('Test Text');
});
