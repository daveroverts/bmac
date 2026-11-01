<?php

namespace App\Livewire\Event\Admin;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Overview extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $period = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(auth()->user()?->isAdmin, 404);

        $period = in_array($this->period, ['upcoming', 'past'], true) ? $this->period : 'all';

        $events = Event::query()
            ->with('type')
            ->when($this->search, function (Builder $query, string $search): void {
                $query->where('name', 'like', sprintf('%%%s%%', $search));
            })
            ->when($period === 'upcoming', function (Builder $query): void {
                $query->where('endEvent', '>=', now());
            })
            ->when($period === 'past', function (Builder $query): void {
                $query->where('endEvent', '<', now());
            })
            ->orderByDesc('startEvent')
            ->orderByDesc('id')
            ->paginate();

        return view('livewire.event.admin.overview', ['events' => $events, 'activePeriod' => $period]);
    }
}
