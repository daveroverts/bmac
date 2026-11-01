<?php

namespace App\Http\Controllers\Booking;

use App\Exports\BookingsExport;
use App\Exports\VatcanSlotsExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingExportController extends Controller
{
    public function __invoke(Event $event, Request $request): BinaryFileResponse|JsonResponse
    {
        $format = $request->validate([
            'format' => ['nullable', Rule::in(['vatcan-csv', 'vatcan-json'])],
        ])['format'] ?? null;

        activity()
            ->by(auth()->user())
            ->on($event)
            ->when($format, fn ($logger) => $logger->withProperties(['format' => $format]))
            ->log('Export triggered');

        return match ($format) {
            'vatcan-csv' => (new VatcanSlotsExport($event))->downloadCsv('vatcan-slots.csv'),
            'vatcan-json' => (new VatcanSlotsExport($event))->downloadJson('vatcan-slots.json'),
            default => (new BookingsExport($event))->download('bookings.csv'),
        };
    }
}
