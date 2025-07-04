<?php

namespace App\Livewire;

use App\Jobs\GenerateBubeResponseJob;
use App\Models\BubeMessage;
use Livewire\Component;
use Livewire\Attributes\Polling;

class AskBubePage extends Component
{
    public string $question = '';
    public $latestResponses;
    public $hasPendingMessages = false;

    public function mount()
    {
        $this->loadLatestResponses();
    }

    public function loadLatestResponses()
    {
        $this->latestResponses = BubeMessage::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->take(3)
            ->get();

        // Check if there are any pending messages or messages without audio
        $this->hasPendingMessages = $this->latestResponses->contains(function ($message) {
            return $message->status === 'pending' || 
                   ($message->response_text && !$message->audio_url && !$message->error_message);
        });

        // If we have pending messages, we'll keep polling
        if ($this->hasPendingMessages) {
            $this->dispatch('refresh-audio');
        }
    }

    public function submit()
    {
        $this->validate([
            'question' => 'required|string|min:5',
        ]);

        $message = auth()->user()->bubeMessages()->create([
            'question' => $this->question,
            'status' => 'pending',
        ]);

        GenerateBubeResponseJob::dispatch($message);

        $this->reset('question');
        $this->loadLatestResponses();
        $this->hasPendingMessages = true;
        session()->flash('message', 'Your question was sent to bube!');
    }

    #[Polling(interval: '1s')]
    public function getPollingData()
    {
        if ($this->hasPendingMessages) {
            $this->loadLatestResponses();
        }
    }

    public function render()
    {
        return view('livewire.ask-bube-page');
    }
}
