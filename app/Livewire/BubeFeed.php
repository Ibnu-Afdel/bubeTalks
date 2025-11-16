<?php

namespace App\Livewire;

use App\Models\BubeMessage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Polling;

#[Layout('components.layouts.app')]
class BubeFeed extends Component
{
    use WithPagination;

    #[Polling(interval: '2s')]
    public function getPollingData()
    {
        // This will trigger a re-render of the component
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.bube-feed', [
            'messages' => BubeMessage::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(10)
        ]);
    }
}
