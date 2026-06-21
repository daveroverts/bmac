<?php

namespace App\Livewire\Airport\Admin;

use App\Models\Airport;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Overview extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(auth()->user()?->isAdmin, 404);

        $airports = Airport::query()
            ->withCount(['flightsDep', 'flightsArr', 'eventDep', 'eventArr'])
            ->when($this->search, function (Builder $query, string $search): void {
                $query->where('icao', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('iata', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('name', 'like', sprintf('%%%s%%', $search));
            })
            ->paginate(100);

        return view('livewire.airport.admin.overview', ['airports' => $airports]);
    }
}
