<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VatcanSlotsExport
{
    public function __construct(public Event $event)
    {
    }

    public function downloadCsv(string $fileName): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'vatcan-export') . '.csv';

        $writer = SimpleExcelWriter::createWithoutBom($path)->addHeader(['cid', 'slot']);

        $this->rows()->each(fn (array $row): SimpleExcelWriter => $writer->addRow([
            'cid' => $row['cid'],
            'slot' => $row['slot'] ?? '',
        ]));

        $writer->close();

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    public function downloadJson(string $fileName): JsonResponse
    {
        return response()->json($this->rows()->all())
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Booked slots as VATCAN expects them, ordered by slot with empty slots last.
     *
     * @return Collection<int, array{cid: int, slot: string|null}>
     */
    private function rows(): Collection
    {
        return $this->event->bookings()
            ->with('flights')
            ->booked()
            ->whereNotNull('user_id')
            ->get()
            ->map(fn (Booking $booking): array => [
                'cid' => (int) $booking->user_id,
                'ctot' => $booking->flights->first()?->ctot,
            ])
            ->sortBy(fn (array $row): int => $row['ctot']?->getTimestamp() ?? PHP_INT_MAX)
            ->map(fn (array $row): array => [
                'cid' => $row['cid'],
                'slot' => $row['ctot']?->format('Hi'),
            ])
            ->values();
    }
}
